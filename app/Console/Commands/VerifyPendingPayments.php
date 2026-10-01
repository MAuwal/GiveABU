<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Donation;
use App\Models\PaymentTransaction;
use App\Services\TierNotificationService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class VerifyPendingPayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'donations:verify-pending {--daemon : Run continuously every 10 seconds}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify pending payments by polling payment gateways';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('daemon')) {
            $this->info('Starting pending payment verification daemon (polling every 10 seconds)...');
            while (true) {
                $this->processPending();
                sleep(10);
            }
        } else {
            $this->info('Running 6 iterations of pending payment verification (1 minute)...');
            for ($i = 0; $i < 6; $i++) {
                $this->processPending();
                if ($i < 5) {
                    sleep(10);
                }
            }
            $this->info('Done.');
        }
        return Command::SUCCESS;
    }

    private function processPending()
    {
        // Preserve recovery for older pending donations as well.
        // We wait at least 5 seconds to avoid querying a transaction that was literally just inserted
        $donations = Donation::with(['donor', 'project', 'transactions'])
            ->where('status', 'pending')
            ->where('created_at', '<', now()->subSeconds(5))
            ->get();

        foreach ($donations as $donation) {
            $this->verifyDonation($donation);
        }
    }

    private function verifyDonation(Donation $donation)
    {
        // Get the latest transaction to determine gateway
        $transaction = $donation->transactions()->latest()->first();
        
        if (!$transaction) {
            return;
        }

        $gateway = strtolower($transaction->payment_gateway);
        $reference = $transaction->payment_reference ?? $donation->payment_reference;

        try {
            switch ($gateway) {
                case 'squad':
                    $this->verifySquad($donation, $reference);
                    break;
                case 'interswitch':
                    $this->verifyInterswitch($donation, $reference, $donation->amount);
                    break;
                case 'paystack':
                    $this->verifyPaystack($donation, $reference);
                    break;
            }
        } catch (\Exception $e) {
            Log::error("Error verifying {$gateway} payment for reference {$reference}", [
                'error' => $e->getMessage()
            ]);
        }
    }

    private function verifySquad(Donation $donation, $reference)
    {
        app(\App\Services\SquadPaymentService::class)->verify((string) $reference);
    }

    private function verifyInterswitch(Donation $donation, $reference, $amountNaira)
    {
        $merchantCode = config('services.interswitch.merchant_code');
        $baseUrl = rtrim(config('services.interswitch.base_url', 'https://sandbox.interswitchng.com'), '/');

        if (empty($merchantCode)) return;

        $amountMinor = (int) round($amountNaira * 100);

        $params = [
            'merchantcode' => $merchantCode,
            'transactionreference' => $reference,
            'amount' => $amountMinor,
        ];

        $response = Http::acceptJson()
            ->timeout(15)
            ->get($baseUrl . '/collections/api/v1/gettransaction.json', $params);

        if ($response->failed()) return;

        $data = $response->json();
        $responseCode = $data['ResponseCode'] ?? $data['responseCode'] ?? null;
        
        if (trim((string) $responseCode) === '00') {
            $amountPaid = isset($data['Amount']) ? ((int) $data['Amount'] / 100) : $amountNaira;
            $this->markAsCompleted($donation, 'interswitch', $reference, $amountPaid, '00', $data);
        }
    }

    private function verifyPaystack(Donation $donation, $reference)
    {
        $secretKey = config('services.paystack.secret_key');
        
        if (empty($secretKey)) return;

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $secretKey,
        ])->timeout(15)->get("https://api.paystack.co/transaction/verify/{$reference}");

        if ($response->failed()) return;

        $data = $response->json();
        $txData = data_get($data, 'data', []);
        
        $status = strtolower($txData['status'] ?? '');
        
        if ($status === 'success') {
            $amount = isset($txData['amount']) ? ($txData['amount'] / 100) : $donation->amount;
            $this->markAsCompleted($donation, 'paystack', $reference, $amount, $status, $data);
        } elseif ($status === 'failed') {
            $this->markAsFailed($donation, 'paystack', $reference, $status, $data);
        }
    }

    private function markAsCompleted(Donation $donation, $gateway, $reference, $amount, $gatewayStatus, $payload)
    {
        $this->info("Payment verified as completed for donation ID {$donation->id} ({$gateway})");

        $donation->update([
            'status' => 'completed',
            'amount' => $amount,
            'verified_at' => now(),
            'paid_at' => now(),
        ]);

        $fee = 0;
        if ($gateway === 'paystack') {
            $fee = data_get($payload, 'data.fees', 0) / 100;
        } elseif ($gateway === 'squad') {
            $fee = data_get($payload, 'data.fee', 0);
        }

        PaymentTransaction::updateOrCreate(
            [
                'payment_gateway' => $gateway,
                'payment_reference' => $reference,
            ],
            [
                'donation_id' => $donation->id,
                'donor_id' => $donation->donor_id,
                'project_id' => $donation->project_id,
                'category' => $donation->project_id ? 'project' : 'general',
                'event_type' => 'charge.success',
                'gateway_reference' => data_get($payload, 'data.transaction_ref', data_get($payload, 'data.reference', $reference)),
                'amount' => $amount,
                'currency' => 'NGN',
                'status' => 'completed',
                'gateway_status' => $gatewayStatus,
                'channel' => data_get($payload, 'data.channel', data_get($payload, 'data.payment_type', null)),
                'fee' => $fee,
                'response_payload' => json_encode($payload),
            ]
        );

        if ($donation->project_id) {
            $this->updateProjectRaised($donation->project_id);
        }

        $this->sendThankYouEmail($donation);
        (new TierNotificationService())->handleDonationTierCheck($donation);
    }

    private function markAsFailed(Donation $donation, $gateway, $reference, $gatewayStatus, $payload)
    {
        $this->info("Payment verified as failed for donation ID {$donation->id} ({$gateway})");

        $donation->update([
            'status' => 'failed',
            'verified_at' => now(),
        ]);

        PaymentTransaction::updateOrCreate(
            [
                'payment_gateway' => $gateway,
                'payment_reference' => $reference,
            ],
            [
                'donation_id' => $donation->id,
                'donor_id' => $donation->donor_id,
                'project_id' => $donation->project_id,
                'category' => $donation->project_id ? 'project' : 'general',
                'event_type' => 'charge.failed',
                'gateway_reference' => $reference,
                'amount' => $donation->amount,
                'currency' => 'NGN',
                'status' => 'failed',
                'gateway_status' => $gatewayStatus,
                'channel' => null,
                'fee' => 0,
                'response_payload' => json_encode($payload),
            ]
        );
    }

    private function updateProjectRaised($projectId)
    {
        if (!$projectId) {
            return false;
        }
        try {
            app(\App\Services\ProjectFundingService::class)->rebuild((int) $projectId);
            return true;
        } catch (\Throwable $e) {
            Log::error('Project total reconciliation failed', ['project_id' => $projectId, 'exception' => get_class($e)]);
            return false;
        }
    }

    private function sendThankYouEmail($donation)
    {
        try {
            $donor = $donation->donor;
            
            if (!$donor || !$donor->email) return;

            $donorName = $donor->full_name ?? trim("{$donor->surname} {$donor->name} {$donor->other_name}");
            $amount = number_format($donation->amount, 2);
            $reference = $donation->payment_reference;
            $projectName = $donation->project ? $donation->project->project_title : 'GIVE ABU';
            
            Mail::send('emails.thank-you', [
                'donorName'    => $donorName,
                'amount'       => $amount,
                'reference'    => $reference,
                'projectName'  => $projectName,
                'donationDate' => $donation->paid_at ?? now(),
                'donationType' => $donation->endowment === 'yes' ? 'GIVE ABU Fund' : 'Project Donation',
                'logoUrl'      => 'https://abu-endowment.cloud/abu_logo_white_for_email.png',
            ], function($message) use ($donor) {
                $message->from(config('mail.from.address', 'noreply@abu-endowment.edu.ng'), config('mail.from.name', 'GIVE ABU'))
                        ->to($donor->email)
                        ->subject('Thank You for Your Generous Donation - GIVE ABU');
            });
        } catch (\Exception $e) {
            Log::error('VerifyPendingPayments: Failed to send thank you email', [
                'donation_id' => $donation->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
