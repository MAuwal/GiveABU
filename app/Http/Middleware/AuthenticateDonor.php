<?php

namespace App\Http\Middleware;

use App\Services\DonorTokenService;
use Closure;
use Illuminate\Http\Request;

class AuthenticateDonor
{
    public function handle(Request $request, Closure $next)
    {
        $session = app(DonorTokenService::class)->fromRequest($request);
        abort_unless($session, 401);
        $request->attributes->set('authenticated_donor_session', $session);
        $id = $request->route('id') ?? $request->route('donor');
        if ($id instanceof \Illuminate\Database\Eloquent\Model) {
            $id = $id->getKey();
        }
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
        $response = $next($request);
        $response->headers->set('Cache-Control', 'private, no-store');

        return $response;
    }
}
