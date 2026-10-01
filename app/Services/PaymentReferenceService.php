<?php

namespace App\Services;

use App\Models\Donation;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

class PaymentReferenceService
{
    public function generate(string $gateway): string
    {
        if (! in_array($gateway, ['squad', 'interswitch'], true)) {
            throw new \InvalidArgumentException('Unsupported payment gateway');
        }
        $shortId = bin2hex(random_bytes(4));

        return 'ABU_ZARIA_'.strtoupper($gateway).'_'.now()->year.'_'.$shortId;
    }

    public function create(array $attributes, string $gateway): Donation
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $reference = $this->generate($gateway);
            try {
                return DB::transaction(fn () => Donation::create(array_merge($attributes, ['payment_reference' => $reference])));
            } catch (UniqueConstraintViolationException $e) {
                if (! Donation::where('payment_reference', $reference)->exists() || $attempt === 4) {
                    throw $e;
                }
            }
        }
        throw new \RuntimeException('Unable to allocate payment reference');
    }
}
