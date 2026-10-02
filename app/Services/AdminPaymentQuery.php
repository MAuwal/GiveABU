<?php

namespace App\Services;

use App\Models\Donation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class AdminPaymentQuery
{
    public static function query(): Builder
    {
        // A financial row always comes from Donation. Event metadata never supplies
        // its amount, status or identity, and delivery events never supply fees.
        $base = Donation::query()->select('donations.*')
            ->selectRaw("CASE WHEN project_id IS NULL THEN 'general' ELSE 'project' END AS category")
            ->selectRaw("'NGN' AS currency")
            ->selectSub(DB::table('payment_transactions')->selectRaw("CASE WHEN COUNT(DISTINCT payment_gateway) = 1 THEN MIN(payment_gateway) ELSE 'unknown' END")
                ->whereColumn('donation_id', 'donations.id'), 'payment_gateway');
        foreach (['event_type', 'gateway_reference', 'channel', 'fee', 'response_payload'] as $column) {
            $metadata = DB::table('payment_transactions')->select($column)
                ->whereColumn('donation_id', 'donations.id')
                ->where('event_type', 'not like', 'sms.%')
                ->where('event_type', 'not like', 'notification.%')
                ->orderByDesc('created_at')->orderByDesc('id')->limit(1);
            if ($column === 'fee') {
                $metadata->whereIn('event_type', ['charge.success', 'payment.success']);
            }
            $base->selectSub($metadata, $column);
        }

        return Donation::query()->fromSub($base, 'donations')->select('donations.*');
    }
}
