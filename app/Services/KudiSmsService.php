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

        if (! $token || ! $url || ! str_starts_with($url, 'https://')) {
            Log::warning('KudiSMS configuration missing or insecure');

            return $this->failure('KudiSMS is not configured. Please set KUDI_SMS_KEY and an HTTPS KUDI_SMS_URL.');
        }
        $recipients = $this->normalizeRecipient($recipient, $defaultCountryId);
        if ($recipients === '') {
            return $this->failure('Please enter a valid recipient phone number.');
        }
        $fields = ['token' => $token, 'senderID' => $senderId, 'recipients' => $recipients, 'message' => $message];
        if (str_ends_with(parse_url($url, PHP_URL_PATH) ?? '', '/intcomposesms')) {
            $fields['country_id'] = $defaultCountryId;
        } else {
            $fields['gateway'] = 2;
        }
        try {
            // POST keeps API keys and message contents out of query URLs. Never
            // retry automatically: an ambiguous timeout may already have sent SMS.
            $response = Http::acceptJson()->asForm()->connectTimeout(5)->timeout(15)->post($url, $fields);
            $payload = $response->json();
            if (! $response->successful() || ! is_array($payload)
                || strtolower((string) ($payload['status'] ?? '')) !== 'success'
                || (string) ($payload['error_code'] ?? '') !== '000') {
                $providerCode = (string) ($payload['error_code'] ?? '');
                $providerCode = preg_match('/\A[0-9]{3}\z/', $providerCode) ? $providerCode : null;
                Log::warning('KudiSMS send rejected', ['http_status' => $response->status(), 'provider_code' => $providerCode]);

                return $this->failure('KudiSMS could not accept this message.'.($providerCode ? ' Provider code: '.$providerCode : ''));
            }
            // Retain only delivery/accounting fields; provider responses may echo secrets.
            $safe = array_intersect_key($payload, array_flip(['status', 'error_code', 'cost', 'data']));
            $data = $safe['data'] ?? null;
            $first = is_array($data) ? ($data[0] ?? null) : $data;
            $messageId = is_string($first) && str_contains($first, '|') ? explode('|', $first, 2)[1] : null;

            return ['success' => true, 'message' => 'Message accepted by KudiSMS', 'response' => $safe,
                'message_id' => $messageId, 'status' => 'accepted', 'to' => $recipients];
        } catch (\Throwable $e) {
            Log::warning('KudiSMS send unavailable', ['exception' => get_class($e)]);

            return $this->failure('SMS delivery is temporarily unavailable.');
        }
    }

    private function failure(string $error): array
    {
        return ['success' => false, 'error' => $error, 'response' => null, 'message_id' => null, 'status' => 'unavailable'];
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

            if (! str_starts_with($digits, $defaultCountryId) && strlen($digits) <= 10) {
                $digits = $defaultCountryId.$digits;
            }

            $normalized[] = $digits;
        }

        return implode(',', array_unique($normalized));
    }
}
