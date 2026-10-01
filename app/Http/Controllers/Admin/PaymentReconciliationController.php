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
        $pending = Donation::where('status', 'pending')->orderBy('created_at')->paginate(20, ['id', 'payment_reference', 'amount', 'created_at'], 'payments_page');
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
        if ($donation->status === 'pending') {
            VerifyPendingPayment::dispatch($donation->id);
        }

        return back()->with('success', 'Payment status check queued. Confirmation still requires gateway verification.');
    }

    public function publish(int $outbox)
    {
        app(PaymentNotificationOutbox::class)->publish($outbox);

        return back()->with('success', 'Pending receipt checked for queue publication. Failed delivery claims require manual reconciliation.');
    }
}
