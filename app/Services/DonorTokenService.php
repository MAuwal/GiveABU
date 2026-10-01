<?php

namespace App\Services;

use App\Models\DonorSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DonorTokenService
{
    public function issue(DonorSession $session): string
    {
        $token = Str::random(64);
        DB::table('donor_access_tokens')->insert([
            'donor_session_id' => $session->id, 'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addDays(30), 'created_at' => now(), 'updated_at' => now(),
        ]);

        return $token;
    }

    public function resolve(mixed $token): ?DonorSession
    {
        if (! is_string($token) || ! preg_match('/\A[a-zA-Z0-9]{64}\z/', $token)) {
            return null;
        }
        $id = DB::table('donor_access_tokens')->where('token_hash', hash('sha256', $token))
            ->where('expires_at', '>', now())->value('donor_session_id');

        return $id ? DonorSession::find($id) : null;
    }

    public function fromRequest(Request $request): ?DonorSession
    {
        return $this->resolve($request->bearerToken() ?: $request->header('X-Device-Session'));
    }

    public function revoke(Request $request): void
    {
        $token = $request->bearerToken() ?: $request->header('X-Device-Session');
        if (is_string($token)) {
            DB::table('donor_access_tokens')->where('token_hash', hash('sha256', $token))->delete();
        }
    }

    public function revokeAll(int $sessionId): void
    {
        DB::table('donor_access_tokens')->where('donor_session_id', $sessionId)->delete();
    }
}
