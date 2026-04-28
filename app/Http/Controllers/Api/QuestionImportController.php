<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\DocumentQuestionParser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class QuestionImportController extends Controller
{
    public function parse(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:docx,pdf,vnd.openxmlformats-officedocument.wordprocessingml.document|max:20480',
        ]);

        $file   = $request->file('file');
        $ext    = strtolower($file->getClientOriginalExtension());
        $parser = new DocumentQuestionParser();

        try {
            $result = $ext === 'pdf'
                ? $parser->parsePdf($file)
                : $parser->parseDocx($file);
        } catch (\Throwable $e) {
            Log::error('QuestionImportController: parse failed', [
                'ext'     => $ext,
                'error'   => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            return response()->json([
                'message'   => 'Не удалось разобрать документ. Убедитесь, что файл не повреждён и имеет формат DOCX или PDF.',
                'detail'    => app()->isLocal() ? $e->getMessage() : null,
            ], 422);
        }

        foreach ($result['questions'] as &$question) {
            $question['image_preview_url'] = !empty($question['image_path'])
                ? Storage::disk('public')->url($question['image_path'])
                : null;
        }
        unset($question);

        return response()->json($result);
    }
}
