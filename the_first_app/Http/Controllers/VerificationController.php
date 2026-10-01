<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Services\SmsService;

class VerificationController extends Controller
{
    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    /**
     * Send SMS verification code
     */
    public function sendSMS(Request $request)
    {
        $request->validate([
            'phone' => 'required|string'
        ]);

        $phone = $request->phone;
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Store code in cache for 10 minutes
        Cache::put("sms_verification_{$phone}", $code, 600);
        
        // Send SMS using Ozeki SMS Gateway
        $smsResult = $this->smsService->sendVerificationSms($phone, $code);
        
        if ($smsResult['success']) {
            return response()->json([
                'success' => true,
                'message' => 'SMS verification code sent successfully',
                'message_id' => $smsResult['message_id']
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send SMS verification code',
                'error' => $smsResult['error']
            ], 500);
        }
    }

    /**
     * Send email verification code
     */
    public function sendEmail(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email'
            ]);

            $email = $request->email;
            $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            
            // Store code in cache for 10 minutes
            Cache::put("email_verification_{$email}", $code, 600);
            
            // Send email with verification code
            Mail::send('emails.verification', ['code' => $code], function($message) use ($email) {
                $message->from('abu-endowment-verify@abu-endowment.cloud', 'ABU Endowment')
                        ->to($email)
                        ->subject('ABU Endowment - Email Verification Code');
            });
            
            return response()->json([
                'success' => true,
                'message' => 'Email verification code sent successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Email verification failed', [
                'email' => $request->email,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to send email verification code'
            ], 500);
        }
    }

    /**
     * Verify SMS code
     */
    public function verifySMS(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'code' => 'required|string|size:6'
        ]);

        $phone = $request->phone;
        $code = $request->code;
        
        $storedCode = Cache::get("sms_verification_{$phone}");
        
        if (!$storedCode || $storedCode !== $code) {
            return response()->json([
                'message' => 'Invalid verification code'
            ], 400);
        }
        
        // Clear the code from cache
        Cache::forget("sms_verification_{$phone}");
        
        return response()->json([
            'verified' => true,
            'message' => 'SMS verification successful'
        ]);
    }

    /**
     * Verify email code
     */
    public function verifyEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string|size:6'
        ]);

        $email = $request->email;
        $code = $request->code;
        
        $storedCode = Cache::get("email_verification_{$email}");
        
        if (!$storedCode || $storedCode !== $code) {
            return response()->json([
                'message' => 'Invalid verification code'
            ], 400);
        }
        
        // Clear the code from cache
        Cache::forget("email_verification_{$email}");
        
        return response()->json([
            'verified' => true,
            'message' => 'Email verification successful'
        ]);
    }
} 