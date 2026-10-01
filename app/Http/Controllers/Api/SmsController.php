<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SmsLog;
use App\Services\KudiSmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SmsController extends Controller
{
    public function sendSms(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'to' => ['required', 'string', 'max:30'],
            'message' => ['required', 'string', 'max:918'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors()->first(),
            ], 422);
        }

        $to = $request->input('to');
        $message = $request->input('message');

        $smsService = app(KudiSmsService::class);
        $result = $smsService->sendSms($to, $message, config('services.kudi.sender_id', 'ABU'));
        try {
            SmsLog::create([
                'recipient_phone' => $to, 'sender_id' => config('services.kudi.sender_id', 'ABU'),
                'message' => $message, 'status' => $result['success'] ? 'sent' : 'failed',
                'error_message' => $result['success'] ? null : ($result['error'] ?? 'SMS unavailable'),
                'cost' => $result['response']['cost'] ?? null,
                'response_payload' => json_encode($result['response'] ?? null),
                'sent_at' => $result['success'] ? now() : null,
            ]);
        } catch (\Throwable $e) {
            // A logging failure must not prompt resending an already accepted SMS.
            \Illuminate\Support\Facades\Log::warning('SMS history could not be recorded', ['exception' => get_class($e)]);
        }

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => $result['message'] ?? 'Message sent',
                'response' => null,
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => 'Failed to send SMS.',
            'response' => null,
        ], 500);
    }

    public function getMessages()
    {
        $messages = SmsLog::latest()->limit(20)->get()->map(fn ($log) => [
            'sid' => (string) $log->id, 'to' => $log->recipient_phone, 'from' => $log->sender_id,
            'body' => $log->message, 'status' => $log->status, 'date_sent' => $log->sent_at,
        ]);

        return response()->json(['success' => true, 'messages' => $messages]);
    }
}
