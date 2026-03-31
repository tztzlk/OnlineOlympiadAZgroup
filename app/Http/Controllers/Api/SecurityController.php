<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\ProofOfWorkService;
use Illuminate\Http\Request;

class SecurityController extends Controller
{
    public function powChallenge(Request $request, ProofOfWorkService $proofOfWorkService)
    {
        $request->validate([
            'context' => 'required|string|in:register,feedback,callback',
        ]);

        return response()->json(
            $proofOfWorkService->issueChallenge($request->string('context')->value(), $request)
        );
    }
}
