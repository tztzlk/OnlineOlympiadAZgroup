<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AiController extends Controller
{
    public function generate(Request $request)
    {
        $request->validate([
            'prompt' => 'required|string|max:4000',
        ]);

        return response()->json([
            'message' => 'AI-запрос принят.',
            'plan' => $request->user()?->plan ?? 'free',
            'preview' => 'MVP endpoint for secured AI generation.',
        ]);
    }
}
