<?php

namespace App\Services;

use App\Models\Donation;
use Illuminate\Support\Facades\Mail;

class PaymentNotificationService
{
    public function send(Donation $donation): void
    {
        app(PaymentSmsService::class)->send($donation, 'squad');
        // Claim once before external effects: retries cannot duplicate an email.
        // A crash/failure requires manual reconciliation; this is not exactly-once mail delivery.
        $claim = app(SquadPaymentService::class)->event($donation, 'notification.claimed');
        if (! $claim->wasRecentlyCreated) {
            return;
        }
        $donation->load('donor', 'project');
        $tierSent = app(TierNotificationService::class)->handleDonationTierCheck($donation);
        if ($tierSent !== null) {
            app(SquadPaymentService::class)->event($donation, $tierSent ? 'notification.sent' : 'notification.failed');

            return;
        }
        $donor = $donation->donor;
        if (! $donor?->email) {
            return;
        }
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
        app(SquadPaymentService::class)->event($donation, 'notification.sent');
    }
}
