<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class GoogleAuthService
{
    protected $clientId;
    protected $allowedClientIds = [];

    public function __construct()
    {
        $this->clientId = config('services.google.client_id');

        // Build allowlist from comma-separated env value.
        // Falls back to just the web client_id if GOOGLE_ALLOWED_CLIENT_IDS is not set.
        $raw = config('services.google.allowed_client_ids', $this->clientId);
        $this->allowedClientIds = array_filter(
            array_map('trim', explode(',', (string) $raw))
        );
    }

    /**
     * Verify Google ID token using Google's tokeninfo endpoint.
     * Google validates the signature, expiry, and issuer server-side.
     * We only need to check the audience (aud) matches our client ID.
     *
     * @param string $idToken  The Google ID token from the frontend
     * @return array|false     User data array on success, false on failure
     */
    public function verifyToken($idToken)
    {
        try {
            if (empty($idToken)) {
                Log::error('GoogleAuthService: Empty token received');
                return false;
            }

            Log::info('Google Token Verification - Start', [
                'token_length'   => strlen($idToken),
                'token_preview'  => substr($idToken, 0, 30) . '...',
                'client_id'      => $this->clientId,
                'client_id_set'  => !empty($this->clientId),
            ]);

            // Ask Google to verify the token (signature + expiry + issuer)
            $response = Http::timeout(10)
                ->get('https://oauth2.googleapis.com/tokeninfo', [
                    'id_token' => $idToken,
                ]);

            if (!$response->successful()) {
                Log::error('Google Token Verification - tokeninfo request failed', [
                    'status'   => $response->status(),
                    'body'     => $response->body(),
                ]);
                return false;
            }

            $payload = $response->json();

            Log::info('Google Token - tokeninfo response', [
                'aud'            => $payload['aud'] ?? null,
                'email'          => $payload['email'] ?? null,
                'email_verified' => $payload['email_verified'] ?? null,
                'exp'            => $payload['exp'] ?? null,
            ]);

            // Verify the audience matches one of our allowed client IDs.
            // allowedClientIds contains the web client ID + any Android/iOS client IDs
            // listed in GOOGLE_ALLOWED_CLIENT_IDS (comma-separated).
            if (!empty($this->allowedClientIds) && isset($payload['aud'])
                && !in_array($payload['aud'], $this->allowedClientIds, true)) {
                Log::error('Google Token - Client ID (aud) not in allowlist', [
                    'expected_one_of' => $this->allowedClientIds,
                    'got'             => $payload['aud'],
                ]);
                return false;
            }

            // Require a verified email
            if (empty($payload['email'])) {
                Log::error('Google Token - Missing email in payload');
                return false;
            }

            $emailVerified = filter_var($payload['email_verified'] ?? false, FILTER_VALIDATE_BOOLEAN);
            if (!$emailVerified) {
                Log::error('Google Token - Email not verified', ['email' => $payload['email']]);
                return false;
            }

            Log::info('Google Token - Verification successful', ['email' => $payload['email']]);

            return [
                'google_id'      => $payload['sub'] ?? null,
                'email'          => $payload['email'],
                'email_verified' => $emailVerified,
                'name'           => $payload['name'] ?? null,
                'given_name'     => $payload['given_name'] ?? null,
                'family_name'    => $payload['family_name'] ?? null,
                'picture'        => $payload['picture'] ?? null,
                'gender'         => $payload['gender'] ?? null,
                'locale'         => $payload['locale'] ?? null,
            ];

        } catch (Exception $e) {
            Log::error('GoogleAuthService: Token verification exception', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            return false;
        }
    }
}
