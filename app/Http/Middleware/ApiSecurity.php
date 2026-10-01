<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
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
        $response = $next($request);
        if (in_array('role:admin', $request->route()->gatherMiddleware(), true)) {
            $response->headers->set('Cache-Control', 'private, no-store');
        }

        return $response;
    }
}
