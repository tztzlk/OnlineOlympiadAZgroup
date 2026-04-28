<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\DocumentQuestionParser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class QuestionImportController extends Controller
{
    public function parse(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:docx,pdf|max:20480',
        ]);

        $file   = $request->file('file');
        $ext    = strtolower($file->getClientOriginalExtension());
        $parser = new DocumentQuestionParser();

        $result = $ext === 'pdf'
            ? $parser->parsePdf($file)
            : $parser->parseDocx($file);

        foreach ($result['questions'] as &$question) {
            $question['image_preview_url'] = !empty($question['image_path'])
                ? Storage::disk('public')->url($question['image_path'])
                : null;
        }
        unset($question);

        return response()->json($result);
    }
}
