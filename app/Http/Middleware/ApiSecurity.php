<?php

namespace App\Http\Middleware;

use App\Services\DonorTokenService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class ApiSecurity
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->isMethod('OPTIONS')) {
            return $next($request);
        }
        $path = $request->path();
        // Legacy passwordless login and identity-by-fingerprint cannot safely authenticate.
        if (in_array($path, ['api/session/create', 'api/session/login', 'api/session/login-with-donor'], true)) {
            return response()->json(['success' => false, 'message' => 'Please sign in through /api/donor-sessions/login with your password.'], 410);
        }
        if (in_array($path, ['api/test-google-token', 'api/devices/test-register', 'api/payments/test', 'api/test'], true)) {
            abort(404);
        }
        // Fixed keys prevent per-route/username variation from bypassing quotas.
        $bucket = $request->is('api/verification/*', 'api/donor-sessions/send-verification', 'api/donor-sessions/forgot-password')
            ? 'verification' : ($request->is('api/donor-sessions/login', 'api/donor-sessions/register', 'api/donor-sessions/google-*')
                || ($path === 'api/donors' && $request->isMethod('POST')) ? 'login' : 'api');
        $limit = ['verification' => 5, 'login' => 10, 'api' => 120][$bucket];
        $key = 'security:'.$bucket.':'.hash('sha256', (string) $request->ip());
        // Payment webhooks use signatures and retries; do not share user IP quotas.
        if (! $request->is('api/*/webhook')) {
            if (RateLimiter::tooManyAttempts($key, $limit)) {
                return response()->json(['success' => false, 'message' => 'Too many requests. Please try again shortly.'], 429)
                    ->header('Retry-After', RateLimiter::availableIn($key));
            }
            RateLimiter::hit($key, 60);
        }
        $admin = $request->is('api/admin/*', 'api/statistics/*', 'api/send-sms', 'api/sms-messages')
            || ($request->is('api/donor-tiers', 'api/donor-tiers/*') && ! $request->isMethod('GET'));
        if ($admin) {
            $user = Auth::guard('sanctum')->user();
            abort_unless($user, 401);
            abort_unless(strtolower($user->role?->role_title ?? '') === 'admin', 403);
        }
        $private = $request->is('api/messages', 'api/messages/*', 'api/donor/*/messages',
            'api/donors', 'api/donors/*', 'api/alumni/*', 'api/donations/history',
            'api/device/*', 'api/devices/register', 'api/devices/check/*', 'api/sessions/check',
            'api/session/check', 'api/session/logout') && ! ($path === 'api/donors' && $request->isMethod('POST'));
        $private = $private || $request->is('api/donor-sessions/me', 'api/donor-sessions/logout',
            'api/donor-sessions/profile', 'api/donor-sessions/check-device', 'api/donor-sessions/send-verification',
            'api/donor-sessions/*/username', 'api/donor-sessions/*/password');
        if ($private && ! $admin) {
            $session = app(DonorTokenService::class)->fromRequest($request);
            abort_unless($session, 401);
            $request->attributes->set('authenticated_donor_session', $session);
            $id = $request->route('id') ?? $request->route('donor');
            if ($id !== null && $request->is('api/donors/*', 'api/donor/*/messages') && is_numeric($id)) {
                abort_unless($session->donor_id && (int) $id === (int) $session->donor_id, 403);
            }
            $requestedSession = $request->route('session_id') ?? $request->input('session_id');
            if ($requestedSession !== null) {
                abort_unless((string) $requestedSession === (string) $session->id, 403);
            }
            // Existing controller validation keeps session_id fields, but identity comes from the token.
            if ($request->is('api/donor-sessions/*')) {
                $request->merge(['session_id' => $session->id]);
            }
            if ($request->filled('donor_id')) {
                abort_unless((int) $request->input('donor_id') === (int) $session->donor_id, 403);
            }
            if ($request->has('donor_tier_id')) {
                abort(403);
            }
        }
        $response = $next($request);
        if ($private || $admin) {
            $response->headers->set('Cache-Control', 'private, no-store');
        }

        return $response;
    }
}
