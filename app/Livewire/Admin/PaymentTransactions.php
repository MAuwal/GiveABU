<?php

namespace App\Livewire\Admin;

use App\Services\AdminPaymentQuery;

use App\Models\Donation;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\DB;
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

    private function verifyLogicalPayments(string $gateway, int $limit): void
    {
        $records = AdminPaymentQuery::query()->where('status', 'pending')
            ->where('payment_gateway', $gateway)->orderByDesc('id')->limit(max(1, min($limit, 100)))->get();
        foreach ($records as $record) {
            $this->verifyPayment($record->id);
        }
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
        $base = $this->filteredPayments();

        return [
            'total'     => (float) (clone $base)->sum('amount'),
            'count'     => (int)   (clone $base)->count(),
            'completed' => (float) (clone $base)->whereIn('status', ['completed', 'success'])->sum('amount'),
            'pending'   => (float) (clone $base)->where('status', 'pending')->sum('amount'),
        ];
    }

    public function viewTransaction($id)
    {
        $this->selectedTransaction = AdminPaymentQuery::query()->with(['donor', 'project'])->findOrFail($id);
        $this->showDetailsModal = true;
        $donations = Donation::where('donor_id', $this->selectedTransaction->donor_id);
        if (! $this->selectedTransaction->donor_id) {
            $donations->whereRaw('1 = 0');
        }
        $this->donorStats = [
            'total_donated' => (float) (clone $donations)->where('status', 'completed')->sum('amount'),
            'total_donations' => (clone $donations)->count(),
            'successful_donations' => (clone $donations)->where('status', 'completed')->count(),
            'total_txns' => (clone $donations)->count(),
            'successful_txns' => (clone $donations)->where('status', 'completed')->count(),
            'first_donation' => (clone $donations)->min('created_at'),
        ];
    }

    public function openExcelExporter(): void
    {
        $this->dispatch('openExcelExporter',
            context:  'transactions',
            dateFrom: '',
            dateTo:   '',
            search:   $this->search,
            gateway:  $this->gateway,
            category: $this->category,
            status:   $this->status,
            period:   $this->period,
        );
    }

    public function closeModal()
    {
        $this->showDetailsModal = false;
        $this->selectedTransaction = null;
    }

    public function verifyPendingSquadTransactions(int $limit = 50): void
    {
        $this->verifyLogicalPayments('squad', $limit);
    }

    public function verifyPendingInterswitchTransactions(int $limit = 50): void
    {
        $this->verifyLogicalPayments('interswitch', $limit);
    }

    public function verifySquad($id): void { $this->verifyPayment($id, 'squad'); }
    public function verifyInterswitch($id): void { $this->verifyPayment($id, 'interswitch'); }

    private function verifyPayment($id, ?string $gateway = null): void
    {
        $donation = AdminPaymentQuery::query()->findOrFail($id);
        if ($donation->status !== 'pending' || ! in_array($donation->payment_gateway, ['squad', 'interswitch'], true)
            || ($gateway && $gateway !== $donation->payment_gateway)) {
            $this->actionMessage = 'Payment does not require verification or its gateway binding requires reconciliation.';
            $this->actionMessageType = 'warning';
            return;
        }
        try {
            (new \App\Jobs\VerifyPendingPayment($donation->id))->handle();
            $status = Donation::findOrFail($id)->status;
            $this->actionMessage = 'Payment status: '.$status;
            $this->actionMessageType = $status === 'completed' ? 'success' : 'warning';
        } catch (\Throwable $e) {
            $this->actionMessage = 'Gateway verification temporarily unavailable. Payment remains recoverable.';
            $this->actionMessageType = 'warning';
        }
        if ($this->selectedTransaction && $this->selectedTransaction->id == $id) {
            $this->viewTransaction($id);
        }
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

        $rows = $this->filteredPayments()->select(
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
        $totalAll = (float) $this->filteredPayments()->where('status', 'completed')->where('created_at', '>=', $start)->sum('amount');

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
                'count'       => $this->filteredPayments()->where('status', 'completed')->where('created_at', '>=', $start)->count(),
            ],
        ];
    }

    private function filteredPayments()
    {
        $query = AdminPaymentQuery::query()->with(['donor', 'project'])
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

        return $this->applyPeriod($query);

    }

    public function render()
    {
        if ($this->selectedTransaction) {
            $this->viewTransaction($this->selectedTransaction->id);
        }
        $query = $this->filteredPayments();

        return view('livewire.admin.payments.transactions', [
            'transactions'    => $query->latest()->paginate($this->perPage),
            'chartData'       => $this->getChartData(),
            'filteredTotals'  => $this->getFilteredTotals(),
            'paymentEvents' => $this->selectedTransaction ? PaymentTransaction::where(fn ($q) => $q->where('donation_id', $this->selectedTransaction->id)->when($this->selectedTransaction->payment_reference, fn ($q) => $q->orWhere('payment_reference', $this->selectedTransaction->payment_reference)))->orderBy('created_at')->orderBy('id')->get() : collect(),
        ]);
    }
}
