<?php

namespace App\Services;

use App\Models\Donation;
use Illuminate\Support\Facades\Mail;

class PaymentNotificationService
{
    public function send(Donation $donation, string $gateway = 'squad'): void
    {
        app(PaymentSmsService::class)->send($donation, $gateway);
        $donation->load('donor', 'project');
        if (! $donation->donor?->email) {
            return;
        }
        // Claim once before external effects: retries cannot duplicate an email.
        // A crash/failure requires manual reconciliation; this is not exactly-once mail delivery.
        $claim = $this->event($donation, $gateway, 'notification.claimed');
        if (! $claim->wasRecentlyCreated) {
            return;
        }
        $donation->load('donor', 'project');
        $tierSent = app(TierNotificationService::class)->handleDonationTierCheck($donation);
        if ($tierSent !== null) {
            $this->event($donation, $gateway, $tierSent ? 'notification.sent' : 'notification.failed');

            return;
        }
        $donor = $donation->donor;
        if (! $donor?->email) {
            return;
        }
        try {
            Mail::send('emails.thank-you', [
                'donorName' => trim("{$donor->surname} {$donor->name}") ?: 'Valued Donor',
                'amount' => number_format((float) $donation->amount, 2),
                'tierName' => $donor->donor_tier_id ? ($donor->tier?->name ?? 'General Supporter') : 'General Supporter',
                'reference' => $donation->payment_reference,
                'projectName' => $donation->project?->project_title ?? 'GIVE ABU',
                'donationDate' => $donation->paid_at ?? $donation->verified_at ?? $donation->created_at,
                'donationType' => $donation->endowment === 'yes' ? 'GIVE ABU Fund' : 'Project Donation',
                'logoUrl' => 'https://abu-endowment.cloud/abu_logo_white_for_email.png',
            ], function ($message) use ($donor) {
                $message->to($donor->email)->subject('Thank You for Your Generous Donation - GIVE ABU');
            });
        } catch (\Throwable $e) {
            $this->event($donation, $gateway, 'notification.failed');
            throw new \RuntimeException('Email delivery failed');
        }
        $this->event($donation, $gateway, 'notification.sent');
    }

    private function event(Donation $donation, string $gateway, string $event)
    {
        if ($gateway === 'squad') {
            return app(SquadPaymentService::class)->event($donation, $event);
        }

        return \App\Models\PaymentTransaction::firstOrCreate([
            'event_key' => hash('sha256', $gateway.'|'.$donation->payment_reference.'|'.$event),
        ], ['donation_id' => $donation->id, 'donor_id' => $donation->donor_id,
            'payment_gateway' => $gateway, 'payment_reference' => $donation->payment_reference,
            'event_type' => $event, 'status' => 'completed', 'amount' => $donation->amount, 'currency' => 'NGN']);
    }
}
