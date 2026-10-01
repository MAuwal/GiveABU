<div>
    @if($showModal)
    {{-- ① Fixed full-screen stacking root --}}
    <div class="fixed inset-0 z-[9999]" wire:keydown.escape.window="close">

        {{-- ② Backdrop: absolute fills the fixed parent, sits behind everything --}}
        <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" wire:click="close"></div>

        {{-- ③ Scrollable centering container --}}
        <div class="absolute inset-0 overflow-y-auto flex justify-center p-4 sm:p-8">
            {{-- ④ Modal panel --}}
            <div class="relative w-full max-w-lg bg-white dark:bg-gray-900 rounded-2xl shadow-2xl self-start mt-6">

                {{-- Header --}}
                <div class="bg-gradient-to-r from-emerald-800 to-emerald-600 px-6 py-5 rounded-t-2xl">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-white leading-tight">Generate Excel Report</h3>
                                <p class="text-xs text-emerald-200 mt-0.5">Multi-sheet workbook with charts &amp; formatted data</p>
                            </div>
                        </div>
                        <button wire:click="close" class="text-white/70 hover:text-white transition-colors mt-0.5 ml-4">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="p-6 space-y-5">

                    {{-- Active filters summary --}}
                    @if($search || $dateFrom || $dateTo || $projectId)
                    <div class="rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 p-3">
                        <p class="text-xs font-semibold text-emerald-800 dark:text-emerald-300 mb-2 uppercase tracking-wider">Filters from current page</p>
                        <div class="flex flex-wrap gap-1.5">
                            @if($search)
                            <span class="text-xs bg-emerald-100 dark:bg-emerald-800/40 text-emerald-700 dark:text-emerald-300 px-2 py-0.5 rounded-full">
                                Search: "{{ \Illuminate\Support\Str::limit($search, 20) }}"
                            </span>
                            @endif
                            @if($dateFrom)
                            <span class="text-xs bg-emerald-100 dark:bg-emerald-800/40 text-emerald-700 dark:text-emerald-300 px-2 py-0.5 rounded-full">
                                From: {{ $dateFrom }}
                            </span>
                            @endif
                            @if($dateTo)
                            <span class="text-xs bg-emerald-100 dark:bg-emerald-800/40 text-emerald-700 dark:text-emerald-300 px-2 py-0.5 rounded-full">
                                To: {{ $dateTo }}
                            </span>
                            @endif
                            @if($projectId)
                            <span class="text-xs bg-emerald-100 dark:bg-emerald-800/40 text-emerald-700 dark:text-emerald-300 px-2 py-0.5 rounded-full">
                                Project filter active
                            </span>
                            @endif
                        </div>
                    </div>
                    @endif

                    {{-- Date range override --}}
                    <div>
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Date Range</p>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">From</label>
                                <input wire:model="dateFrom" type="date"
                                       class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">To</label>
                                <input wire:model="dateTo" type="date"
                                       class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                            </div>
                        </div>
                        <p class="text-xs text-gray-400 mt-1.5">Leave empty to export all records regardless of date.</p>
                    </div>

                    {{-- Sheet selection --}}
                    <div>
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Sheets to Include</p>
                        <div class="space-y-2">
                            @foreach($allSheets as $key => $sheet)
                            <label class="flex items-start gap-3 p-3 rounded-xl border cursor-pointer transition-all duration-150
                                {{ in_array($key, $sheets)
                                    ? 'bg-emerald-50 dark:bg-emerald-900/20 border-emerald-300 dark:border-emerald-700'
                                    : 'bg-gray-50 dark:bg-gray-800 border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600' }}">
                                <input type="checkbox"
                                       wire:click="toggleSheet('{{ $key }}')"
                                       {{ in_array($key, $sheets) ? 'checked' : '' }}
                                       class="mt-0.5 h-4 w-4 rounded text-emerald-600 border-gray-300 focus:ring-emerald-500 cursor-pointer flex-shrink-0">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-base leading-none">{{ $sheet['icon'] }}</span>
                                        <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $sheet['label'] }}</span>
                                    </div>
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $sheet['desc'] }}</p>
                                </div>
                            </label>
                            @endforeach
                        </div>
                        @if(empty($sheets))
                        <p class="text-xs text-red-500 mt-2">Select at least one sheet to generate the report.</p>
                        @else
                        <p class="text-xs text-gray-400 mt-2">
                            {{ count($sheets) }} sheet{{ count($sheets) !== 1 ? 's' : '' }} selected &nbsp;·&nbsp; Charts included in Dashboard &amp; Trends
                        </p>
                        @endif
                    </div>

                    {{-- VBA note --}}
                    <div class="rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 p-3 flex gap-2.5">
                        <svg class="w-4 h-4 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-xs text-amber-700 dark:text-amber-300">
                            To use the <strong>VBA macro template</strong> for advanced interactivity,
                            <a href="{{ asset('templates/report-vba.txt') }}" target="_blank" class="underline font-semibold">download the VBA code</a>
                            and paste it into the Excel Visual Basic Editor (Alt+F11).
                        </p>
                    </div>
                </div>

                {{-- Footer actions --}}
                <div class="px-6 pb-6 flex items-center justify-between gap-3">
                    <button wire:click="close"
                            class="px-4 py-2.5 text-sm font-medium text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-xl transition-colors">
                        Cancel
                    </button>
                    <button wire:click="generate"
                            @if(empty($sheets)) disabled @endif
                            class="flex items-center gap-2 px-6 py-2.5 text-sm font-semibold text-white rounded-xl shadow-sm transition-all duration-150
                                {{ empty($sheets)
                                    ? 'bg-gray-300 cursor-not-allowed'
                                    : 'bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Generate &amp; Download
                    </button>
                </div>

            </div>{{-- /modal panel --}}
        </div>{{-- /scrollable centering container --}}
    </div>{{-- /fixed stacking root --}}
    @endif
</div>
