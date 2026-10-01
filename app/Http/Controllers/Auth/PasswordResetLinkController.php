<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;

class PasswordResetLinkController extends Controller
{
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        try {
            Password::sendResetLink($request->only('email'));
        } catch (\Exception $e) {
            Log::error('Failed to send account password reset email', ['exception' => get_class($e)]);
        }

        $message = 'If the email exists, a reset link has been sent.';

        return $request->expectsJson()
            ? response()->json(['status' => $message])
            : back()->with('status', $message);
    }
}
