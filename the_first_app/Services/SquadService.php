<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SquadService
{
    private string $secretKey;
    private string $baseUrl;

    public function __construct()
    {
        $this->secretKey = (string) config('services.squad.secret_key', '');
        $this->baseUrl   = rtrim(config('services.squad.base_url', 'https://api-d.squadco.com'), '/');
    }

    /**
     * Verify a Squad transaction by reference.
     *
     * @return array{success: bool, data: array, raw: array, error: string|null}
     */
    public function verifyTransaction(string $reference): array
    {
        if (empty($this->secretKey)) {
            Log::error('SquadService: SQUAD_SECRET_KEY not configured');
            return ['success' => false, 'data' => [], 'raw' => [], 'error' => 'Squad secret key not configured'];
        }

        try {
            $response = Http::withToken($this->secretKey)
                ->acceptJson()
                ->timeout(30)
                ->get("{$this->baseUrl}/transaction/verify/{$reference}");

            $raw = $response->json() ?? [];

            if ($response->failed()) {
                Log::warning('SquadService: Verification request failed', [
                    'reference' => $reference,
                    'status'    => $response->status(),
                    'body'      => $response->body(),
                ]);
                return [
                    'success' => false,
                    'data'    => [],
                    'raw'     => $raw,
                    'error'   => $raw['message'] ?? 'Squad verification request failed',
                ];
            }

            $data = $raw['data'] ?? [];

            return [
                'success' => true,
                'data'    => $data,
                'raw'     => $raw,
                'error'   => null,
            ];

        } catch (\Exception $e) {
            Log::error('SquadService: verifyTransaction exception', [
                'reference' => $reference,
                'error'     => $e->getMessage(),
            ]);
            return [
                'success' => false,
                'data'    => [],
                'raw'     => [],
                'error'   => $e->getMessage(),
            ];
        }
    }
}
