<?php

namespace App\Http\Controllers;

use App\Models\Donation;

class PaymentStatusController extends Controller
{
    public function __invoke(Donation $donation)
    {
        if ($donation->status === 'pending') {
            $gateways = $donation->transactions()->pluck('payment_gateway')->unique()->values();
            try {
                if ($gateways->count() === 1 && $gateways[0] === 'squad') {
                    app(\App\Services\SquadPaymentService::class)->verify($donation->payment_reference);
                } elseif ($gateways->count() === 1 && $gateways[0] === 'interswitch') {
                    app(\App\Http\Controllers\InterswitchPaymentController::class)->verifyApi($donation->payment_reference);
                }
            } catch (\Throwable $e) {
                // Provider outages leave the financial record recoverable.
            }
            $donation->refresh();
        }

        return response()->json(['status' => $donation->status])->header('Cache-Control', 'private, no-store');
    }
}
