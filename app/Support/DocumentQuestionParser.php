<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\IOFactory;
use Smalot\PdfParser\Parser as PdfParser;
use ZipArchive;

class DocumentQuestionParser
{
    private const LABELS = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];

    public function parseDocx(UploadedFile $file): array
    {
        $phpWord = IOFactory::load($file->getRealPath(), 'Word2007');
        $items = [];

        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                $this->collectElement($element, $items);
            }
        }

        $this->injectDocxImages($file->getRealPath(), $items);

        return $this->parse($items);
    }

    public function parsePdf(UploadedFile $file): array
    {
        $parser = new PdfParser();
        $pdf = $parser->parseFile($file->getRealPath());
        $text = $pdf->getText();

        $items = [];
        foreach (explode("\n", $text) as $line) {
            $line = trim($line);
            if ($line !== '') {
                $items[] = ['type' => 'text', 'text' => $line, 'bold' => false];
            }
        }

        return $this->parse($items);
    }

    private function collectElement(mixed $element, array &$items): void
    {
        if ($element instanceof \PhpOffice\PhpWord\Element\Image) {
            $items[] = ['type' => 'img_slot'];
            return;
        }

        if ($element instanceof \PhpOffice\PhpWord\Element\Table) {
            foreach ($element->getRows() as $row) {
                foreach ($row->getCells() as $cell) {
                    foreach ($cell->getElements() as $el) {
                        $this->collectElement($el, $items);
                    }
                }
            }
            return;
        }

        if (!method_exists($element, 'getElements')) {
            if (method_exists($element, 'getText')) {
                $t = trim((string) $element->getText());
                if ($t !== '') {
                    $items[] = ['type' => 'text', 'text' => $t, 'bold' => false];
                }
            }
            return;
        }

        $text = '';
        $boldRuns = 0;
        $totalRuns = 0;

        foreach ($element->getElements() as $child) {
            if ($child instanceof \PhpOffice\PhpWord\Element\Image) {
                $items[] = ['type' => 'img_slot'];
                continue;
            }

            if (method_exists($child, 'getText')) {
                $t = (string) $child->getText();
                if ($t !== '') {
                    $text .= $t;
                    $totalRuns++;
                    $font = method_exists($child, 'getFontStyle') ? $child->getFontStyle() : null;
                    if ($font && method_exists($font, 'isBold') && $font->isBold()) {
                        $boldRuns++;
                    }
                }
            }
        }

        $text = trim($text);
        if ($text !== '') {
            $bold = $totalRuns > 0 && $boldRuns >= (int) ceil($totalRuns / 2);
            $items[] = ['type' => 'text', 'text' => $text, 'bold' => $bold];
        }
    }

    private function injectDocxImages(string $filePath, array &$items): void
    {
        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            return;
        }

        $images = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (!preg_match('/^word\/media\//i', $name)) {
                continue;
            }
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if (!in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'webp', 'bmp'], true)) {
                continue;
            }
            $data = $zip->getFromIndex($i);
            if ($data !== false) {
                $images[] = ['data' => $data, 'ext' => ($ext === 'jpeg' ? 'jpg' : $ext)];
            }
        }
        $zip->close();

        $imgIdx = 0;
        foreach ($items as &$item) {
            if ($item['type'] === 'img_slot') {
                if (isset($images[$imgIdx])) {
                    $item = ['type' => 'image', 'data' => $images[$imgIdx]['data'], 'ext' => $images[$imgIdx]['ext']];
                    $imgIdx++;
                } else {
                    $item['type'] = '_skip';
                }
            }
        }
        unset($item);
    }

    private function parse(array $items): array
    {
        $questions = [];
        $warnings  = [];
        $current   = null;
        $state     = 'idle'; // idle | question | answers

        // Numbered question: "1. text", "1) text", "Вопрос 1: text" etc.
        $qPat = '/^(?:\d+[.)]\s+|[Вв]опрос\s*\d+[.:)]\s*)(.+)/su';
        // Answer option: optional *, then Latin A-H or Cyrillic А-З, then .) and text
        $aPat = '/^(\*?)([A-Ha-hАБВГДЕЖЗабвгдежз])[.)]\s*(.*)/su';

        foreach ($items as $item) {
            if (($item['type'] ?? '') === '_skip') {
                continue;
            }

            if ($item['type'] === 'image') {
                // Attach first image to current question
                if ($current !== null && $current['image'] === null) {
                    $path = $this->saveImage($item['data'], $item['ext']);
                    if ($path) {
                        $current['image'] = $path;
                    }
                }
                continue;
            }

            $text = $item['text'];
            $bold = $item['bold'] ?? false;

            // New question?
            if (preg_match($qPat, $text, $m)) {
                if ($current !== null) {
                    $q = $this->finalize($current, $warnings);
                    if ($q) {
                        $questions[] = $q;
                    }
                }
                $current = ['question' => trim($m[1]), 'answers' => [], 'correct' => null, 'image' => null];
                $state   = 'question';
                continue;
            }

            if ($current === null) {
                continue;
            }

            // Answer option?
            if (preg_match($aPat, $text, $m)) {
                $state       = 'answers';
                $starPrefix  = $m[1] === '*';
                $answerText  = trim($m[3]);
                $isCorrect   = $starPrefix || $bold;

                if (!$isCorrect && str_contains($answerText, '(+)')) {
                    $isCorrect  = true;
                    $answerText = trim(str_replace('(+)', '', $answerText));
                }

                if (!$isCorrect && preg_match('/\+\s*$/', $answerText)) {
                    $isCorrect  = true;
                    $answerText = rtrim(rtrim($answerText), '+');
                    $answerText = trim($answerText);
                }

                $idx   = count($current['answers']);
                $label = self::LABELS[$idx] ?? chr(65 + $idx);
                $current['answers'][] = ['label' => $label, 'answer' => $answerText, 'position' => $idx + 1];

                if ($isCorrect && $current['correct'] === null) {
                    $current['correct'] = $label;
                }
                continue;
            }

            // Continuation text
            if ($state === 'question') {
                $current['question'] .= ' ' . $text;
            } elseif ($state === 'answers' && !empty($current['answers'])) {
                $last = count($current['answers']) - 1;
                $current['answers'][$last]['answer'] .= ' ' . $text;
            }
        }

        if ($current !== null) {
            $q = $this->finalize($current, $warnings);
            if ($q) {
                $questions[] = $q;
            }
        }

        return ['questions' => $questions, 'warnings' => $warnings];
    }

    private function finalize(array $current, array &$warnings): ?array
    {
        if (count($current['answers']) < 2) {
            if ($current['question']) {
                $preview = mb_substr(trim($current['question']), 0, 50);
                $warnings[] = "Вопрос без вариантов ответа пропущен: \"{$preview}\"";
            }
            return null;
        }

        if ($current['correct'] === null) {
            $preview = mb_substr(trim($current['question']), 0, 50);
            $warnings[] = "Правильный ответ не определён: \"{$preview}\"";
        }

        return [
            'question'      => trim($current['question']),
            'explanation'   => '',
            'correct_answer' => $current['correct'],
            'image_source'  => $current['image'] ? 'upload' : '',
            'image_url'     => '',
            'image_path'    => $current['image'] ?? '',
            'answers'       => $current['answers'],
        ];
    }

    private function saveImage(string $data, string $ext): ?string
    {
        try {
            $path = 'question-images/' . Str::uuid() . '.' . $ext;
            Storage::disk('public')->put($path, $data);
            return $path;
        } catch (\Throwable) {
            return null;
        }
    }
}
