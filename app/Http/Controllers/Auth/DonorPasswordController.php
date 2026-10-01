<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Api\DonorSessionController;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class DonorPasswordController extends Controller
{
    public function send(Request $request): RedirectResponse
    {
        // Website links always return to our own reset page.
        $request->request->remove('callback_url');
        app(DonorSessionController::class)->forgotPassword($request, true);

        return back()->with('status', 'If the email exists, a reset link has been sent.');
    }

    public function reset(Request $request): RedirectResponse
    {
        $request->validate(['token' => 'required|string|max:128']);
        $response = app(DonorSessionController::class)->resetPasswordWithToken($request, $request->input('token'));
        if (! $response->isSuccessful()) {
            throw ValidationException::withMessages(['token' => 'This reset link is invalid or expired. Request a new link.']);
        }

        $request->session()->forget('donor_token');
        $request->session()->regenerate();

        return redirect()->route('donor.password.request')->with('status', 'Your password has been reset. Return to the website to sign in with your new password.');
    }
}
