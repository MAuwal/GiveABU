<?php

namespace App\Livewire\Admin;

use App\Http\Controllers\InterswitchPaymentController;
use App\Models\Donation;
use App\Models\Donor;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;

class PaymentTransactions extends Component
{
    use WithPagination;

    public $search = '';
    public $gateway = '';
    public $status = '';
    public $category = '';
    public $period = '';
    public $perPage = 15;
    public $selectedTransaction;
    public $showDetailsModal = false;
    public array $donorStats = [];
    public $actionMessage = '';
    public $actionMessageType = 'info';

    protected $queryString = [
        'search'   => ['except' => ''],
        'gateway'  => ['except' => ''],
        'status'   => ['except' => ''],
        'category' => ['except' => ''],
        'period'   => ['except' => ''],
        'perPage'  => ['except' => 15],
    ];

    public function mount()
    {
        // Only backfill if there are donations with no matching transaction record.
        // Uses a fast subquery count instead of loading all refs into PHP memory.
        $missing = Donation::whereNotNull('payment_reference')
            ->whereNotExists(function ($q) {
                $q->from('payment_transactions')
                  ->whereColumn('payment_transactions.payment_reference', 'donations.payment_reference');
            })
            ->count();

        if ($missing > 0) {
            $this->backfillDonationsToTransactions();
        }

        $this->runAutoVerificationSafely();
    }

    /**
     * Throttled auto-verification on page load — uses a cache lock so only one
     * request performs verification within any 45-second window.
     */
    private function runAutoVerificationSafely(): void
    {
        $lock = Cache::lock('admin-transactions-auto-verify-lock', 20);
        if (!$lock->get()) {
            return;
        }

        try {
            if (!Cache::add('admin-transactions-auto-verify-throttle', 1, now()->addSeconds(45))) {
                return;
            }

            $this->verifyLatestUnresolvedTransactions(5);
        } finally {
            optional($lock)->release();
        }
    }

    /**
     * Verify the N most-recent pending/failed Squad & Interswitch transactions.
     */
    private function verifyLatestUnresolvedTransactions(int $limit = 5): void
    {
        $records = PaymentTransaction::whereIn('payment_gateway', ['squad', 'interswitch'])
            ->whereIn('status', ['pending', 'failed'])
            ->whereNotNull('payment_reference')
            ->orderByDesc('id')
            ->limit(max(1, $limit))
            ->get();

        if ($records->isEmpty()) {
            return;
        }

        $interswitchController = app(InterswitchPaymentController::class);

        foreach ($records as $transaction) {
            if ($transaction->payment_gateway === 'interswitch') {
                try {
                    $interswitchController->verifyApi($transaction->payment_reference);
                } catch (\Throwable $e) {
                    Log::warning('Auto-verify Interswitch failed on admin transactions page load', [
                        'transaction_id'    => $transaction->id,
                        'payment_reference' => $transaction->payment_reference,
                        'error'             => $e->getMessage(),
                    ]);
                }
                continue;
            }

            if ($transaction->payment_gateway === 'squad') {
                $this->syncSquadTransactionFromGateway($transaction);
            }
        }
    }

    /**
     * Call Squad's verify API for a single transaction and sync the local record.
     */
    private function syncSquadTransactionFromGateway(PaymentTransaction $transaction): void
    {
        if ($transaction->payment_reference) {
            app(\App\Services\SquadPaymentService::class)->verify($transaction->payment_reference);
        }
    }

    public function backfillDonationsToTransactions(): void
    {
        Donation::with('donor', 'project')
            ->whereNotNull('payment_reference')
            ->whereNotExists(function ($q) {
                $q->from('payment_transactions')
                  ->whereColumn('payment_transactions.payment_reference', 'donations.payment_reference');
            })
            ->each(function ($donation) {
                $ref     = $donation->payment_reference;
                $gateway = str_contains($ref, '_SQUAD_') ? 'squad' : 'paystack';
                $status  = match($donation->status) {
                    'completed' => 'completed',
                    'failed'    => 'failed',
                    default     => 'pending',
                };
                $event = match($status) {
                    'completed' => 'charge.success',
                    'failed'    => 'charge.failed',
                    default     => 'payment.initialized',
                };

                PaymentTransaction::create([
                    'donation_id'       => $donation->id,
                    'donor_id'          => $donation->donor_id,
                    'project_id'        => $donation->project_id,
                    'payment_gateway'   => $gateway,
                    'category'          => $donation->project_id ? 'project' : 'general',
                    'event_type'        => $event,
                    'payment_reference' => $ref,
                    'gateway_reference' => $ref,
                    'amount'            => $donation->amount,
                    'currency'          => 'NGN',
                    'status'            => $status,
                    'gateway_status'    => $status,
                    'channel'           => null,
                    'fee'               => 0,
                    'response_payload'  => null,
                ]);
            });
    }

    public function updatingSearch()   { $this->resetPage(); }
    public function updatingGateway()  { $this->resetPage(); }
    public function updatingStatus()   { $this->resetPage(); }
    public function updatingCategory() { $this->resetPage(); }
    public function updatingPeriod()   { $this->resetPage(); }

    private function applyPeriod($query)
    {
        return match($this->period) {
            'today' => $query->whereDate('created_at', today()),
            'week'  => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]),
            'month' => $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year),
            'year'  => $query->whereYear('created_at', now()->year),
            default => $query,
        };
    }

    public function getFilteredTotals(): array
    {
        $base = PaymentTransaction::query()
            ->when($this->gateway,  fn($q) => $q->where('payment_gateway', $this->gateway))
            ->when($this->status,   fn($q) => $q->where('status', $this->status))
            ->when($this->category, fn($q) => $q->where('category', $this->category))
            ->when($this->search, function ($q) {
                $s = '%' . $this->search . '%';
                $q->where(fn($sub) => $sub
                    ->where('payment_reference', 'like', $s)
                    ->orWhere('payment_gateway', 'like', $s)
                    ->orWhere('status', 'like', $s)
                    ->orWhereHas('donor', fn($d) => $d->where('name', 'like', $s)->orWhere('surname', 'like', $s)->orWhere('email', 'like', $s))
                );
            });
        $base = $this->applyPeriod($base);

        return [
            'total'     => (float) (clone $base)->sum('amount'),
            'count'     => (int)   (clone $base)->count(),
            'completed' => (float) (clone $base)->whereIn('status', ['completed', 'success'])->sum('amount'),
            'pending'   => (float) (clone $base)->where('status', 'pending')->sum('amount'),
        ];
    }

    public function viewTransaction($id)
    {
        $this->selectedTransaction = PaymentTransaction::with(['donation.project', 'donor', 'project'])->find($id);
        $this->showDetailsModal = true;
        $this->donorStats = [];

        $donorId = $this->selectedTransaction?->donor_id;
        if ($donorId) {
            $donations = Donation::where('donor_id', $donorId);
            $txns      = PaymentTransaction::where('donor_id', $donorId);

            $this->donorStats = [
                'total_donated'        => (float) (clone $donations)->whereIn('status', ['completed', 'success'])->sum('amount'),
                'total_donations'      => (int)   (clone $donations)->count(),
                'successful_donations' => (int)   (clone $donations)->whereIn('status', ['completed', 'success'])->count(),
                'total_txns'           => (int)   (clone $txns)->count(),
                'successful_txns'      => (int)   (clone $txns)->whereIn('status', ['completed', 'success'])->count(),
                'first_donation'       => (clone $donations)->min('created_at'),
            ];
        }
    }

    public function openExcelExporter(): void
    {
        $this->dispatch('openExcelExporter',
            context:  'transactions',
            dateFrom: '',
            dateTo:   '',
            search:   $this->search,
            gateway:  $this->gateway,
            status:   $this->status,
            period:   $this->period,
        );
    }

    public function closeModal()
    {
        $this->showDetailsModal = false;
        $this->selectedTransaction = null;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Bulk verification actions (callable from the view via wire:click)
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Verify up to $limit pending/failed Squad transactions by calling the
     * Squad API. Creates a charge.success/charge.failed event record as needed.
     */
    public function verifyPendingSquadTransactions(int $limit = 50): void
    {
        $records = PaymentTransaction::where('payment_gateway', 'squad')->whereIn('status', ['pending', 'failed'])
            ->orderByDesc('id')->limit(max(1, $limit))->get()->unique('payment_reference');
        foreach ($records as $record) {
            if ($record->payment_reference) {
                app(\App\Services\SquadPaymentService::class)->verify($record->payment_reference);
            }
        }
    }

    /**
     * Verify up to $limit pending/failed Interswitch transactions via the
     * InterswitchPaymentController::verifyApi() helper.
     */
    public function verifyPendingInterswitchTransactions(int $limit = 50): void
    {
        $interswitchController = app(InterswitchPaymentController::class);

        $pendingTransactions = PaymentTransaction::where('payment_gateway', 'interswitch')
            ->whereIn('status', ['pending', 'failed'])
            ->orderByDesc('id')
            ->limit(max(1, $limit))
            ->get();

        foreach ($pendingTransactions as $transaction) {
            $reference = $transaction->payment_reference;
            if (!$reference) {
                continue;
            }

            try {
                $interswitchController->verifyApi($reference);
            } catch (\Throwable $e) {
                Log::warning('Bulk Interswitch verify failed', [
                    'transaction_id'    => $transaction->id,
                    'payment_reference' => $reference,
                    'error'             => $e->getMessage(),
                ]);
            }
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Single-transaction manual verification actions
    // ──────────────────────────────────────────────────────────────────────────

    public function verifyInterswitch($id): void
    {
        $transaction = PaymentTransaction::find($id);

        if (!$transaction) {
            $this->setActionMessage('error', 'Transaction not found.');
            return;
        }

        if ($transaction->payment_gateway !== 'interswitch') {
            $this->setActionMessage('error', 'Only Interswitch transactions can be verified here.');
            return;
        }

        $reference = $transaction->payment_reference;
        if (!$reference) {
            $this->setActionMessage('error', 'Missing payment reference for this transaction.');
            return;
        }

        try {
            $controller = app(InterswitchPaymentController::class);
            $response   = $controller->verifyApi($reference);
            $payload    = $response->getData(true);

            $success = (bool) ($payload['success'] ?? false);
            $status  = strtolower((string) ($payload['data']['status'] ?? ''));
            $message = (string) ($payload['message'] ?? 'Verification completed.');

            if ($success || in_array($status, ['completed', 'success'], true)) {
                $this->setActionMessage('success', 'Interswitch verified successfully. Local record has been updated.');
            } elseif ($status === 'pending') {
                $this->setActionMessage('warning', 'Gateway still reports this transaction as pending.');
            } else {
                $this->setActionMessage('error', $message !== '' ? $message : 'Verification returned a failed status.');
            }

            if ($this->selectedTransaction && (int) $this->selectedTransaction->id === (int) $id) {
                $this->selectedTransaction = PaymentTransaction::with(['donation.project', 'donor', 'project'])->find($id);
            }
        } catch (\Throwable $e) {
            Log::error('Manual Interswitch verify failed from admin transactions', [
                'transaction_id'    => $id,
                'payment_reference' => $reference,
                'error'             => $e->getMessage(),
            ]);
            $this->setActionMessage('error', 'Verification failed due to a server error.');
        }
    }

    public function verifySquad($id): void
    {
        $record = PaymentTransaction::find($id);
        if (!$record || $record->payment_gateway !== 'squad' || !$record->payment_reference) {
            $this->setActionMessage('error', 'Squad payment reference not found.');
            return;
        }
        try {
            $result = app(\App\Services\SquadPaymentService::class)->verify($record->payment_reference);
            $this->setActionMessage($result['success'] ? 'success' : 'warning', 'Payment confirmation: '.$result['outcome']);
            if ($this->selectedTransaction && (int) $this->selectedTransaction->id === (int) $id) {
                $this->selectedTransaction = $record->fresh(['donation.project', 'donor', 'project']);
            }
        } catch (\Throwable $e) {
            $this->setActionMessage('error', 'Payment confirmation is temporarily unavailable.');
        }
    }

    private function setActionMessage(string $type, string $message): void
    {
        $this->actionMessageType = $type;
        $this->actionMessage     = $message;
    }

    public function getChartData(): array
    {
        $days   = 30;
        $start  = now()->subDays($days - 1)->startOfDay();
        $labels = [];
        for ($i = 0; $i < $days; $i++) {
            $labels[] = now()->subDays($days - 1 - $i)->format('M d');
        }

        $isSqlite = DB::connection()->getDriverName() === 'sqlite';
        $daySql   = $isSqlite ? "strftime('%Y-%m-%d', created_at)" : "DATE_FORMAT(created_at, '%Y-%m-%d')";

        $rows = PaymentTransaction::select(
                DB::raw("{$daySql} as day"),
                'payment_gateway',
                DB::raw('SUM(amount) as total')
            )
            ->where('created_at', '>=', $start)
            ->whereIn('status', ['completed', 'success'])
            ->groupBy('day', 'payment_gateway')
            ->get()
            ->groupBy('payment_gateway');

        $fill = function ($gateway) use ($labels, $rows, $days) {
            $map  = [];
            foreach ($rows[$gateway] ?? [] as $row) {
                $map[$row->day] = (float) $row->total;
            }
            $data = [];
            for ($i = 0; $i < $days; $i++) {
                $key    = now()->subDays($days - 1 - $i)->format('Y-m-d');
                $data[] = $map[$key] ?? 0;
            }
            return $data;
        };

        $totalPaystack   = array_sum($fill('paystack'));
        $totalSquad      = array_sum($fill('squad'));
        $totalInterswitch = array_sum($fill('interswitch'));
        $totalAll        = $totalPaystack + $totalSquad + $totalInterswitch;

        return [
            'labels'      => $labels,
            'paystack'    => $fill('paystack'),
            'squad'       => $fill('squad'),
            'interswitch' => $fill('interswitch'),
            'totals'      => [
                'all'         => number_format($totalAll, 2),
                'paystack'    => number_format($totalPaystack, 2),
                'squad'       => number_format($totalSquad, 2),
                'interswitch' => number_format($totalInterswitch, 2),
                'count'       => PaymentTransaction::whereIn('status', ['completed', 'success'])->count(),
            ],
        ];
    }

    public function render()
    {
        $query = PaymentTransaction::with(['donation.project', 'donor', 'project'])
            ->when($this->gateway,  fn($q) => $q->where('payment_gateway', $this->gateway))
            ->when($this->status,   fn($q) => $q->where('status', $this->status))
            ->when($this->category, fn($q) => $q->where('category', $this->category))
            ->when($this->search, function ($q) {
                $s = '%' . $this->search . '%';
                $q->where(fn($sub) => $sub
                    ->where('payment_reference', 'like', $s)
                    ->orWhere('gateway_reference', 'like', $s)
                    ->orWhere('payment_gateway', 'like', $s)
                    ->orWhere('event_type', 'like', $s)
                    ->orWhere('status', 'like', $s)
                    ->orWhereHas('donor', fn($d) => $d->where('name', 'like', $s)->orWhere('surname', 'like', $s)->orWhere('other_name', 'like', $s)->orWhere('email', 'like', $s))
                    ->orWhereHas('project', fn($p) => $p->where('project_title', 'like', $s))
                );
            });

        $query = $this->applyPeriod($query);

        return view('livewire.admin.payments.transactions', [
            'transactions'    => $query->latest()->paginate($this->perPage),
            'chartData'       => $this->getChartData(),
            'filteredTotals'  => $this->getFilteredTotals(),
        ]);
    }
}
