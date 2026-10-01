<?php

namespace App\Console\Commands;

use App\Services\SmsService;
use Illuminate\Console\Command;

class TestSmsService extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sms:test {phone : Phone number to send test SMS to}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the SMS service with KudiSMS';

    /**
     * Execute the console command.
     */
    public function handle(SmsService $smsService)
    {
        $phone = $this->argument('phone');

        $this->info('Testing SMS service...');
        $this->info("Sending test SMS to: {$phone}");

        // Check configuration first
        $connectionTest = $smsService->testConnection();

        if (! $connectionTest['success']) {
            $this->error('KudiSMS configuration missing:');
            $this->error($connectionTest['error']);

            return 1;
        }

        $this->info('✓ KudiSMS configuration present');

        // Send test SMS
        $result = $smsService->sendSms($phone, 'Test SMS from ABU Endowment - '.now()->format('Y-m-d H:i:s'), [
            'tag' => 'test',
            'submit_report' => true,
            'delivery_report' => true,
        ]);

        if ($result['success']) {
            $this->info('✓ SMS sent successfully!');
            $this->info('Message ID: '.$result['message_id']);
            $this->info('Status: '.$result['status']);
        } else {
            $this->error('✗ SMS sending failed:');
            $this->error($result['error']);

            return 1;
        }

        return 0;
    }
}
