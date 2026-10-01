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

        $message = 'If this email is linked to an eligible account, we have sent a password reset link. Check your inbox and spam folder.';

        return $request->expectsJson()
            ? response()->json(['status' => $message])
            : back()->with('status', $message)->with('recovery_requested', true);
    }
}
