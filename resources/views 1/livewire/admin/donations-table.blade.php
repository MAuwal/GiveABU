<div>
    <!-- Search and Filters -->
    <div class="flex flex-col md:flex-row gap-4 mb-6">
        <div class="flex-1">
            <div class="relative">
                <input wire:model.live="search" type="text" 
                       class="w-full pl-10 pr-4 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white text-slate-800 placeholder-slate-400 shadow-sm" 
                       placeholder="Search projects by name or description...">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-slate-400"></i>
                </div>
            </div>
        </div>
        <div class="w-full md:w-48">
            <select wire:model.live="perPage" 
                    class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white text-slate-800 shadow-sm">
                <option value="10">10 per page</option>
                <option value="25">25 per page</option>
                <option value="50">50 per page</option>
                <option value="100">100 per page</option>
            </select>
        </div>
        <div>
            <button wire:click="exportProjects" 
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 shadow-sm transition-colors whitespace-nowrap">
                <i class="fas fa-file-excel mr-2"></i> Export
            </button>
        </div>
    </div>

    <!-- Projects Table -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Project Name</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Target</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Raised</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Donors</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-200">
                    @forelse($projects as $project)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                {{ $project->id ?? '—' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-semibold text-slate-800">{{ $project->project_title ?? 'Endowment Project' }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">
                                @if($project->target)
                                    <div>
                                        <div class="font-medium">₦{{ number_format($project->target, 2) }}</div>
                                        @php
                                            $amountInWords = \App\Helpers\NumberToWords::convert((int)$project->target);
                                        @endphp
                                        <div class="text-xs text-slate-500 mt-0.5">
                                            {{ $amountInWords }} Naira Only
                                        </div>
                                    </div>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">
                                @php
                                    // Use raised_amount from query or calculate from donations
                                    $raised = $project->raised_amount ?? ($project->raised ?? 0);
                                @endphp
                                <div class="font-medium">₦{{ number_format($raised, 2) }}</div>
                                @if($project->target && $project->target > 0)
                                    @php
                                        $percentage = ($raised / $project->target) * 100;
                                    @endphp
                                    <div class="mt-1.5 w-32">
                                        <div class="w-full bg-slate-100 rounded-full h-1.5">
                                            <div class="bg-blue-600 h-1.5 rounded-full transition-all duration-500" style="width: {{ min($percentage, 100) }}%"></div>
                                        </div>
                                        <span class="text-xs text-slate-500 mt-0.5 block">{{ number_format($percentage, 1) }}%</span>
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @php
                                    $donorCount = $project->donor_count ?? 0;
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-50 text-purple-700 border border-purple-100">
                                    <i class="fas fa-users mr-1.5"></i>
                                    {{ $donorCount }} {{ $donorCount === 1 ? 'Donor' : 'Donors' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button wire:click="showProjectDetails({{ $project->id ?? 'null' }})"
                                        class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors shadow-sm"
                                        title="View Project Details">
                                    <i class="fas fa-eye mr-1.5"></i> Details
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-sm text-slate-500">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="fas fa-folder-open text-4xl mb-3 text-slate-300"></i>
                                    <p>No projects found.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="flex items-center justify-between mt-6">
        <div class="text-sm text-slate-600">
            Showing {{ $projects->firstItem() ?? 0 }} to {{ $projects->lastItem() ?? 0 }} of {{ $projects->total() }} projects
        </div>
        <div>
            {{ $projects->links() }}
        </div>
    </div>

    <!-- Project Details Modal -->
    @if($showDetailsModal && $selectedProject)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm" 
             x-data="{ show: @entangle('showDetailsModal') }" 
             x-show="show" 
             x-cloak
             @keydown.escape.window="$wire.closeDetailsModal()">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-slate-50">
                    <h3 class="text-lg font-bold text-slate-800">
                        Project Details: {{ $selectedProject->project_title ?? 'Endowment Project' }}
                    </h3>
                    <button @click="$wire.closeDetailsModal()" 
                            class="text-slate-400 hover:text-slate-600 text-2xl font-bold transition-colors">&times;</button>
                </div>
                <div class="overflow-y-auto p-6 flex-1 bg-white">
                    <!-- Project Information -->
                    <div class="mb-8 p-6 bg-slate-50 rounded-xl border border-slate-100">
                        <h4 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Project Information</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1 block">Project Name</label>
                                <p class="text-base font-medium text-slate-900">{{ $selectedProject->project_title ?? 'Endowment Project' }}</p>
                            </div>
                            @if($selectedProject->project_description ?? null)
                            <div>
                                <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1 block">Description</label>
                                <p class="text-sm text-slate-700 leading-relaxed">{{ $selectedProject->project_description }}</p>
                            </div>
                            @endif
                            <div>
                                <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1 block">Target</label>
                                <p class="text-base font-medium text-slate-900">
                                    @if($selectedProject->target ?? null)
                                        ₦{{ number_format($selectedProject->target, 2) }}
                                    @else
                                        <span class="text-slate-400">Not set</span>
                                    @endif
                                </p>
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1 block">Total Raised</label>
                                <p class="text-lg font-bold text-emerald-600">
                                    ₦{{ number_format($totalRaised, 2) }}
                                </p>
                            </div>
                            @if(($selectedProject->target ?? null) && ($selectedProject->target ?? 0) > 0)
                            <div class="col-span-2">
                                <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1 block">Progress</label>
                                @php
                                    $percentage = ($totalRaised / $selectedProject->target) * 100;
                                @endphp
                                <div class="mt-1">
                                    <div class="w-full bg-slate-200 rounded-full h-2.5">
                                        <div class="bg-blue-600 h-2.5 rounded-full transition-all duration-500" style="width: {{ min($percentage, 100) }}%"></div>
                                    </div>
                                    <p class="text-xs font-medium text-slate-600 mt-1.5">{{ number_format($percentage, 1) }}% complete</p>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Donations List -->
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-sm font-bold text-slate-900 uppercase tracking-wider">
                                Donations ({{ $selectedDonations->total() }})
                            </h4>
                            <div class="flex items-center gap-2">
                                <button wire:click="exportModalDonations" 
                                        class="inline-flex items-center px-3 py-1 border border-slate-300 text-xs font-medium rounded text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 shadow-sm transition-colors mr-2">
                                    <i class="fas fa-file-excel mr-1.5 text-emerald-600"></i> Export
                                </button>
                                <label class="text-xs font-medium text-slate-500">Show:</label>
                                <select wire:model.live="perPageModal" 
                                        class="px-2 py-1 text-xs border border-slate-200 rounded bg-white text-slate-700 focus:ring-1 focus:ring-blue-500">
                                    <option value="5">5</option>
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </div>
                        </div>
                        <div class="bg-white rounded-lg border border-slate-200 overflow-hidden">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-slate-200">
                                    <thead class="bg-slate-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Donor</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Dept/Faculty/Year</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Amount</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Date</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Reference</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-slate-200">
                                        @forelse($selectedDonations as $donation)
                                            <tr class="hover:bg-slate-50 transition-colors">
                                                <td class="px-4 py-3 text-sm text-slate-900">
                                                    @if($donation->donor)
                                                        <div>
                                                            <div class="font-medium">{{ $donation->donor->surname }} {{ $donation->donor->name }}</div>
                                                            <div class="text-xs text-slate-500">{{ $donation->donor->email ?? $donation->donor->phone }}</div>
                                                        </div>
                                                    @else
                                                        <span class="text-slate-400 italic">Anonymous</span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3 text-sm text-slate-600">
                                                    @if($donation->donor)
                                                        <div class="space-y-0.5">
                                                            @if($donation->donor->department)
                                                                <div class="text-xs">
                                                                    <span class="font-medium text-slate-500">Dept:</span>
                                                                    <span class="text-slate-700">{{ $donation->donor->department->current_name ?? $donation->donor->department->name ?? '—' }}</span>
                                                                </div>
                                                            @endif
                                                            @if($donation->donor->faculty)
                                                                <div class="text-xs">
                                                                    <span class="font-medium text-slate-500">Faculty:</span>
                                                                    <span class="text-slate-700">{{ $donation->donor->faculty->current_name ?? $donation->donor->faculty->name ?? '—' }}</span>
                                                                </div>
                                                            @endif
                                                            @if($donation->donor->entry_year)
                                                                <div class="text-xs">
                                                                    <span class="font-medium text-slate-500">Entry:</span>
                                                                    <span class="text-slate-700">{{ $donation->donor->entry_year }}</span>
                                                                </div>
                                                            @endif
                                                            @if(!$donation->donor->department && !$donation->donor->faculty && !$donation->donor->entry_year)
                                                                <span class="text-slate-400 text-xs">—</span>
                                                            @endif
                                                        </div>
                                                    @else
                                                        <span class="text-slate-400 text-xs">—</span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3 text-sm font-bold text-emerald-600">
                                                    ₦{{ number_format($donation->amount, 2) }}
                                                </td>
                                                <td class="px-4 py-3 text-sm text-slate-600">
                                                    {{ $donation->created_at->format('M d, Y') }}
                                                    <div class="text-xs text-slate-400">
                                                        {{ $donation->created_at->format('H:i') }}
                                                    </div>
                                                </td>
                                                <td class="px-4 py-3 text-sm">
                                                    @if($donation->status === 'success' || $donation->status === 'paid' || $donation->status === 'completed')
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-100">
                                                            <i class="fas fa-check-circle mr-1"></i> Paid
                                                        </span>
                                                    @elseif($donation->status === 'pending')
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-50 text-amber-700 border border-amber-100">
                                                            <i class="fas fa-clock mr-1"></i> Pending
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200">
                                                            {{ ucfirst($donation->status ?? 'Unknown') }}
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3 text-sm text-slate-500 font-mono text-xs">
                                                    {{ $donation->payment_reference ?? '—' }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="px-4 py-8 text-center text-sm text-slate-500">
                                                    No donations found for this project.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Pagination Controls -->
                        @if($selectedDonations->hasPages())
                            <div class="flex items-center justify-between mt-4">
                                <div class="text-xs text-slate-500">
                                    Showing {{ $selectedDonations->firstItem() ?? 0 }} to {{ $selectedDonations->lastItem() ?? 0 }} of {{ $selectedDonations->total() }} donations
                                </div>
                                <div class="flex gap-1">
                                    {{-- Previous Button --}}
                                    <button wire:click="gotoModalPage({{ $selectedDonations->currentPage() - 1 }})"
                                            @if($selectedDonations->onFirstPage()) disabled @endif
                                            class="px-2 py-1 text-xs border border-slate-200 rounded bg-white text-slate-600 hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed">
                                        <i class="fas fa-chevron-left"></i>
                                    </button>
                                    
                                    {{-- Page Numbers --}}
                                    @foreach(range(1, $selectedDonations->lastPage()) as $page)
                                        @if($page == 1 || $page == $selectedDonations->lastPage() || abs($page - $selectedDonations->currentPage()) < 2)
                                            <button wire:click="gotoModalPage({{ $page }})"
                                                    class="px-2 py-1 text-xs border border-slate-200 rounded {{ $page == $selectedDonations->currentPage() ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-slate-600 hover:bg-slate-50' }}">
                                                {{ $page }}
                                            </button>
                                        @elseif(abs($page - $selectedDonations->currentPage()) == 2)
                                            <span class="px-1 py-1 text-slate-400 text-xs">...</span>
                                        @endif
                                    @endforeach
                                    
                                    {{-- Next Button --}}
                                    <button wire:click="gotoModalPage({{ $selectedDonations->currentPage() + 1 }})"
                                            @if(!$selectedDonations->hasMorePages()) disabled @endif
                                            class="px-2 py-1 text-xs border border-slate-200 rounded bg-white text-slate-600 hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed">
                                        <i class="fas fa-chevron-right"></i>
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="flex justify-end gap-2 px-6 py-4 border-t border-slate-100 bg-slate-50">
                    <button @click="$wire.closeDetailsModal()" 
                            class="px-4 py-2 bg-white border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 transition-colors font-medium text-sm shadow-sm">
                        Close
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
