<?php

namespace App\Services;

class SmsService
{
    public function __construct(private KudiSmsService $provider) {}

    public function sendSms($phoneNumber, $message, $options = [])
    {
        return $this->provider->sendSms($phoneNumber, $message, $options['from_address'] ?? config('services.kudi.sender_id', 'ABU'));
    }

    /**
     * Send verification SMS
     *
     * @param  string  $phoneNumber
     * @param  string  $code
     * @return array
     */
    public function sendVerificationSms($phoneNumber, $code)
    {
        $message = "Your ABU Endowment verification code is: {$code}. Valid for 10 minutes. Do not share this code with anyone.";

        return $this->sendSms($phoneNumber, $message, [
            'tag' => 'verification',
            'submit_report' => true,
            'delivery_report' => true,
        ]);
    }

    /**
     * Send welcome SMS
     *
     * @param  string  $phoneNumber
     * @param  string  $name
     * @return array
     */
    public function sendWelcomeSms($phoneNumber, $name)
    {
        $message = "Welcome {$name} to ABU Endowment! Your account has been successfully created. Thank you for joining our community.";

        return $this->sendSms($phoneNumber, $message, [
            'tag' => 'welcome',
            'submit_report' => true,
            'delivery_report' => false,
        ]);
    }

    /**
     * Send donation confirmation SMS
     *
     * @param  string  $phoneNumber
     * @param  string  $name
     * @param  float  $amount
     * @param  string  $project
     * @return array
     */
    public function sendDonationConfirmationSms($phoneNumber, $name, $amount, $project, $reference = '')
    {
        $message = "Thank you for your generous donation to ABU Zaria. Your payment of **₦{$amount}** has been received successfully.\n**Payment Reference:** {$reference}";

        return $this->sendSms($phoneNumber, $message, [
            'tag' => 'donation',
            'submit_report' => true,
            'delivery_report' => true,
        ]);
    }

    /**
     * Send password reset SMS
     *
     * @param  string  $phoneNumber
     * @param  string  $code
     * @return array
     */
    public function sendPasswordResetSms($phoneNumber, $code)
    {
        $message = "Your ABU Endowment password reset code is: {$code}. Valid for 15 minutes. If you didn't request this, ignore this message.";

        return $this->sendSms($phoneNumber, $message, [
            'tag' => 'password_reset',
            'submit_report' => true,
            'delivery_report' => true,
        ]);
    }

    /** Check configuration without sending a billable message to a dummy number. */
    public function testConnection()
    {
        $configured = filled(config('services.kudi.token')) && filled(config('services.kudi.url'));

        return ['success' => $configured, 'message' => $configured ? 'KudiSMS is configured' : 'KudiSMS is not configured',
            'error' => $configured ? null : 'Set KUDI_SMS_KEY and KUDI_SMS_URL.'];
    }
}
