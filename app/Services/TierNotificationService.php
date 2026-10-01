<?php

namespace App\Services;

use App\Models\Donation;
use App\Models\Donor;
use App\Models\DonorTier;
use App\Models\EmailTemplate;
use App\Models\EmailLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TierNotificationService
{
    /** Returns null when no tier receipt applies, true on delivery, false on failure. */
    public function handleDonationTierCheck(Donation $donation): ?bool
    {
        try {
            $donor = $donation->donor ?? Donor::find($donation->donor_id);
            if (!$donor || !$donor->email) {
                return null;
            }

            // Sum all completed donations including the current one
            $total = Donation::where('donor_id', $donor->id)
                ->where('status', 'completed')
                ->sum('amount');

            // Find the highest tier the donor qualifies for
            $currentTier = DonorTier::where('is_active', true)
                ->where('min_amount', '<=', $total)
                ->orderBy('sort_order', 'desc')
                ->first();

            if (!$currentTier) {
                return null;
            }

            // Always keep donor tier in sync
            $donor->update(['donor_tier_id' => $currentTier->id]);

            // Find active email template for this tier
            $template = EmailTemplate::where('donor_tier_id', $currentTier->id)
                ->where('is_active', true)
                ->first();

            if (!$template) {
                Log::info('No active email template found for tier', [
                    'tier_id'   => $currentTier->id,
                    'tier_name' => $currentTier->name,
                ]);
                return null;
            }

            return $this->sendTierEmail($donor, $currentTier, $template, $donation, (float) $total);

        } catch (\Exception $e) {
            Log::error('TierNotificationService error', [
                'donation_id' => $donation->id,
                'exception' => get_class($e),
            ]);
        }
        return false;
    }

    private function sendTierEmail(
        Donor $donor,
        DonorTier $tier,
        EmailTemplate $template,
        Donation $donation,
        float $total
    ): bool {
        $donorName = trim("{$donor->surname} {$donor->name}") ?: $donor->email;

        $logoUrl = 'https://abu-endowment.cloud/abu_logo_white_for_email.png';

        $receiptDate = ($donation->paid_at ?? $donation->verified_at ?? $donation->created_at)?->format('d M Y') ?? '';

        $variables = [
            'donor_name'        => $donorName,
            'donor_email'       => $donor->email,
            'tier_name'         => $tier->name,
            'total_amount'      => '₦' . number_format($total, 2),
            'amount'            => '₦' . number_format((float) $donation->amount, 2),
            'reference'         => $donation->payment_reference ?? '',
            'donation_date'     => $receiptDate,
            'created_at'        => $receiptDate,
            'project_name'      => $donation->project?->project_title ?? 'GIVE ABU',
            'organization_name' => $donor->organization_name ?? 'GIVE ABU',
            'logo_url'          => $logoUrl,
        ];

        // Decode HTML entities in case the template was saved via the old Trix editor
        $rawHtml = html_entity_decode($template->body_html ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $rawHtml = str_replace('ABU Endowment Fund Team', 'GiveABU Team', $rawHtml);
        $rawHtml = preg_replace(
            '/©\s*\d{4}\s+[^<\r\n]+/u',
            '© '.now()->year.' ABU. All rights reserved. Powered by @KADICT Hub.',
            $rawHtml
        );

        // Replace any logo filename references with the absolute production URL
        $rawHtml = $this->fixLogoImageUrls($rawHtml, $logoUrl);

        $bodyHtml = $this->replaceVariables($rawHtml, $variables);
        $subject  = $this->replaceVariables(
            $template->subject ?: "Thank you for your donation — {$tier->name}",
            $variables
        );

        $log = EmailLog::create([
            'recipient_email' => $donor->email,
            'recipient_name'  => $donorName,
            'template_id'     => $template->id,
            'status'          => 'pending',
            'sent_at'         => null,
        ]);

        try {
            Mail::html($bodyHtml, function ($message) use ($donor, $subject) {
                $message->to($donor->email)
                        ->subject($subject)
                        ->from(
                            config('mail.from.address', 'abuendowment@gmail.com'),
                            config('mail.from.name', 'GIVE ABU')
                        );
            });

            $log->update(['status' => 'sent', 'sent_at' => now()]);

            Log::info('Tier donation email sent', [
                'donor_id'  => $donor->id,
                'tier_name' => $tier->name,
                'email'     => $donor->email,
                'amount'    => $donation->amount,
            ]);
            return true;
        } catch (\Exception $e) {
            $log->update([
                'status'        => 'failed',
                'error_message' => 'Email delivery failed',
            ]);
            Log::error('Failed to send tier donation email', [
                'donor_id' => $donor->id,
                'exception' => get_class($e),
            ]);
        }
        return false;
    }

    /**
     * Replace any logo src references (relative or absolute) with the production URL.
     */
    private function fixLogoImageUrls(string $html, string $logoUrl): string
    {
        $filenames = ['abu_logo_white_for_email.png', 'abu_logo.png'];

        foreach ($filenames as $filename) {
            $html = preg_replace(
                '/src=["\'][^"\']*' . preg_quote($filename, '/') . '["\']/i',
                'src="' . $logoUrl . '"',
                $html
            );
        }

        return $html;
    }

    /**
     * Replace template variables. Handles {{var}}, {{ var }}, Blade-compiled
     * php-echo-e(var) formats, and [var] bracket style.
     */
    private function replaceVariables(string $text, array $variables): string
    {
        // Build PHP tag strings via concatenation so the parser never sees them
        $open  = '<' . '?php';
        $close = '?' . '>';

        foreach ($variables as $key => $value) {
            // Standard {{var}} and {{ var }}
            $text = str_replace('{{' . $key . '}}', $value, $text);
            $text = str_replace('{{ ' . $key . ' }}', $value, $text);

            // Blade-compiled: echo e(var) and echo e($var)
            $text = str_replace($open . ' echo e(' . $key . '); ' . $close, $value, $text);
            $text = str_replace($open . ' echo e($' . $key . '); ' . $close, $value, $text);

            // Bracket style [var]
            $text = str_replace('[' . $key . ']', $value, $text);
        }
        return $text;
    }
}
