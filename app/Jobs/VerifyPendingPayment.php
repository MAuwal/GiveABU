<?php

namespace App\Jobs;

use App\Http\Controllers\InterswitchPaymentController;
use App\Models\Donation;
use App\Services\SquadPaymentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\Middleware\WithoutOverlapping;

class VerifyPendingPayment implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, Queueable;

    public int $tries = 3;

    public int $timeout = 120;

    public int $uniqueFor = 300;

    public function __construct(public int $donationId)
    {
        $this->onQueue('payments');
        $this->afterCommit();
    }

    public function uniqueId(): string
    {
        return (string) $this->donationId;
    }

    public function middleware(): array
    {
        return [(new WithoutOverlapping('payment-recovery:'.$this->donationId))->shared()->releaseAfter(30)->expireAfter(150)];
    }

    public function backoff(): array
    {
        return [30, 120, 300];
    }

    public function handle(): void
    {
        $donation = Donation::find($this->donationId);
        if (! $donation || $donation->status !== 'pending' || ! $donation->payment_reference) {
            return;
        }
        $gateways = $donation->transactions()->pluck('payment_gateway')->unique()->values();
        if ($gateways->count() !== 1) {
            return; // Ambiguous or unbound payments require reconciliation.
        }
        if ($gateways[0] === 'squad') {
            $result = app(SquadPaymentService::class)->verify($donation->payment_reference);
            if ($result['outcome'] === 'unavailable') {
                throw new \RuntimeException('Payment provider temporarily unavailable');
            }
        } elseif ($gateways[0] === 'interswitch') {
            $response = app(InterswitchPaymentController::class)->verifyApi($donation->payment_reference);
            if ($response->getStatusCode() === 503) {
                throw new \RuntimeException('Payment provider temporarily unavailable');
            }
        }
    }
}
