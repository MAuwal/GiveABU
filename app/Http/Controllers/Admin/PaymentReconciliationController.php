<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\VerifyPendingPayment;
use App\Models\Donation;
use App\Services\PaymentNotificationOutbox;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

class PaymentReconciliationController extends Controller
{
    public function index()
    {
        $pending = Donation::with(['donor', 'project', 'transactions' => fn ($query) => $query->select('id', 'donation_id', 'payment_gateway', 'event_type')->orderByDesc('id')])->where('status', 'pending')->orderBy('created_at')->paginate(20, ['id', 'donor_id', 'project_id', 'payment_reference', 'amount', 'status', 'created_at'], 'payments_page');
        $receipts = DB::table('payment_notification_outbox')->where('status', '!=', 'delivered')->orderBy('created_at')->paginate(20, ['id', 'donation_id', 'gateway', 'status', 'published_at', 'updated_at'], 'receipts_page');
        $failed = DB::table('failed_jobs')->orderByDesc('failed_at')->paginate(20, ['id', 'uuid', 'connection', 'queue', 'failed_at'], 'failed_page');
        $queueSizes = [];
        foreach (['payments', 'notifications'] as $queue) {
            try {
                $queueSizes[$queue] = Queue::size($queue);
            } catch (\Throwable $e) {
                $queueSizes[$queue] = 'Unavailable';
            }
        }

        return response()->view('admin.payment-reconciliation', compact('pending', 'receipts', 'failed', 'queueSizes'))->header('Cache-Control', 'private, no-store');
    }

    public function verify(Donation $donation)
    {
        if ($donation->status !== 'pending') {
            return back()->with('success', 'Payment status: '.$donation->status.'.');
        }
        $gateways = $donation->transactions()->pluck('payment_gateway')->unique()->values();
        if (! $donation->payment_reference || $gateways->count() !== 1 || ! in_array($gateways[0], ['squad', 'interswitch'], true)) {
            return back()->with('success', 'Cannot verify this payment: its gateway binding requires reconciliation.');
        }
        try {
            // Reuse the integrity checks immediately; receipt delivery stays queued after commit.
            (new VerifyPendingPayment($donation->id))->handle();
            $donation->refresh();
            $message = match ($donation->status) {
                'completed' => 'Payment verified successfully. Status updated to completed; receipts are queued.',
                'failed' => 'The gateway confirmed payment failure. Status updated to failed.',
                default => 'Payment remains pending. The gateway has not confirmed a valid successful payment; review its latest event.',
            };
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Admin payment verification unavailable', ['donation_id' => $donation->id, 'exception' => get_class($e)]);
            $message = 'Verification is temporarily unavailable. Payment state is preserved; try again shortly.';
        }

        return back()->with('success', $message);
    }

    public function publish(int $outbox)
    {
        app(PaymentNotificationOutbox::class)->publish($outbox);

        return back()->with('success', 'Pending receipt checked for queue publication. Failed delivery claims require manual reconciliation.');
    }
}
