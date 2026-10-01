<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Donation;
use App\Models\PaymentTransaction;
use App\Models\Project;
use Illuminate\Database\Eloquent\Builder;

class ReportsManager extends Component
{
    use WithPagination;

    public string $search           = '';
    public string $selectedProgramme = '';
    public string $selectedProject  = '';
    public string $dateFrom         = '';
    public string $dateTo           = '';
    public int    $perPage          = 15;

    protected $queryString = [
        'search'            => ['except' => ''],
        'selectedProgramme' => ['except' => ''],
        'selectedProject'   => ['except' => ''],
        'dateFrom'          => ['except' => ''],
        'dateTo'            => ['except' => ''],
        'perPage'           => ['except' => 15],
    ];

    public function updated(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search            = '';
        $this->selectedProgramme = '';
        $this->selectedProject   = '';
        $this->dateFrom          = '';
        $this->dateTo            = '';
        $this->perPage           = 15;
        $this->resetPage();
    }

    private function applyFilters(Builder $query): Builder
    {
        return $query
            ->when($this->selectedProject, fn($q) => $q->where('project_id', $this->selectedProject))
            ->when($this->dateFrom, fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo,   fn($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->when($this->search, function ($q) {
                $s = '%' . $this->search . '%';
                $q->where(fn($sub) => $sub
                    ->whereHas('donor', fn($d) => $d
                        ->where('name', 'like', $s)
                        ->orWhere('surname', 'like', $s)
                        ->orWhere('email', 'like', $s)
                        ->orWhere('phone', 'like', $s)
                        ->orWhere('organization_name', 'like', $s)
                    )
                    ->orWhereHas('project', fn($p) => $p->where('project_title', 'like', $s))
                    ->orWhere('payment_reference', 'like', $s)
                    ->orWhere('status', 'like', $s)
                    ->orWhere('type', 'like', $s)
                );
            });
    }

    private function applyTxnFilters(Builder $query): Builder
    {
        return $query
            ->when($this->selectedProject, fn($q) => $q->where('project_id', $this->selectedProject))
            ->when($this->dateFrom, fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo,   fn($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->when($this->search, function ($q) {
                $s = '%' . $this->search . '%';
                $q->where(fn($sub) => $sub
                    ->whereHas('donor', fn($d) => $d
                        ->where('name', 'like', $s)
                        ->orWhere('surname', 'like', $s)
                        ->orWhere('email', 'like', $s)
                        ->orWhere('phone', 'like', $s)
                    )
                    ->orWhereHas('project', fn($p) => $p->where('project_title', 'like', $s))
                    ->orWhere('payment_reference', 'like', $s)
                    ->orWhere('gateway_reference', 'like', $s)
                    ->orWhere('payment_gateway', 'like', $s)
                    ->orWhere('status', 'like', $s)
                );
            });
    }

    public function getFilteredTotals(): array
    {
        $d = $this->applyFilters(Donation::query());
        $t = $this->applyTxnFilters(PaymentTransaction::query());

        return [
            'donations' => [
                'completed' => (float)(clone $d)->whereIn('status', ['completed', 'success', 'paid'])->sum('amount'),
                'pending'   => (float)(clone $d)->where('status', 'pending')->sum('amount'),
                'failed'    => (float)(clone $d)->where('status', 'failed')->sum('amount'),
                'count'     => (int)(clone $d)->count(),
            ],
            'transactions' => [
                'completed' => (float)(clone $t)->whereIn('status', ['completed', 'success'])->sum('amount'),
                'pending'   => (float)(clone $t)->where('status', 'pending')->sum('amount'),
                'failed'    => (float)(clone $t)->where('status', 'failed')->sum('amount'),
                'count'     => (int)(clone $t)->count(),
            ],
        ];
    }

    public function openExcelExporter(): void
    {
        $this->dispatch('openExcelExporter',
            context:   'reports',
            dateFrom:  $this->dateFrom,
            dateTo:    $this->dateTo,
            search:    $this->search,
            projectId: $this->selectedProject,
        );
    }

    public function render()
    {
        $donations = $this->applyFilters(Donation::query())
            ->with(['donor', 'project'])
            ->latest()
            ->paginate($this->perPage);

        return view('livewire.admin.reports-manager', [
            'donations'      => $donations,
            'projects'       => Project::withoutTrashed()->orderBy('project_title')->get(),
            'filteredTotals' => $this->getFilteredTotals(),
        ]);
    }
}
