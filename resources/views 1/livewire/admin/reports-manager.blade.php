<div>
    {{-- ═══ HEADER + SEARCH ═══ --}}
    <div class="px-6 pt-6 pb-5 border-b border-gray-200 dark:border-gray-700">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-5">
            <div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white tracking-tight">Reports &amp; Analytics</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Real-time search and filter across all donation records</p>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
                <button wire:click="openExcelExporter"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-gray-800 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400 text-sm font-semibold rounded-lg border-2 border-emerald-500 shadow-sm hover:shadow-md hover:shadow-emerald-100 dark:hover:shadow-emerald-900/30 hover:scale-[1.02] active:scale-[0.98] transition-all duration-150">
                    <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Excel Report
                </button>
                <a href="{{ route('admin.reports.export', array_filter([
                        'search'    => $search,
                        'project'   => $selectedProject,
                        'date_from' => $dateFrom,
                        'date_to'   => $dateTo,
                    ])) }}"
                   target="_blank"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-150">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Export CSV
                </a>
                <button onclick="window.print()"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 shadow-sm transition-colors duration-150">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Print
                </button>
            </div>
        </div>

        {{-- Global Search Bar --}}
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <input wire:model.live.debounce.400ms="search"
                   type="search"
                   placeholder="Search by donor name, email, phone, organisation, project title, payment reference…"
                   class="w-full pl-12 pr-12 py-3.5 border border-gray-300 dark:border-gray-600 rounded-xl text-sm bg-white dark:bg-gray-800 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 shadow-sm transition-all duration-150">
            <div wire:loading.delay wire:target="search,selectedProject,dateFrom,dateTo,perPage,resetFilters"
                 class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                <svg class="animate-spin h-5 w-5 text-emerald-500" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
            </div>
        </div>
    </div>

    {{-- ═══ FILTERS ROW ═══ --}}
    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-700">
        <div class="flex flex-wrap items-end gap-3">
            {{-- Programme (placeholder — not yet in DB) --}}
            <div class="flex-1 min-w-[160px]">
                <label class="block text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1.5">Programme</label>
                <select disabled
                        class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 rounded-lg text-sm bg-gray-100 dark:bg-gray-900 text-gray-400 cursor-not-allowed">
                    <option>All Programmes</option>
                </select>
            </div>

            {{-- Project --}}
            <div class="flex-1 min-w-[190px]">
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Project</label>
                <select wire:model.live="selectedProject"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
                    <option value="">All Projects</option>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}">{{ $project->project_title }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Date From --}}
            <div class="flex-1 min-w-[155px]">
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Date From</label>
                <input wire:model.live="dateFrom" type="date"
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
            </div>

            {{-- Date To --}}
            <div class="flex-1 min-w-[155px]">
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Date To</label>
                <input wire:model.live="dateTo" type="date"
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
            </div>

            {{-- Reset button --}}
            <div class="flex-shrink-0">
                <button wire:click="resetFilters"
                        class="flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-red-600 dark:hover:text-red-400 border border-gray-300 dark:border-gray-600 rounded-lg hover:border-red-300 dark:hover:border-red-700 bg-white dark:bg-gray-800 transition-colors duration-150">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Reset
                </button>
            </div>
        </div>

        {{-- Active filter pills --}}
        @if($search || $selectedProject || $dateFrom || $dateTo)
        <div class="flex flex-wrap gap-2 mt-3 pt-3 border-t border-gray-200 dark:border-gray-700">
            @if($search)
            <span class="inline-flex items-center gap-1 pl-2.5 pr-1.5 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd"/></svg>
                "{{ \Illuminate\Support\Str::limit($search, 24) }}"
                <button wire:click="$set('search', '')" class="ml-0.5 w-4 h-4 rounded-full hover:bg-emerald-200 dark:hover:bg-emerald-700 flex items-center justify-center transition-colors">&times;</button>
            </span>
            @endif
            @if($selectedProject)
            <span class="inline-flex items-center gap-1 pl-2.5 pr-1.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                Project: {{ $projects->find($selectedProject)?->project_title ?? '—' }}
                <button wire:click="$set('selectedProject', '')" class="ml-0.5 w-4 h-4 rounded-full hover:bg-blue-200 dark:hover:bg-blue-700 flex items-center justify-center transition-colors">&times;</button>
            </span>
            @endif
            @if($dateFrom)
            <span class="inline-flex items-center gap-1 pl-2.5 pr-1.5 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300">
                From: {{ $dateFrom }}
                <button wire:click="$set('dateFrom', '')" class="ml-0.5 w-4 h-4 rounded-full hover:bg-purple-200 dark:hover:bg-purple-700 flex items-center justify-center transition-colors">&times;</button>
            </span>
            @endif
            @if($dateTo)
            <span class="inline-flex items-center gap-1 pl-2.5 pr-1.5 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300">
                To: {{ $dateTo }}
                <button wire:click="$set('dateTo', '')" class="ml-0.5 w-4 h-4 rounded-full hover:bg-purple-200 dark:hover:bg-purple-700 flex items-center justify-center transition-colors">&times;</button>
            </span>
            @endif
        </div>
        @endif
    </div>

    {{-- ═══ REAL-TIME TOTALS ═══ --}}
    <div class="px-6 py-5 grid grid-cols-1 xl:grid-cols-2 gap-4">

        {{-- Donations Totals --}}
        <div class="rounded-xl border border-emerald-200 dark:border-emerald-800/60 bg-gradient-to-br from-emerald-50 to-teal-50 dark:from-emerald-900/20 dark:to-teal-900/10 p-5"
             wire:loading.class="opacity-60" wire:target="search,selectedProject,dateFrom,dateTo,resetFilters">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <div class="w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-sm shadow-emerald-300"></div>
                    <span class="text-xs font-bold text-emerald-900 dark:text-emerald-100 uppercase tracking-widest">Donations</span>
                </div>
                <span class="text-xs font-semibold bg-white/70 dark:bg-emerald-800/40 text-emerald-700 dark:text-emerald-300 px-2.5 py-1 rounded-full border border-emerald-200 dark:border-emerald-700">
                    {{ number_format($filteredTotals['donations']['count']) }} records
                </span>
            </div>
            <div class="grid grid-cols-3 divide-x divide-emerald-200 dark:divide-emerald-800/60">
                <div class="text-center pr-4">
                    <div class="flex items-center justify-center gap-1.5 mb-1.5">
                        <div class="w-1.5 h-1.5 rounded-full bg-emerald-500"></div>
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Completed</span>
                    </div>
                    <div class="text-base font-bold text-emerald-700 dark:text-emerald-400 leading-tight">
                        ₦{{ number_format($filteredTotals['donations']['completed'], 0) }}
                    </div>
                </div>
                <div class="text-center px-4">
                    <div class="flex items-center justify-center gap-1.5 mb-1.5">
                        <div class="w-1.5 h-1.5 rounded-full bg-amber-400"></div>
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Pending</span>
                    </div>
                    <div class="text-base font-bold text-amber-600 dark:text-amber-400 leading-tight">
                        ₦{{ number_format($filteredTotals['donations']['pending'], 0) }}
                    </div>
                </div>
                <div class="text-center pl-4">
                    <div class="flex items-center justify-center gap-1.5 mb-1.5">
                        <div class="w-1.5 h-1.5 rounded-full bg-red-400"></div>
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Failed</span>
                    </div>
                    <div class="text-base font-bold text-red-500 dark:text-red-400 leading-tight">
                        ₦{{ number_format($filteredTotals['donations']['failed'], 0) }}
                    </div>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t border-emerald-200 dark:border-emerald-800/60 flex items-center justify-between">
                <span class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total</span>
                <span class="text-2xl font-extrabold text-emerald-800 dark:text-emerald-300">
                    ₦{{ number_format($filteredTotals['donations']['completed'] + $filteredTotals['donations']['pending'] + $filteredTotals['donations']['failed'], 0) }}
                </span>
            </div>
        </div>

        {{-- Transactions Totals --}}
        <div class="rounded-xl border border-blue-200 dark:border-blue-800/60 bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/10 p-5"
             wire:loading.class="opacity-60" wire:target="search,selectedProject,dateFrom,dateTo,resetFilters">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <div class="w-2.5 h-2.5 rounded-full bg-blue-500 shadow-sm shadow-blue-300"></div>
                    <span class="text-xs font-bold text-blue-900 dark:text-blue-100 uppercase tracking-widest">Transactions</span>
                </div>
                <span class="text-xs font-semibold bg-white/70 dark:bg-blue-800/40 text-blue-700 dark:text-blue-300 px-2.5 py-1 rounded-full border border-blue-200 dark:border-blue-700">
                    {{ number_format($filteredTotals['transactions']['count']) }} records
                </span>
            </div>
            <div class="grid grid-cols-3 divide-x divide-blue-200 dark:divide-blue-800/60">
                <div class="text-center pr-4">
                    <div class="flex items-center justify-center gap-1.5 mb-1.5">
                        <div class="w-1.5 h-1.5 rounded-full bg-blue-500"></div>
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Completed</span>
                    </div>
                    <div class="text-base font-bold text-blue-700 dark:text-blue-400 leading-tight">
                        ₦{{ number_format($filteredTotals['transactions']['completed'], 0) }}
                    </div>
                </div>
                <div class="text-center px-4">
                    <div class="flex items-center justify-center gap-1.5 mb-1.5">
                        <div class="w-1.5 h-1.5 rounded-full bg-amber-400"></div>
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Pending</span>
                    </div>
                    <div class="text-base font-bold text-amber-600 dark:text-amber-400 leading-tight">
                        ₦{{ number_format($filteredTotals['transactions']['pending'], 0) }}
                    </div>
                </div>
                <div class="text-center pl-4">
                    <div class="flex items-center justify-center gap-1.5 mb-1.5">
                        <div class="w-1.5 h-1.5 rounded-full bg-red-400"></div>
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Failed</span>
                    </div>
                    <div class="text-base font-bold text-red-500 dark:text-red-400 leading-tight">
                        ₦{{ number_format($filteredTotals['transactions']['failed'], 0) }}
                    </div>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t border-blue-200 dark:border-blue-800/60 flex items-center justify-between">
                <span class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total</span>
                <span class="text-2xl font-extrabold text-blue-800 dark:text-blue-300">
                    ₦{{ number_format($filteredTotals['transactions']['completed'] + $filteredTotals['transactions']['pending'] + $filteredTotals['transactions']['failed'], 0) }}
                </span>
            </div>
        </div>
    </div>

    {{-- Loading progress bar --}}
    <div wire:loading.delay wire:target="search,selectedProject,dateFrom,dateTo,perPage,resetFilters"
         class="px-6 pb-1">
        <div class="h-0.5 w-full bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
            <div class="h-full bg-emerald-500 rounded-full animate-pulse w-full"></div>
        </div>
    </div>

    {{-- ═══ TABLE CONTROLS ═══ --}}
    <div class="px-6 pb-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3"
         wire:loading.class="opacity-50" wire:target="search,selectedProject,dateFrom,dateTo,perPage,resetFilters">
        <div class="text-sm text-gray-500 dark:text-gray-400">
            @if($donations->total() > 0)
                Showing
                <span class="font-semibold text-gray-900 dark:text-white">{{ number_format($donations->firstItem()) }}</span>
                &ndash;
                <span class="font-semibold text-gray-900 dark:text-white">{{ number_format($donations->lastItem()) }}</span>
                of
                <span class="font-semibold text-gray-900 dark:text-white">{{ number_format($donations->total()) }}</span>
                donations
            @else
                No records found
            @endif
        </div>
        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
            <label class="text-xs font-medium">Per page:</label>
            <select wire:model.live="perPage"
                    class="px-2.5 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                <option value="15">15</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
        </div>
    </div>

    {{-- ═══ DATA TABLE ═══ --}}
    <div class="px-6 pb-6"
         wire:loading.class="opacity-60 pointer-events-none" wire:target="search,selectedProject,dateFrom,dateTo,perPage,resetFilters">
        <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800">
                        <th scope="col" class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                        <th scope="col" class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Donor</th>
                        <th scope="col" class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Project</th>
                        <th scope="col" class="px-5 py-3.5 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Amount</th>
                        <th scope="col" class="px-5 py-3.5 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($donations as $donation)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/60 transition-colors duration-75">

                        {{-- Date --}}
                        <td class="px-5 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $donation->created_at->format('M d, Y') }}</div>
                            <div class="text-xs text-gray-400 mt-0.5">{{ $donation->created_at->format('h:i A') }}</div>
                        </td>

                        {{-- Donor --}}
                        <td class="px-5 py-4 whitespace-nowrap">
                            @if($donation->donor)
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-900/40 flex items-center justify-center flex-shrink-0 text-xs font-bold text-emerald-700 dark:text-emerald-300">
                                    {{ strtoupper(substr($donation->donor->surname ?? 'A', 0, 1) . substr($donation->donor->name ?? '', 0, 1)) }}
                                </div>
                                <div>
                                    <div class="text-sm font-semibold text-gray-900 dark:text-white leading-tight">{{ $donation->donor->full_name }}</div>
                                    <div class="text-xs text-gray-400 mt-0.5">{{ $donation->donor->email }}</div>
                                </div>
                            </div>
                            @else
                            <span class="text-sm text-gray-400 italic">Unknown donor</span>
                            @endif
                        </td>

                        {{-- Project --}}
                        <td class="px-5 py-4 whitespace-nowrap">
                            @if($donation->project)
                                <div class="text-sm text-gray-900 dark:text-white">{{ $donation->project->project_title }}</div>
                            @else
                                <span class="text-xs text-gray-400 italic">General Donation</span>
                            @endif
                        </td>

                        {{-- Amount --}}
                        <td class="px-5 py-4 whitespace-nowrap text-right">
                            <div class="text-sm font-bold text-gray-900 dark:text-white">₦{{ number_format($donation->amount, 2) }}</div>
                        </td>

                        {{-- Status --}}
                        <td class="px-5 py-4 whitespace-nowrap text-center">
                            @php $status = strtolower($donation->status ?? ''); @endphp
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold
                                {{ in_array($status, ['completed', 'success', 'paid'])
                                    ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300'
                                    : ($status === 'pending'
                                        ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300'
                                        : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300') }}">
                                <span class="w-1.5 h-1.5 rounded-full flex-shrink-0
                                    {{ in_array($status, ['completed', 'success', 'paid']) ? 'bg-emerald-500'
                                        : ($status === 'pending' ? 'bg-amber-500' : 'bg-red-500') }}"></span>
                                {{ ucfirst($donation->status ?? 'unknown') }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-5 py-16 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <div class="w-14 h-14 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                                    <svg class="w-7 h-7 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">No records found</p>
                                    <p class="text-xs text-gray-400 mt-1">Try adjusting your search or filter criteria</p>
                                </div>
                                @if($search || $selectedProject || $dateFrom || $dateTo)
                                <button wire:click="resetFilters"
                                        class="text-xs text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300 font-semibold underline underline-offset-2 transition-colors">
                                    Clear all filters
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ═══ PAGINATION ═══ --}}
    @if($donations->hasPages())
    <div class="px-6 pb-6">
        {{ $donations->links() }}
    </div>
    @endif
</div>
