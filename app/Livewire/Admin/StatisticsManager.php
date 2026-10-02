<?php

namespace App\Livewire\Admin;

use App\Services\AdminPaymentQuery;

use App\Models\Donation;
use App\Models\Donor;
use App\Models\DonorTier;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class StatisticsManager extends Component
{
    public string $period = '30';

    public array $kpi            = [];
    public array $revenueChart   = [];
    public array $statusChart    = [];
    public array $gatewayChart   = [];
    public array $tierChart      = [];
    public array $typeChart      = [];
    public array $projectsData   = [];
    public array $demoData       = [];
    public array $txnHourChart   = [];
    public array $recentActivity = [];
    public array $topDonors      = [];

    protected $queryString = ['period' => ['except' => '30']];

    public function mount(): void { $this->loadAll(); }

    public function updatedPeriod(): void { $this->loadAll(); }

    private function start(): Carbon  { return now()->subDays((int) $this->period)->startOfDay(); }
    private function pStart(): Carbon { return now()->subDays((int) $this->period * 2)->startOfDay(); }
    private function pEnd(): Carbon   { return $this->start()->subSecond(); }

    private function trend($cur, $prev): float
    {
        if ($prev == 0) return $cur > 0 ? 100.0 : 0.0;
        return round((($cur - $prev) / $prev) * 100, 1);
    }

    public function loadAll(): void
    {
        $start = $this->start();
        $ps    = $this->pStart();
        $pe    = $this->pEnd();

        // ── KPI cards ────────────────────────────────────────────
        $raised      = Donation::where('status', 'completed')->where('created_at', '>=', $start)->sum('amount');
        $prevRaised  = Donation::where('status', 'completed')->whereBetween('created_at', [$ps, $pe])->sum('amount');

        $completed   = Donation::where('status', 'completed')->where('created_at', '>=', $start)->count();
        $prevComp    = Donation::where('status', 'completed')->whereBetween('created_at', [$ps, $pe])->count();

        $allTx       = Donation::where('created_at', '>=', $start)->count();
        $prevAllTx   = Donation::whereBetween('created_at', [$ps, $pe])->count();
        $rate        = $allTx > 0 ? round($completed / $allTx * 100, 1) : 0;
        $prevRate    = $prevAllTx > 0 ? round($prevComp / $prevAllTx * 100, 1) : 0;

        $donors      = Donor::count();
        $prevDonors  = Donor::where('created_at', '<', $start)->count();

        $avg         = $completed > 0 ? round((float) $raised / $completed, 2) : 0;
        $prevAvg     = $prevComp  > 0 ? round((float) $prevRaised / $prevComp, 2) : 0;

        $fees        = AdminPaymentQuery::query()->where('status', 'completed')->where('created_at', '>=', $start)->sum('fee');

        $endoAmt     = Donation::where('status', 'completed')->where('endowment', 'yes')->where('created_at', '>=', $start)->sum('amount');
        $endoCnt     = Donation::where('status', 'completed')->where('endowment', 'yes')->where('created_at', '>=', $start)->count();
        $projAmt     = Donation::where('status', 'completed')->where('type', 'project')->where('created_at', '>=', $start)->sum('amount');
        $projCnt     = Donation::where('status', 'completed')->where('type', 'project')->where('created_at', '>=', $start)->count();

        $this->kpi = [
            'raised'    => ['value' => (float) $raised,   'trend' => $this->trend($raised, $prevRaised), 'fmt' => 'currency', 'label' => 'Total Raised',         'icon' => 'wallet',       'color' => 'emerald'],
            'completed' => ['value' => (int)   $completed,'trend' => $this->trend($completed, $prevComp), 'fmt' => 'number',  'label' => 'Completed Donations',   'icon' => 'check-circle', 'color' => 'blue'],
            'donors'    => ['value' => (int)   $donors,   'trend' => $this->trend($donors, $prevDonors),  'fmt' => 'number',  'label' => 'Total Donors',          'icon' => 'users',        'color' => 'violet'],
            'avg'       => ['value' => (float) $avg,      'trend' => $this->trend($avg, $prevAvg),        'fmt' => 'currency','label' => 'Avg. Donation',         'icon' => 'trending-up',  'color' => 'amber'],
            'rate'      => ['value' => (float) $rate,     'trend' => $this->trend($rate, $prevRate),      'fmt' => 'percent', 'label' => 'Success Rate',          'icon' => 'pie-chart',    'color' => 'teal'],
            'endowment' => ['value' => (float) $endoAmt,  'trend' => 0, 'count' => $endoCnt,             'fmt' => 'currency','label' => 'General Donations',     'icon' => 'heart',        'color' => 'pink'],
            'project'   => ['value' => (float) $projAmt,  'trend' => 0, 'count' => $projCnt,             'fmt' => 'currency','label' => 'Project Donations',     'icon' => 'folder',       'color' => 'indigo'],
        ];

        // ── Revenue by gateway per day ────────────────────────────
        $days = (int) $this->period;
        $isSqlite = DB::connection()->getDriverName() === 'sqlite';
        $daySql = $isSqlite ? "strftime('%Y-%m-%d', created_at)" : "DATE_FORMAT(created_at, '%Y-%m-%d')";
        $rows = AdminPaymentQuery::query()->select(
                DB::raw("{$daySql} as day"),
                'payment_gateway',
                DB::raw('SUM(amount) as total')
            )
            ->where('created_at', '>=', $start)
            ->where('status', 'completed')
            ->groupBy('day', 'payment_gateway')
            ->get()
            ->groupBy('payment_gateway');

        $psMap = collect($rows->get('paystack', collect()))->keyBy('day')->map(fn($r) => (float) $r->total);
        $sqMap = collect($rows->get('squad',    collect()))->keyBy('day')->map(fn($r) => (float) $r->total);
        $iswMap = collect($rows->get('interswitch', collect()))->keyBy('day')->map(fn($r) => (float) $r->total);

        $rLabels = $rPs = $rSq = $rIsw = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $key      = now()->subDays($i)->format('Y-m-d');
            $rLabels[]= now()->subDays($i)->format($days > 60 ? 'M d' : 'M d');
            $rPs[]    = $psMap[$key] ?? 0;
            $rSq[]    = $sqMap[$key] ?? 0;
            $rIsw[]   = $iswMap[$key] ?? 0;
        }
        $this->revenueChart = ['labels' => $rLabels, 'paystack' => $rPs, 'squad' => $rSq, 'interswitch' => $rIsw];

        // ── Status breakdown ──────────────────────────────────────
        $sc = Donation::where('created_at', '>=', $start)
            ->selectRaw('status, count(*) as cnt, COALESCE(sum(amount),0) as total')
            ->groupBy('status')->get()->keyBy('status');

        $this->statusChart = [
            'completed' => ['count' => (int)(($sc['completed'] ?? null)?->cnt ?? 0),  'total' => (float)(($sc['completed'] ?? null)?->total ?? 0)],
            'pending'   => ['count' => (int)(($sc['pending']   ?? null)?->cnt ?? 0),  'total' => (float)(($sc['pending']   ?? null)?->total ?? 0)],
            'failed'    => ['count' => (int)(($sc['failed']    ?? null)?->cnt ?? 0),  'total' => (float)(($sc['failed']    ?? null)?->total ?? 0)],
        ];

        // ── Gateway split ─────────────────────────────────────────
        $gw = AdminPaymentQuery::query()->where('status', 'completed')->where('created_at', '>=', $start)
            ->selectRaw('payment_gateway, count(*) as cnt, COALESCE(sum(amount),0) as total, COALESCE(sum(fee),0) as fees')
            ->groupBy('payment_gateway')->get()->keyBy('payment_gateway');

        $this->gatewayChart = [
            'paystack' => ['amount' => (float)(($gw['paystack'] ?? null)?->total ?? 0), 'count' => (int)(($gw['paystack'] ?? null)?->cnt ?? 0), 'fee' => (float)(($gw['paystack'] ?? null)?->fees ?? 0)],
            'squad'    => ['amount' => (float)(($gw['squad']    ?? null)?->total ?? 0), 'count' => (int)(($gw['squad']    ?? null)?->cnt ?? 0), 'fee' => (float)(($gw['squad']    ?? null)?->fees ?? 0)],
            'interswitch' => ['amount' => (float)(($gw['interswitch'] ?? null)?->total ?? 0), 'count' => (int)(($gw['interswitch'] ?? null)?->cnt ?? 0), 'fee' => (float)(($gw['interswitch'] ?? null)?->fees ?? 0)],
        ];

        // ── Donor tiers ───────────────────────────────────────────
        $this->tierChart = DonorTier::orderBy('sort_order')->get()->map(function ($tier) {
            $cnt   = Donor::where('donor_tier_id', $tier->id)->count();
            $total = Donation::where('status', 'completed')
                ->whereHas('donor', fn($q) => $q->where('donor_tier_id', $tier->id))
                ->sum('amount');
            return [
                'name'  => $tier->name,
                'color' => $tier->color ?? '#10b981',
                'count' => $cnt,
                'min'   => (float) $tier->min_amount,
                'max'   => (float) ($tier->max_amount ?? 0),
                'total' => (float) $total,
            ];
        })->toArray();

        // ── Donation type by month ────────────────────────────────
        $monthSql = $isSqlite ? "strftime('%Y-%m', created_at)" : "DATE_FORMAT(created_at, '%Y-%m')";
        $typeRows = Donation::where('status', 'completed')->where('created_at', '>=', $start)
            ->selectRaw("{$monthSql} as month, type, COALESCE(sum(amount),0) as total")
            ->groupBy('month', 'type')->orderBy('month')->get()->groupBy('month');

        $tLabels = $tEndo = $tProj = [];
        foreach ($typeRows as $month => $rr) {
            $tLabels[] = Carbon::parse($month . '-01')->format('M Y');
            $tEndo[]   = (float)(($rr->firstWhere('type', 'endowment'))->total ?? 0);
            $tProj[]   = (float)(($rr->firstWhere('type', 'project'))->total ?? 0);
        }
        $this->typeChart = ['labels' => $tLabels, 'endowment' => $tEndo, 'project' => $tProj];

        // ── Projects progress ─────────────────────────────────────
        $this->projectsData = Project::withoutTrashed()->where('target', '>', 0)
            ->orderByDesc('raised')->take(8)->get()
            ->map(fn($p) => [
                'title'  => $p->project_title,
                'target' => (float) $p->target,
                'raised' => (float) ($p->raised ?? 0),
                'status' => $p->status ?? 'active',
                'pct'    => $p->target > 0 ? min(round($p->raised / $p->target * 100, 1), 100) : 0,
            ])->toArray();

        // ── Demographics ──────────────────────────────────────────
        $dTypes   = Donor::selectRaw('donor_type, count(*) as cnt')->groupBy('donor_type')->get();
        $dGenders = Donor::whereNotNull('gender')->selectRaw('gender, count(*) as cnt')->groupBy('gender')->get();
        $dStates  = Donor::whereNotNull('state')
            ->join('donations', 'donors.id', '=', 'donations.donor_id')
            ->where('donations.status', 'completed')
            ->selectRaw('donors.state, COALESCE(sum(donations.amount),0) as total')
            ->groupBy('donors.state')->orderByDesc('total')->take(6)->get();

        $this->demoData = [
            'types'  => $dTypes->map(fn($r)   => ['label' => $r->donor_type ?: 'Unknown', 'count' => (int)$r->cnt])->toArray(),
            'gender' => $dGenders->map(fn($r)  => ['label' => ucfirst($r->gender),          'count' => (int)$r->cnt])->toArray(),
            'states' => $dStates->map(fn($r)   => ['label' => $r->state,                    'total' => (float)$r->total])->toArray(),
        ];

        // ── Transactions by hour ──────────────────────────────────
        $hourSql = $isSqlite ? "CAST(strftime('%H', created_at) AS INTEGER)" : "HOUR(created_at)";
        $hrRows = AdminPaymentQuery::query()->where('status', 'completed')->where('created_at', '>=', $start)
            ->selectRaw("{$hourSql} as hr, count(*) as cnt")
            ->groupBy('hr')->get()->keyBy('hr');

        $hLabels = $hData = [];
        for ($h = 0; $h < 24; $h++) {
            $hLabels[] = str_pad($h, 2, '0', STR_PAD_LEFT) . ':00';
            $hData[]   = (int)(($hrRows[$h] ?? null)?->cnt ?? 0);
        }
        $this->txnHourChart = ['labels' => $hLabels, 'data' => $hData];

        // ── Recent activity ───────────────────────────────────────
        $this->recentActivity = AdminPaymentQuery::query()->with('donor')
            ->where('status', 'completed')->latest()->take(8)->get()
            ->map(fn($t) => [
                'name'    => optional($t->donor)->full_name ?? 'Anonymous',
                'initials'=> $this->initials(optional($t->donor)->full_name ?? 'AN'),
                'amount'  => (float) $t->amount,
                'gateway' => $t->payment_gateway,
                'ago'     => $t->created_at->diffForHumans(),
                'status'  => $t->status,
            ])->toArray();

        // ── Top donors ────────────────────────────────────────────
        $this->topDonors = Donation::where('donations.status', 'completed')
            ->where('donations.created_at', '>=', $start)
            ->join('donors', 'donations.donor_id', '=', 'donors.id')
            ->selectRaw('donors.surname, donors.name, donors.email, donors.donor_type, count(*) as gifts, COALESCE(sum(donations.amount),0) as total')
            ->groupBy('donors.id', 'donors.surname', 'donors.name', 'donors.email', 'donors.donor_type')
            ->orderByDesc('total')->take(10)->get()
            ->map(fn($r) => [
                'name'  => trim(($r->surname ?? '') . ' ' . ($r->name ?? '')),
                'email' => $r->email,
                'type'  => $r->donor_type,
                'gifts' => (int) $r->gifts,
                'total' => (float) $r->total,
            ])->toArray();

        $this->dispatch('stats-charts-ready',
            revenue:  $this->revenueChart,
            statusD:  $this->statusChart,
            gatewayD: $this->gatewayChart,
            typeD:    $this->typeChart,
            demoD:    $this->demoData,
            hourD:    $this->txnHourChart,
        );
    }

    public function openExcelExporter(): void
    {
        $this->dispatch('openExcelExporter',
            context: 'statistics',
        );
    }

    private function initials(string $name): string
    {
        $parts = array_filter(explode(' ', trim($name)));
        return strtoupper(substr($parts[0] ?? 'A', 0, 1) . substr($parts[1] ?? '', 0, 1));
    }

    public function render()
    {
        return view('livewire.admin.statistics-manager')->layout('layouts.admin');
    }
}
