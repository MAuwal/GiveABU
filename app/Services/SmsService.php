<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Exception;

class SmsService
{
    private $messageApi;
    private $configuration;

    public function __construct()
    {
        // Include the Ozeki SMS library files
        require_once base_path('MessageApi/Configuration.php');
        require_once base_path('MessageApi/Message.php');
        require_once base_path('MessageApi/MessageApi.php');
        require_once base_path('MessageApi/MessageApi_MessageSendResult.php');
        require_once base_path('MessageApi/MessageApi_MessageSendResults.php');

        // Initialize configuration
        $this->configuration = new \Ozeki_PHP_Rest\Configuration();
        $this->configuration->Username = config('services.ozeki.username', 'http_user');
        $this->configuration->Password = config('services.ozeki.password', 'qwe123');
        $this->configuration->ApiUrl = config('services.ozeki.api_url', 'http://127.0.0.1:9509/api?action=rest');

        // Initialize MessageApi
        $this->messageApi = new \Ozeki_PHP_Rest\MessageApi($this->configuration);
    }

    /**
     * Send a single SMS message
     *
     * @param string $phoneNumber
     * @param string $message
     * @param array $options
     * @return array
     */
    public function sendSms($phoneNumber, $message, $options = [])
    {
        try {
            // Format phone number (ensure it starts with +)
            $phoneNumber = $this->formatPhoneNumber($phoneNumber);

            // Create message object
            $msg = new \Ozeki_PHP_Rest\Message();
            $msg->ToAddress = $phoneNumber;
            $msg->Text = $message;

            // Set optional properties
            if (isset($options['from_address'])) {
                $msg->FromAddress = $options['from_address'];
            }

            if (isset($options['submit_report'])) {
                $msg->IsSubmitReportRequested = $options['submit_report'];
            }

            if (isset($options['delivery_report'])) {
                $msg->IsDeliveryReportRequested = $options['delivery_report'];
            }

            // Add tags for tracking
            $msg->AddTag('Type', 'SMS:TEXT');
            $msg->AddTag('Source', 'ABU_Endowment');
            if (isset($options['tag'])) {
                $msg->AddTag('Category', $options['tag']);
            }

            // Send the message
            $result = $this->messageApi->SendSingle($msg);

            if ($result && $result->StatusMessage === 'SUCCESS') {
                Log::info('SMS sent successfully', [
                    'to' => $phoneNumber,
                    'message_id' => $result->Message->ID,
                    'status' => $result->StatusMessage
                ]);

                return [
                    'success' => true,
                    'message_id' => $result->Message->ID,
                    'status' => $result->StatusMessage,
                    'to' => $phoneNumber
                ];
            } else {
                Log::error('SMS sending failed', [
                    'to' => $phoneNumber,
                    'status' => $result ? $result->StatusMessage : 'Unknown error'
                ]);

                return [
                    'success' => false,
                    'error' => $result ? $result->StatusMessage : 'Unknown error',
                    'to' => $phoneNumber
                ];
            }

        } catch (Exception $e) {
            Log::error('SMS service error', [
                'error' => $e->getMessage(),
                'to' => $phoneNumber
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'to' => $phoneNumber
            ];
        }
    }

    /**
     * Send verification SMS
     *
     * @param string $phoneNumber
     * @param string $code
     * @return array
     */
    public function sendVerificationSms($phoneNumber, $code)
    {
        $message = "Your ABU Endowment verification code is: {$code}. Valid for 10 minutes. Do not share this code with anyone.";
        
        return $this->sendSms($phoneNumber, $message, [
            'tag' => 'verification',
            'submit_report' => true,
            'delivery_report' => true
        ]);
    }

    /**
     * Send welcome SMS
     *
     * @param string $phoneNumber
     * @param string $name
     * @return array
     */
    public function sendWelcomeSms($phoneNumber, $name)
    {
        $message = "Welcome {$name} to ABU Endowment! Your account has been successfully created. Thank you for joining our community.";
        
        return $this->sendSms($phoneNumber, $message, [
            'tag' => 'welcome',
            'submit_report' => true,
            'delivery_report' => false
        ]);
    }

    /**
     * Send donation confirmation SMS
     *
     * @param string $phoneNumber
     * @param string $name
     * @param float $amount
     * @param string $project
     * @return array
     */
    public function sendDonationConfirmationSms($phoneNumber, $name, $amount, $project)
    {
        $message = "Dear {$name}, thank you for your donation of ₦{$amount} to {$project}. Your contribution makes a difference!";
        
        return $this->sendSms($phoneNumber, $message, [
            'tag' => 'donation',
            'submit_report' => true,
            'delivery_report' => true
        ]);
    }

    /**
     * Send password reset SMS
     *
     * @param string $phoneNumber
     * @param string $code
     * @return array
     */
    public function sendPasswordResetSms($phoneNumber, $code)
    {
        $message = "Your ABU Endowment password reset code is: {$code}. Valid for 15 minutes. If you didn't request this, ignore this message.";
        
        return $this->sendSms($phoneNumber, $message, [
            'tag' => 'password_reset',
            'submit_report' => true,
            'delivery_report' => true
        ]);
    }

    /**
     * Format phone number to international format
     *
     * @param string $phoneNumber
     * @return string
     */
    private function formatPhoneNumber($phoneNumber)
    {
        // Remove all non-digit characters except +
        $phoneNumber = preg_replace('/[^0-9+]/', '', $phoneNumber);

        // If it doesn't start with +, assume it's a Nigerian number
        if (!str_starts_with($phoneNumber, '+')) {
            // Remove leading 0 if present
            if (str_starts_with($phoneNumber, '0')) {
                $phoneNumber = substr($phoneNumber, 1);
            }
            
            // Add Nigerian country code if not present
            if (!str_starts_with($phoneNumber, '234')) {
                $phoneNumber = '234' . $phoneNumber;
            }
            
            $phoneNumber = '+' . $phoneNumber;
        }

        return $phoneNumber;
    }

    /**
     * Test SMS service connectivity
     *
     * @return array
     */
    public function testConnection()
    {
        try {
            // Try to send a test message to a dummy number
            $result = $this->sendSms('+2348000000000', 'Test message from ABU Endowment SMS Service');
            
            return [
                'success' => true,
                'message' => 'SMS service is properly configured',
                'details' => $result
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'SMS service configuration error',
                'error' => $e->getMessage()
            ];
        }
    }
} 