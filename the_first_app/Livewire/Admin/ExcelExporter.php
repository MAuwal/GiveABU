<?php

namespace App\Livewire\Admin;

use Livewire\Attributes\On;
use Livewire\Component;

class ExcelExporter extends Component
{
    public bool   $showModal  = false;
    public string $context    = 'reports';
    public string $dateFrom   = '';
    public string $dateTo     = '';
    public string $search     = '';
    public string $projectId  = '';
    public string $gateway    = '';
    public string $status     = '';
    public string $period     = '';
    public array  $sheets     = [];

    protected array $contextDefaults = [
        'reports'      => ['dashboard', 'donations', 'top_donors', 'trends'],
        'transactions' => ['dashboard', 'transactions', 'trends'],
        'statistics'   => ['dashboard', 'donations', 'transactions', 'top_donors', 'trends'],
        'donations'    => ['dashboard', 'donations', 'top_donors'],
    ];

    protected array $allSheets = [
        'dashboard'    => ['label' => 'Dashboard Summary',    'icon' => '📊', 'desc' => 'KPI cards, gateway split, donor types'],
        'donations'    => ['label' => 'Donations Detail',     'icon' => '💚', 'desc' => 'Full donations table with all fields'],
        'transactions' => ['label' => 'Transactions Detail',  'icon' => '💳', 'desc' => 'Full transactions with gateway & fees'],
        'top_donors'   => ['label' => 'Top Donors Ranking',   'icon' => '🏆', 'desc' => 'Top 20 donors by total donated'],
        'trends'       => ['label' => 'Monthly Trends',       'icon' => '📈', 'desc' => 'Month-by-month chart data'],
    ];

    #[On('openExcelExporter')]
    public function openModal(
        string $context   = 'reports',
        string $dateFrom  = '',
        string $dateTo    = '',
        string $search    = '',
        string $projectId = '',
        string $gateway   = '',
        string $status    = '',
        string $period    = '',
    ): void {
        $this->context   = $context;
        $this->dateFrom  = $dateFrom;
        $this->dateTo    = $dateTo;
        $this->search    = $search;
        $this->projectId = $projectId;
        $this->gateway   = $gateway;
        $this->status    = $status;
        $this->period    = $period;
        $this->sheets    = $this->contextDefaults[$context] ?? ['dashboard', 'donations'];
        $this->showModal = true;
    }

    public function close(): void
    {
        $this->showModal = false;
    }

    public function toggleSheet(string $sheet): void
    {
        if (in_array($sheet, $this->sheets)) {
            $this->sheets = array_values(array_filter($this->sheets, fn($s) => $s !== $sheet));
        } else {
            $this->sheets[] = $sheet;
        }
    }

    public function generate(): void
    {
        if (empty($this->sheets)) {
            return;
        }

        $params = array_filter([
            'context'   => $this->context,
            'sheets'    => implode(',', $this->sheets),
            'date_from' => $this->dateFrom,
            'date_to'   => $this->dateTo,
            'search'    => $this->search,
            'project'   => $this->projectId,
            'gateway'   => $this->gateway,
            'status'    => $this->status,
            'period'    => $this->period,
        ]);

        $url = route('admin.excel.export', $params);
        $this->js("window.open('{$url}', '_blank')");
        $this->showModal = false;
    }

    public function render()
    {
        return view('livewire.admin.excel-exporter', [
            'allSheets' => $this->allSheets,
        ]);
    }
}
