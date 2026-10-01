@section('title', 'Analytics')
<div class="space-y-6">

    {{-- ── PAGE HEADER ─────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white tracking-tight">Analytics & Insights</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Comprehensive giving data across all channels</p>
        </div>
        <div class="flex items-center gap-3 flex-wrap">
            {{-- Period selector --}}
            <div class="inline-flex rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 overflow-hidden shadow-sm">
                @foreach(['7' => '7D', '30' => '30D', '90' => '90D', '365' => '1Y'] as $val => $label)
                    <button wire:click="$set('period','{{ $val }}')"
                        class="px-3 py-2 text-xs font-semibold transition-colors
                            {{ $period === $val
                                ? 'bg-emerald-600 text-white'
                                : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
            <button wire:click="loadAll" class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-50 shadow-sm transition">
                <i class="fas fa-sync-alt text-emerald-500" wire:loading.class="animate-spin"></i> Refresh
            </button>
            <button wire:click="openExcelExporter" class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-slate-800 border-2 border-emerald-500 rounded-xl text-sm font-semibold text-emerald-700 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 shadow-sm hover:shadow-md hover:shadow-emerald-100 dark:hover:shadow-emerald-900/30 hover:scale-[1.02] active:scale-[0.98] transition-all">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Excel Report
            </button>
            <button onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 rounded-xl text-sm font-medium text-white shadow-sm transition">
                <i class="fas fa-print"></i> Print Report
            </button>
        </div>
    </div>

    <style>
        @media print {
            /* Hide everything except the analytics content */
            body * { visibility: hidden; }
            .space-y-6, .space-y-6 * { visibility: visible; }
            .space-y-6 { position: absolute; top: 0; left: 0; width: 100%; }

            /* Hide the period selector, refresh and print buttons when printing */
            .space-y-6 button { display: none !important; }

            /* Remove shadows and borders for cleaner print */
            .shadow-sm, .shadow-md { box-shadow: none !important; }

            /* Ensure charts print correctly */
            canvas { max-width: 100% !important; }

            /* Page settings */
            @page { margin: 1cm; size: A4 landscape; }
        }
    </style>

    {{-- ── KPI CARDS ────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @php
        $kpiIcons = [
            'raised'    => ['icon'=>'fa-wallet',        'bg'=>'bg-emerald-50 dark:bg-emerald-900/30', 'text'=>'text-emerald-600 dark:text-emerald-400'],
            'completed' => ['icon'=>'fa-circle-check',  'bg'=>'bg-blue-50 dark:bg-blue-900/30',     'text'=>'text-blue-600 dark:text-blue-400'],
            'donors'    => ['icon'=>'fa-users',         'bg'=>'bg-violet-50 dark:bg-violet-900/30', 'text'=>'text-violet-600 dark:text-violet-400'],
            'avg'       => ['icon'=>'fa-chart-line',    'bg'=>'bg-amber-50 dark:bg-amber-900/30',   'text'=>'text-amber-600 dark:text-amber-400'],
            'rate'      => ['icon'=>'fa-percent',       'bg'=>'bg-teal-50 dark:bg-teal-900/30',     'text'=>'text-teal-600 dark:text-teal-400'],
            'fees'      => ['icon'=>'fa-receipt',       'bg'=>'bg-rose-50 dark:bg-rose-900/30',     'text'=>'text-rose-600 dark:text-rose-400'],
            'endowment' => ['icon'=>'fa-heart',         'bg'=>'bg-pink-50 dark:bg-pink-900/30',     'text'=>'text-pink-600 dark:text-pink-400'],
            'project'   => ['icon'=>'fa-folder-open',  'bg'=>'bg-indigo-50 dark:bg-indigo-900/30', 'text'=>'text-indigo-600 dark:text-indigo-400'],
        ];
        @endphp

        @foreach($kpi as $key => $card)
        @php $ico = $kpiIcons[$key] ?? ['icon'=>'fa-circle','bg'=>'bg-slate-50','text'=>'text-slate-600']; @endphp
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-100 dark:border-slate-700 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-start justify-between mb-3">
                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide leading-tight">{{ $card['label'] }}</p>
                <div class="p-2 rounded-xl {{ $ico['bg'] }}">
                    <i class="fas {{ $ico['icon'] }} text-sm {{ $ico['text'] }}"></i>
                </div>
            </div>
            <p class="text-2xl font-bold text-slate-800 dark:text-white">
                @if($card['fmt'] === 'currency') ₦{{ number_format($card['value'], 0) }}
                @elseif($card['fmt'] === 'percent') {{ $card['value'] }}%
                @else {{ number_format($card['value']) }}
                @endif
            </p>
            @if(isset($card['count']))
                <p class="text-xs text-slate-400 mt-0.5">{{ $card['count'] }} donations</p>
            @endif
            <div class="mt-2 flex items-center gap-1 text-xs">
                @if($card['trend'] > 0)
                    <span class="text-emerald-600 font-semibold flex items-center gap-0.5"><i class="fas fa-arrow-up text-[10px]"></i>{{ $card['trend'] }}%</span>
                    <span class="text-slate-400">vs prev period</span>
                @elseif($card['trend'] < 0)
                    <span class="text-rose-500 font-semibold flex items-center gap-0.5"><i class="fas fa-arrow-down text-[10px]"></i>{{ abs($card['trend']) }}%</span>
                    <span class="text-slate-400">vs prev period</span>
                @else
                    <span class="text-slate-400">— no comparison data</span>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    {{-- ── REVENUE LINE CHART ───────────────────────────────────── --}}
    <div wire:ignore class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm p-6">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h2 class="text-base font-bold text-slate-800 dark:text-white">Revenue Over Time</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Daily collection — last {{ $period }} days</p>
            </div>
            <div class="flex items-center gap-4 text-xs text-slate-500">
                <span class="flex items-center gap-1.5"><span class="w-3 h-0.5 bg-emerald-500 inline-block rounded"></span>Paystack</span>
                <span class="flex items-center gap-1.5"><span class="w-3 h-0.5 bg-blue-500 inline-block rounded"></span>Squad</span>
            </div>
        </div>
        <div style="height:260px;position:relative;">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>

    {{-- ── STATUS + GATEWAY ROW ────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-4">

        {{-- Donation Status --}}
        <div wire:ignore class="lg:col-span-3 bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm p-6">
            <h2 class="text-base font-bold text-slate-800 dark:text-white mb-1">Donation Status Breakdown</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-5">Count and value by status</p>
            <div class="grid grid-cols-3 gap-3 mb-5">
                @php
                $statusCfg = [
                    'completed' => ['label'=>'Completed','bg'=>'bg-emerald-50 dark:bg-emerald-900/20','text'=>'text-emerald-700 dark:text-emerald-400','dot'=>'bg-emerald-500'],
                    'pending'   => ['label'=>'Pending',  'bg'=>'bg-amber-50 dark:bg-amber-900/20',   'text'=>'text-amber-700 dark:text-amber-400',    'dot'=>'bg-amber-400'],
                    'failed'    => ['label'=>'Failed',   'bg'=>'bg-rose-50 dark:bg-rose-900/20',     'text'=>'text-rose-700 dark:text-rose-400',      'dot'=>'bg-rose-500'],
                ];
                $totalStatus = collect($statusChart)->sum('count') ?: 1;
                @endphp
                @foreach($statusCfg as $s => $cfg)
                <div class="rounded-xl p-3 {{ $cfg['bg'] }}">
                    <div class="flex items-center gap-1.5 mb-1">
                        <span class="w-2 h-2 rounded-full {{ $cfg['dot'] }}"></span>
                        <span class="text-xs font-medium {{ $cfg['text'] }}">{{ $cfg['label'] }}</span>
                    </div>
                    <p class="text-xl font-bold {{ $cfg['text'] }}">{{ $statusChart[$s]['count'] ?? 0 }}</p>
                    <p class="text-xs {{ $cfg['text'] }} opacity-75">₦{{ number_format($statusChart[$s]['total'] ?? 0, 0) }}</p>
                    <p class="text-xs {{ $cfg['text'] }} opacity-60 mt-0.5">{{ $totalStatus > 0 ? round(($statusChart[$s]['count'] ?? 0) / $totalStatus * 100, 1) : 0 }}%</p>
                </div>
                @endforeach
            </div>
            <div style="height:180px;position:relative;">
                <canvas id="statusChart"></canvas>
            </div>
        </div>

        {{-- Gateway Split --}}
        <div wire:ignore class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm p-6 flex flex-col">
            <h2 class="text-base font-bold text-slate-800 dark:text-white mb-1">Gateway Split</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">Paystack vs Squad</p>
            <div style="height:180px;position:relative;" class="mb-4">
                <canvas id="gatewayChart"></canvas>
            </div>
            @php $gwTotal = ($gatewayChart['paystack']['amount'] ?? 0) + ($gatewayChart['squad']['amount'] ?? 0); @endphp
            <div class="space-y-2 mt-auto">
                <div class="flex items-center justify-between p-2.5 rounded-lg bg-emerald-50 dark:bg-emerald-900/20">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        <span class="text-xs font-semibold text-emerald-700 dark:text-emerald-400">Paystack</span>
                    </div>
                    <div class="text-right">
                        <p class="text-xs font-bold text-emerald-700 dark:text-emerald-400">₦{{ number_format($gatewayChart['paystack']['amount'] ?? 0, 0) }}</p>
                        <p class="text-[10px] text-emerald-600 opacity-70">{{ $gatewayChart['paystack']['count'] ?? 0 }} txns · {{ $gwTotal > 0 ? round(($gatewayChart['paystack']['amount'] ?? 0) / $gwTotal * 100, 1) : 0 }}%</p>
                    </div>
                </div>
                <div class="flex items-center justify-between p-2.5 rounded-lg bg-blue-50 dark:bg-blue-900/20">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                        <span class="text-xs font-semibold text-blue-700 dark:text-blue-400">Squad</span>
                    </div>
                    <div class="text-right">
                        <p class="text-xs font-bold text-blue-700 dark:text-blue-400">₦{{ number_format($gatewayChart['squad']['amount'] ?? 0, 0) }}</p>
                        <p class="text-[10px] text-blue-600 opacity-70">{{ $gatewayChart['squad']['count'] ?? 0 }} txns · {{ $gwTotal > 0 ? round(($gatewayChart['squad']['amount'] ?? 0) / $gwTotal * 100, 1) : 0 }}%</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── DONOR TIERS ──────────────────────────────────────────── --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm p-6">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h2 class="text-base font-bold text-slate-800 dark:text-white">Donor Tier Distribution</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Giving ladder from General Supporter to Platinum</p>
            </div>
        </div>
        <div class="space-y-3">
            @php
            $tierColors = ['General Supporter'=>'#64748b','Bronze Benefactor'=>'#cd7f32','Silver Benefactor'=>'#9ca3af','Gold Benefactor'=>'#f59e0b','Platinum Benefactor'=>'#8b5cf6'];
            $maxTierTotal = collect($tierChart)->max('total') ?: 1;
            @endphp
            @forelse($tierChart as $tier)
            <div class="flex items-center gap-4">
                <div class="w-36 shrink-0">
                    <p class="text-xs font-semibold text-slate-700 dark:text-slate-200 truncate">{{ $tier['name'] }}</p>
                    <p class="text-[10px] text-slate-400">{{ $tier['count'] }} donor{{ $tier['count'] != 1 ? 's' : '' }}</p>
                </div>
                <div class="flex-1">
                    <div class="h-5 rounded-full bg-slate-100 dark:bg-slate-700 overflow-hidden">
                        @php $barPct = $maxTierTotal > 0 ? max(($tier['total'] / $maxTierTotal) * 100, $tier['count'] > 0 ? 3 : 0) : 0; @endphp
                        <div class="h-full rounded-full transition-all duration-700"
                            style="width:{{ $barPct }}%;background-color:{{ $tierColors[$tier['name']] ?? '#10b981' }};">
                        </div>
                    </div>
                </div>
                <div class="w-28 text-right shrink-0">
                    <p class="text-xs font-bold text-slate-700 dark:text-slate-200">₦{{ number_format($tier['total'], 0) }}</p>
                    <p class="text-[10px] text-slate-400">
                        ₦{{ number_format($tier['min'], 0) }}{{ $tier['max'] > 0 ? '–₦'.number_format($tier['max'],0) : '+' }}
                    </p>
                </div>
            </div>
            @empty
            <p class="text-sm text-slate-400 text-center py-4">No tier data available.</p>
            @endforelse
        </div>
    </div>

    {{-- ── DONATION TYPE + PROJECTS ─────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

        {{-- Donation type by month --}}
        <div wire:ignore class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm p-6">
            <h2 class="text-base font-bold text-slate-800 dark:text-white mb-1">General vs Project Donations</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">Monthly donation type breakdown</p>
            @if(count($typeChart['labels'] ?? []) > 0)
            <div style="height:220px;position:relative;">
                <canvas id="typeChart"></canvas>
            </div>
            @else
            <div class="h-44 flex items-center justify-center text-slate-400 text-sm">No data for this period.</div>
            @endif
        </div>

        {{-- Projects progress --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm p-6">
            <h2 class="text-base font-bold text-slate-800 dark:text-white mb-1">Project Funding Progress</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">Raised vs target for active projects</p>
            <div class="space-y-3 overflow-y-auto" style="max-height:240px;">
                @forelse($projectsData as $proj)
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <p class="text-xs font-semibold text-slate-700 dark:text-slate-200 truncate max-w-[60%]">{{ $proj['title'] }}</p>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] px-1.5 py-0.5 rounded-full font-medium
                                {{ $proj['status'] === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                {{ ucfirst($proj['status']) }}
                            </span>
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $proj['pct'] }}%</span>
                        </div>
                    </div>
                    <div class="h-2 rounded-full bg-slate-100 dark:bg-slate-700 overflow-hidden">
                        <div class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-emerald-400 transition-all duration-700"
                            style="width:{{ $proj['pct'] }}%"></div>
                    </div>
                    <div class="flex justify-between mt-0.5">
                        <span class="text-[10px] text-slate-400">₦{{ number_format($proj['raised'], 0) }} raised</span>
                        <span class="text-[10px] text-slate-400">₦{{ number_format($proj['target'], 0) }} target</span>
                    </div>
                </div>
                @empty
                <p class="text-sm text-slate-400 text-center py-8">No project data available.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ── DEMOGRAPHICS ─────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

        {{-- Donor type --}}
        <div wire:ignore class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm p-6">
            <h2 class="text-sm font-bold text-slate-800 dark:text-white mb-1">Donor Type</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">Alumni vs Individual</p>
            <div style="height:160px;position:relative;">
                <canvas id="donorTypeChart"></canvas>
            </div>
            <div class="mt-3 space-y-1">
                @foreach($demoData['types'] ?? [] as $i => $t)
                @php $typeColors = ['#10b981','#3b82f6','#f59e0b','#8b5cf6','#ec4899']; @endphp
                <div class="flex items-center justify-between text-xs">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full" style="background:{{ $typeColors[$i % count($typeColors)] }}"></span>
                        <span class="text-slate-600 dark:text-slate-300">{{ $t['label'] }}</span>
                    </div>
                    <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $t['count'] }}</span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Gender --}}
        <div wire:ignore class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm p-6">
            <h2 class="text-sm font-bold text-slate-800 dark:text-white mb-1">Gender Split</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">Donor gender distribution</p>
            @if(count($demoData['gender'] ?? []) > 0)
            <div style="height:160px;position:relative;">
                <canvas id="genderChart"></canvas>
            </div>
            @else
            <div class="h-40 flex items-center justify-center text-slate-400 text-xs">No gender data recorded.</div>
            @endif
        </div>

        {{-- Top states --}}
        <div wire:ignore class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm p-6">
            <h2 class="text-sm font-bold text-slate-800 dark:text-white mb-1">Top States</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">By total amount donated</p>
            @if(count($demoData['states'] ?? []) > 0)
            <div style="height:200px;position:relative;">
                <canvas id="statesChart"></canvas>
            </div>
            @else
            <div class="h-40 flex items-center justify-center text-slate-400 text-xs">No state data recorded.</div>
            @endif
        </div>
    </div>

    {{-- ── PEAK HOURS + RECENT ACTIVITY ────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-4">

        {{-- Transactions by hour --}}
        <div wire:ignore class="lg:col-span-3 bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm p-6">
            <h2 class="text-base font-bold text-slate-800 dark:text-white mb-1">Peak Payment Hours</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">When donors are most active (24h)</p>
            <div style="height:200px;position:relative;">
                <canvas id="hourChart"></canvas>
            </div>
        </div>

        {{-- Recent activity --}}
        <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm p-6">
            <h2 class="text-base font-bold text-slate-800 dark:text-white mb-1">Recent Activity</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">Latest completed payments</p>
            <div class="space-y-3 overflow-y-auto" style="max-height:220px;">
                @forelse($recentActivity as $act)
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400 flex items-center justify-center text-xs font-bold shrink-0">
                        {{ $act['initials'] }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold text-slate-700 dark:text-slate-200 truncate">{{ $act['name'] }}</p>
                        <p class="text-[10px] text-slate-400">{{ $act['ago'] }}</p>
                    </div>
                    <div class="text-right shrink-0">
                        <p class="text-xs font-bold text-slate-700 dark:text-slate-200">₦{{ number_format($act['amount'], 0) }}</p>
                        <span class="text-[10px] px-1.5 py-0.5 rounded-full font-medium
                            {{ $act['gateway'] === 'paystack' ? 'bg-emerald-100 text-emerald-700' : 'bg-blue-100 text-blue-700' }}">
                            {{ ucfirst($act['gateway']) }}
                        </span>
                    </div>
                </div>
                @empty
                <p class="text-xs text-slate-400 text-center py-6">No recent activity.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ── TOP DONORS TABLE ─────────────────────────────────────── --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-700">
            <h2 class="text-base font-bold text-slate-800 dark:text-white">Top Donors</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Highest contributors for the selected period</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 dark:divide-slate-700">
                <thead class="bg-slate-50 dark:bg-slate-900/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">#</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Donor</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Gifts</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Donated</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($topDonors as $i => $donor)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition">
                        <td class="px-6 py-3 text-sm font-bold text-slate-400">
                            @if($i === 0) 🥇
                            @elseif($i === 1) 🥈
                            @elseif($i === 2) 🥉
                            @else <span class="text-slate-400">{{ $i + 1 }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-full bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400 flex items-center justify-center text-xs font-bold">
                                    {{ strtoupper(substr($donor['name'], 0, 1)) }}
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">{{ $donor['name'] ?: 'Anonymous' }}</p>
                                    <p class="text-xs text-slate-400">{{ $donor['email'] }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-3">
                            <span class="text-xs px-2 py-0.5 rounded-full font-medium bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300">
                                {{ $donor['type'] ?? 'N/A' }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-center text-sm font-semibold text-slate-600 dark:text-slate-300">{{ $donor['gifts'] }}</td>
                        <td class="px-6 py-3 text-right">
                            <span class="text-sm font-bold text-emerald-600 dark:text-emerald-400">₦{{ number_format($donor['total'], 0) }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-sm text-slate-400">No donor data for this period.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── CHARTS SCRIPT ────────────────────────────────────────── --}}
    <script>
    (function () {
        /* ── helpers ───────────────────────────────────────────── */
        function naira(v) {
            if (v >= 1e6) return '₦' + (v / 1e6).toFixed(1) + 'M';
            if (v >= 1e3) return '₦' + (v / 1e3).toFixed(0) + 'k';
            return '₦' + v;
        }
        function theme() {
            var dark = document.documentElement.classList.contains('dark');
            return { grid: dark ? 'rgba(148,163,184,0.12)' : 'rgba(148,163,184,0.18)', txt: dark ? '#94a3b8' : '#64748b' };
        }
        function baseOpts(yFmt) {
            var t = theme();
            return {
                responsive: true, maintainAspectRatio: false, interaction: { mode: 'index', intersect: false },
                plugins: { legend: { display: false }, tooltip: { callbacks: { label: function (c) {
                    var v = c.parsed.y !== undefined ? c.parsed.y : c.parsed;
                    return ' ' + c.dataset.label + ': ' + (yFmt === 'currency' ? '₦' + Number(v).toLocaleString('en-NG') : v);
                }}}},
                scales: {
                    x: { grid: { display: false }, ticks: { color: t.txt, font: { size: 10 }, maxRotation: 0, maxTicksLimit: 10 } },
                    y: { beginAtZero: true, grid: { color: t.grid }, ticks: { color: t.txt, font: { size: 10 },
                        callback: yFmt === 'currency' ? function (v) { return naira(v); } : undefined }}
                }
            };
        }
        function ic(id, cfg) {
            var el = document.getElementById(id);
            if (!el || typeof Chart === 'undefined') return;
            if (el._ch) { el._ch.destroy(); el._ch = null; }
            el._ch = new Chart(el, cfg);
        }

        /* ── main build ─────────────────────────────────────────── */
        function buildCharts(revenue, statusD, gatewayD, typeD, demoD, hourD) {
            var t = theme();

            ic('revenueChart', { type: 'line', data: { labels: revenue.labels, datasets: [
                { label: 'Paystack', data: revenue.paystack, borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,0.08)', borderWidth: 2, tension: 0.4, pointRadius: 2, fill: true },
                { label: 'Squad',    data: revenue.squad,    borderColor: '#3b82f6', backgroundColor: 'rgba(59,130,246,0.08)', borderWidth: 2, tension: 0.4, pointRadius: 2, fill: true },
            ]}, options: baseOpts('currency') });

            ic('statusChart', { type: 'doughnut', data: {
                labels: ['Completed', 'Pending', 'Failed'],
                datasets: [{ data: [statusD.completed.count, statusD.pending.count, statusD.failed.count], backgroundColor: ['#10b981','#f59e0b','#ef4444'], borderWidth: 0, hoverOffset: 6 }]
            }, options: { responsive: true, maintainAspectRatio: false, cutout: '70%', plugins: { legend: { display: false } } } });

            ic('gatewayChart', { type: 'doughnut', data: {
                labels: ['Paystack', 'Squad'],
                datasets: [{ data: [gatewayD.paystack.amount, gatewayD.squad.amount], backgroundColor: ['#10b981','#3b82f6'], borderWidth: 0, hoverOffset: 6 }]
            }, options: { responsive: true, maintainAspectRatio: false, cutout: '68%', plugins: { legend: { display: false },
                tooltip: { callbacks: { label: function (c) { return ' ' + c.label + ': ₦' + Number(c.parsed).toLocaleString('en-NG'); } } } } } });

            if (typeD.labels && typeD.labels.length) {
                var tOpts = baseOpts('currency');
                tOpts.plugins.legend = { display: true, labels: { color: t.txt, font: { size: 11 } } };
                ic('typeChart', { type: 'bar', data: { labels: typeD.labels, datasets: [
                    { label: 'General', data: typeD.endowment, backgroundColor: '#10b981', borderRadius: 4 },
                    { label: 'Project', data: typeD.project,   backgroundColor: '#6366f1', borderRadius: 4 },
                ]}, options: tOpts });
            }

            var demoColors = ['#10b981','#3b82f6','#f59e0b','#8b5cf6','#ec4899'];
            ic('donorTypeChart', { type: 'doughnut', data: {
                labels: demoD.types.map(function (x) { return x.label; }),
                datasets: [{ data: demoD.types.map(function (x) { return x.count; }), backgroundColor: demoColors, borderWidth: 0 }]
            }, options: { responsive: true, maintainAspectRatio: false, cutout: '60%', plugins: { legend: { display: false } } } });

            if (demoD.gender && demoD.gender.length) {
                ic('genderChart', { type: 'doughnut', data: {
                    labels: demoD.gender.map(function (x) { return x.label; }),
                    datasets: [{ data: demoD.gender.map(function (x) { return x.count; }), backgroundColor: ['#3b82f6','#ec4899','#94a3b8'], borderWidth: 0 }]
                }, options: { responsive: true, maintainAspectRatio: false, cutout: '60%',
                    plugins: { legend: { position: 'bottom', labels: { color: t.txt, font: { size: 10 }, boxWidth: 10, padding: 8 } } } } });
            }

            if (demoD.states && demoD.states.length) {
                ic('statesChart', { type: 'bar', data: {
                    labels: demoD.states.map(function (x) { return x.label; }),
                    datasets: [{ label: 'Total (₦)', data: demoD.states.map(function (x) { return x.total; }), backgroundColor: '#6366f1', borderRadius: 4 }]
                }, options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false }, tooltip: { callbacks: { label: function (c) { return ' ₦' + Number(c.parsed.x).toLocaleString('en-NG'); } } } },
                    scales: { x: { beginAtZero: true, grid: { color: t.grid }, ticks: { color: t.txt, font: { size: 10 }, callback: function (v) { return naira(v); } } },
                              y: { grid: { display: false }, ticks: { color: t.txt, font: { size: 10 } } } }
                } });
            }

            ic('hourChart', { type: 'bar', data: { labels: hourD.labels, datasets: [
                { label: 'Transactions', data: hourD.data, backgroundColor: 'rgba(16,185,129,0.7)', borderRadius: 3 }
            ]}, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } },
                scales: { x: { grid: { display: false }, ticks: { color: t.txt, font: { size: 9 }, maxRotation: 45, maxTicksLimit: 12 } },
                          y: { beginAtZero: true, grid: { color: t.grid }, ticks: { color: t.txt, font: { size: 10 }, stepSize: 1 } } }
            } });
        }

        /* ── listen for PHP dispatch event ───────────────────────── */
        document.addEventListener('stats-charts-ready', function (e) {
            var d = e.detail;
            buildCharts(d.revenue, d.statusD, d.gatewayD, d.typeD, d.demoD, d.hourD);
        });
    })();
    </script>
</div>
