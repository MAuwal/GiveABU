<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KudiSmsService
{
    public function sendSms(string $recipient, string $message, string $senderId = 'ABU', string $defaultCountryId = '234'): array
    {
        $token = config('services.kudi.token');
        $url = config('services.kudi.url');

        if (!$token || !$url) {
            Log::error('KudiSMS configuration missing', [
                'token' => $token,
                'url' => $url,
            ]);

            return [
                'success' => false,
                'error' => 'KudiSMS is not configured. Please set KUDI_SMS_KEY and KUDI_SMS_URL.',
            ];
        }

        $recipients = $this->normalizeRecipient($recipient, $defaultCountryId);

        try {
            $response = Http::get($url, [
                'token' => $token,
                'senderID' => $senderId,
                'recipients' => $recipients,
                'message' => $message,
                'gateway' => 2,
            ]);

            if ($response->failed()) {
                Log::error('KudiSMS request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return [
                    'success' => false,
                    'error' => 'Failed to send SMS: ' . $response->body(),
                    'response' => $response->body(),
                ];
            }

            $payload = $response->json();
            $status = $payload['status'] ?? null;
            $errorCode = $payload['error_code'] ?? null;
            $messageText = $payload['msg'] ?? $payload['message'] ?? $response->body();

            if (strtolower($status) === 'success' && $errorCode === '000') {
                return [
                    'success' => true,
                    'message' => $messageText,
                    'response' => $payload,
                ];
            }

            Log::warning('KudiSMS returned non-success response', [
                'payload' => $payload,
            ]);

            return [
                'success' => false,
                'error' => 'KudiSMS error: ' . $messageText,
                'response' => $payload,
            ];
        } catch (\Exception $e) {
            Log::error('KudiSMS send exception', [
                'error' => $e->getMessage(),
                'recipient' => $recipient,
                'message' => $message,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    private function normalizeRecipient(string $recipient, string $defaultCountryId): string
    {
        $parts = preg_split('/[\s,;]+/', trim($recipient));
        $normalized = [];

        foreach ($parts as $part) {
            $digits = preg_replace('/[^0-9]/', '', $part);

            if (empty($digits)) {
                continue;
            }

            if (str_starts_with($digits, '0')) {
                $digits = ltrim($digits, '0');
            }

            if (!str_starts_with($digits, $defaultCountryId) && strlen($digits) <= 10) {
                $digits = $defaultCountryId . $digits;
            }

            $normalized[] = $digits;
        }

        return implode(',', array_unique($normalized));
    }
}
