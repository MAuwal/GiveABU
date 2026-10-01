<?php

namespace App\Livewire\Admin\Notifications;

use Livewire\Component;
use App\Models\SmsLog;
use App\Services\KudiSmsService;

class SendSms extends Component
{
    public $receiver;
    public $message;
    public $sending = false;
    public $statusMessage;
    public $statusType = 'success';

    protected $rules = [
        'receiver' => 'required|string|max:500',
        'message' => 'required|string|max:918',
    ];

    public function sendSms()
    {
        $this->validate();

        $this->sending = true;
        $this->statusMessage = null;

        $recipients = $this->parseRecipients($this->receiver);

        if (empty($recipients)) {
            $this->statusType = 'error';
            $this->statusMessage = 'Please enter at least one valid phone number.';
            $this->sending = false;
            return;
        }

        $smsService = new KudiSmsService();
        $recipientString = implode(',', $recipients);
        $result = $smsService->sendSms($recipientString, $this->message, 'ABU');

        $responsePayload = is_array($result['response']) ? json_encode($result['response']) : (string) ($result['response'] ?? '');
        $cost = is_array($result['response']) ? ($result['response']['cost'] ?? null) : null;

        foreach ($recipients as $recipient) {
            SmsLog::create([
                'recipient_phone' => $recipient,
                'sender_id' => 'ABU',
                'message' => $this->message,
                'status' => $result['success'] ? 'sent' : 'failed',
                'error_message' => $result['success'] ? null : ($result['error'] ?? 'Failed to send SMS.'),
                'cost' => $cost,
                'response_payload' => $responsePayload,
                'sent_at' => $result['success'] ? now() : null,
            ]);
        }

        if ($result['success']) {
            $this->statusType = 'success';
            $this->statusMessage = 'SMS sent successfully to ' . count($recipients) . ' recipient' . (count($recipients) > 1 ? 's' : '') . '.';
            $this->reset(['receiver', 'message']);
        } else {
            $this->statusType = 'error';
            $this->statusMessage = $result['error'] ?? 'Failed to send SMS.';
        }

        $this->sending = false;
    }

    private function parseRecipients(string $receiver): array
    {
        $parts = preg_split('/[\s,;]+/', trim($receiver));

        return collect($parts)
            ->filter()
            ->map(function ($recipient) {
                $normalized = $this->normalizePhoneNumber($recipient);
                return $normalized;
            })
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function normalizePhoneNumber(string $phone): ?string
    {
        $digits = preg_replace('/[^0-9]/', '', $phone);

        if (empty($digits)) {
            return null;
        }

        if (str_starts_with($digits, '0')) {
            $digits = ltrim($digits, '0');
        }

        if (!str_starts_with($digits, '234') && strlen($digits) <= 10) {
            $digits = '234' . $digits;
        }

        return $digits;
    }

    public function render()
    {
        return view('livewire.admin.notifications.send-sms');
    }
}
