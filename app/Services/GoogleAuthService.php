<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleAuthService
{
    public function verifyToken($idToken): array|false
    {
        $audiences = array_filter(array_map('trim', explode(',', (string) config('services.google.allowed_client_ids', config('services.google.client_id')))));
        if (! $audiences || ! is_string($idToken) || $idToken === '' || strlen($idToken) > 16384) {
            return false;
        }
        try {
            // Existing server-side Google verification remains the signature authority.
            // Never log the bearer ID token, query URL, payload or provider error body.
            $response = Http::connectTimeout(5)->timeout(10)->get('https://oauth2.googleapis.com/tokeninfo', ['id_token' => $idToken]);
            $payload = $response->json();
            if (! $response->successful() || ! is_array($payload)
                || ! in_array($payload['aud'] ?? null, $audiences, true)
                || ! in_array($payload['iss'] ?? null, ['accounts.google.com', 'https://accounts.google.com'], true)
                || ! ctype_digit((string) ($payload['exp'] ?? '')) || (int) $payload['exp'] <= now()->timestamp
                || ! is_string($payload['sub'] ?? null) || $payload['sub'] === ''
                || ! filter_var($payload['email'] ?? '', FILTER_VALIDATE_EMAIL)
                || ! filter_var($payload['email_verified'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                return false;
            }

            return [
                'email_authoritative' => str_ends_with(strtolower($payload['email']), '@gmail.com') || ! empty($payload['hd']),
                'google_id' => $payload['sub'], 'email' => $payload['email'], 'email_verified' => true,
                'name' => $payload['name'] ?? null, 'given_name' => $payload['given_name'] ?? null,
                'family_name' => $payload['family_name'] ?? null, 'picture' => $payload['picture'] ?? null,
                'gender' => $payload['gender'] ?? null, 'locale' => $payload['locale'] ?? null,
            ];
        } catch (\Throwable $e) {
            Log::warning('Google authentication unavailable', ['exception' => get_class($e)]);

            return false;
        }
    }
}
