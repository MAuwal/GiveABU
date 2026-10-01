<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\KudiSmsService;
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

        $smsService = new KudiSmsService();
        $result = $smsService->sendSms($to, $message, 'ABU');

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => $result['message'] ?? 'Message sent',
                'response' => $result['response'] ?? null,
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => $result['error'] ?? 'Failed to send SMS.',
            'response' => $result['response'] ?? null,
        ], 500);
    }

    public function getMessages()
    {
        $sid = env('TWILIO_SID');
        $token = env('TWILIO_AUTH_TOKEN');
        $twilio = new Client($sid, $token);

        try {
            $messages = $twilio->messages->read([], 20); // last 20 messages
            $result = [];
            foreach ($messages as $msg) {
                $result[] = [
                    'sid' => $msg->sid,
                    'to' => $msg->to,
                    'from' => $msg->from,
                    'body' => $msg->body,
                    'status' => $msg->status,
                    'date_sent' => $msg->dateSent,
                ];
            }
            return response()->json(['success' => true, 'messages' => $result]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
} 