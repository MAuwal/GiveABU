<div>
    @if(session('message'))
        <div class="mb-4 px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm font-medium">
            {{ session('message') }}
        </div>
    @endif

    <!-- Header Actions -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Projects</h2>
            <p class="text-sm text-slate-500 mt-1">Manage and track all endowment projects</p>
        </div>
        <button wire:click="$dispatch('open-add-project-modal')" 
                class="inline-flex items-center px-5 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-xl shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200">
            <i class="fas fa-plus mr-2"></i> Add New Project
        </button>
    </div>

    <!-- Search and Filters -->
    <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 mb-8">
        <div class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <div class="relative">
                    <input wire:model.live="search" type="text" 
                           class="w-full pl-10 pr-4 py-2.5 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-slate-50 text-slate-800 placeholder-slate-400 transition-all duration-200" 
                           placeholder="Search projects by title, description...">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-slate-400"></i>
                    </div>
                </div>
            </div>
            <div class="w-full md:w-48">
                <select wire:model.live="statusFilter" 
                        class="w-full px-3 py-2.5 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-slate-50 text-slate-800 cursor-pointer transition-all duration-200">
                    <option value="">All Statuses</option>
                    <option value="active">Active Only</option>
                    <option value="closed">Closed Only</option>
                </select>
            </div>
            <div class="w-full md:w-48">
                <select wire:model.live="perPage" 
                        class="w-full px-3 py-2.5 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-slate-50 text-slate-800 cursor-pointer transition-all duration-200">
                    <option value="10">10 per page</option>
                    <option value="25">25 per page</option>
                    <option value="50">50 per page</option>
                    <option value="100">100 per page</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Projects Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">
        @foreach($projects as $project)
            <div class="bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 border border-slate-200 overflow-hidden group flex flex-col">
                <!-- Card Image/Icon -->
                <div class="relative h-48 bg-slate-100 overflow-hidden">
                    @if($project->icon_image)
                        <img src="{{ $project->icon_image_url }}"
                             alt="{{ $project->project_title }}" 
                             class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="hidden absolute inset-0 flex items-center justify-center bg-slate-100 text-slate-300">
                            <i class="fas fa-image text-4xl"></i>
                        </div>
                    @else
                        <div class="flex items-center justify-center h-full text-slate-300 bg-slate-50">
                            <i class="fas fa-project-diagram text-4xl"></i>
                        </div>
                    @endif
                    
                    <!-- Overlay Gradient -->
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-60"></div>

                    <!-- Photos Count Badge -->
                    <div class="absolute top-3 right-3">
                        <span class="px-2.5 py-1 bg-white/90 backdrop-blur-md text-slate-700 text-[10px] font-bold uppercase tracking-wider rounded-lg flex items-center shadow-sm">
                            <i class="fas fa-images mr-1.5 text-blue-500"></i> {{ $project->photos_count }}
                        </span>
                    </div>

                    <!-- Status Badge -->
                    <div class="absolute top-3 left-3">
                        <span class="px-2.5 py-1 {{ $project->status === 'active' ? 'bg-emerald-500 text-white' : 'bg-rose-500 text-white' }} text-[10px] font-bold uppercase tracking-wider rounded-lg flex items-center shadow-sm">
                            <i class="fas {{ $project->status === 'active' ? 'fa-check-circle' : 'fa-times-circle' }} mr-1.5"></i> {{ $project->status }}
                        </span>
                    </div>

                    <!-- Date Badge -->
                    <div class="absolute bottom-3 left-3">
                        <span class="text-white/90 text-xs font-medium drop-shadow-md">
                            {{ $project->created_at->format('M Y') }}
                        </span>
                    </div>
                </div>

                <!-- Card Content -->
                <div class="p-5 flex-1 flex flex-col">
                    <div class="mb-3">
                        <h3 class="text-lg font-bold text-slate-800 mb-1 line-clamp-1 group-hover:text-blue-600 transition-colors">
                            {{ $project->project_title }}
                        </h3>
                        <div class="h-1 w-12 bg-blue-500 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    </div>
                    
                    <p class="text-sm text-slate-500 line-clamp-2 mb-5 flex-1 leading-relaxed">
                        {{ $project->project_description }}
                    </p>

                    <!-- Funding Progress -->
                    <div class="mb-6 p-3 bg-slate-50 rounded-xl border border-slate-100">
                        <div class="flex justify-between text-xs font-bold uppercase tracking-wider mb-2">
                            <span class="text-blue-600">Raised</span>
                            <span class="text-slate-400">Target</span>
                        </div>
                        <div class="flex justify-between items-end mb-2">
                            <span class="text-sm font-bold text-slate-800">₦{{ number_format($project->calculated_raised ?? 0, 0) }}</span>
                            <span class="text-xs font-medium text-slate-500">₦{{ number_format($project->target, 0) }}</span>
                        </div>
                        <div class="w-full bg-slate-200 rounded-full h-1.5 overflow-hidden">
                            <div class="bg-blue-500 h-full rounded-full transition-all duration-500" 
                                 style="width: {{ $project->target > 0 ? min((($project->calculated_raised ?? 0) / $project->target) * 100, 100) : 0 }}%"></div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                        <div class="flex items-center gap-1">
                             <a href="{{ route('admin.project-details', $project->id) }}" 
                                class="inline-flex items-center px-2 py-1.5 border border-slate-200 text-xs font-semibold rounded-lg text-slate-600 bg-white hover:bg-slate-50 hover:text-blue-600 hover:border-blue-200 transition-all duration-200" 
                                title="View Details">
                                 <i class="fas fa-eye mr-1"></i> View
                             </a>
                             @livewire('admin.add-project-photos', ['project' => $project], key('card-photos-' . $project->id))
                             <button wire:click="$dispatch('manage-project-details', { projectId: {{ $project->id }} })" 
                                     class="inline-flex items-center px-2 py-1.5 border border-slate-200 text-xs font-semibold rounded-lg text-slate-600 bg-white hover:bg-slate-50 hover:text-purple-600 hover:border-purple-200 transition-all duration-200" 
                                     title="Manage Details">
                                 <i class="fas fa-file-alt mr-1"></i> Details
                             </button>
                        </div>
                        <button wire:click="editProject({{ $project->id }})"
                                class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-amber-500 hover:bg-amber-50 rounded-lg transition-all duration-200"
                                title="Edit Project">
                            <i class="fas fa-edit"></i>
                        </button>

                        @if($confirmingDeleteId === $project->id)
                            <div class="flex items-center gap-1 ml-auto">
                                <span class="text-xs text-rose-600 font-semibold">Delete?</span>
                                <button wire:click="deleteProject({{ $project->id }})"
                                        class="px-2 py-1 text-xs font-bold bg-rose-500 text-white rounded-lg hover:bg-rose-600 transition-colors">
                                    Yes
                                </button>
                                <button wire:click="cancelDelete"
                                        class="px-2 py-1 text-xs font-bold bg-slate-100 text-slate-600 rounded-lg hover:bg-slate-200 transition-colors">
                                    No
                                </button>
                            </div>
                        @else
                            <button wire:click="confirmDelete({{ $project->id }})"
                                    class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-rose-500 hover:bg-rose-50 rounded-lg transition-all duration-200 ml-auto"
                                    title="Delete Project">
                                <i class="fas fa-trash"></i>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if($projects->hasMorePages())
        <div class="flex justify-center mb-10">
            <button wire:click="loadMore" 
                    class="px-6 py-2.5 bg-white border border-slate-200 text-slate-600 font-semibold rounded-xl shadow-sm hover:bg-slate-50 hover:text-blue-600 hover:border-blue-200 transition-all duration-200 flex items-center gap-2">
                <span>See More Projects</span>
                <i class="fas fa-chevron-down text-xs"></i>
            </button>
        </div>
    @endif


@if($showDonationsModal && $selectedProject)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm" x-data="{ show: @entangle('showDonationsModal') }" x-show="show" x-cloak>
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-slate-50">
                <h3 class="text-lg font-bold text-slate-800">Donations for {{ $selectedProject->project_title }}</h3>
                <button @click="$wire.set('showDonationsModal', false)" class="text-slate-400 hover:text-slate-600 text-2xl font-bold transition-colors">&times;</button>
            </div>
            <div class="overflow-y-auto p-6 flex-1 bg-white">
                <!-- Project Images Section -->
                @if($selectedProject->photos && $selectedProject->photos->count() > 0)
                    <div class="mb-8">
                        <h4 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Project Images</h4>
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                            @foreach($selectedProject->photos as $photo)
                                <div class="relative group aspect-square">
                                    @php
                                        $imageUrl = $photo->image_url;
                                    @endphp
                                    <img src="{{ $imageUrl }}" 
                                         alt="Project Photo" 
                                         class="w-full h-full object-cover rounded-xl border border-slate-200 cursor-pointer hover:opacity-90 transition-opacity shadow-sm"
                                         onclick="window.open('{{ $imageUrl }}', '_blank')"
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div class="hidden w-full h-full bg-slate-100 rounded-xl flex items-center justify-center border border-slate-200">
                                        <span class="text-slate-400 text-xs">Image not found</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Donations List -->
                <div>
                    <h4 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">
                        Donations ({{ $selectedDonations->count() }})
                    </h4>
                    @forelse($selectedDonations as $donation)
                        <div class="mb-3 p-4 border border-slate-100 rounded-xl bg-slate-50 hover:bg-white hover:border-blue-100 hover:shadow-sm transition-all duration-200">
                            <div class="flex justify-between items-start">
                                <div>
                                    <div class="font-bold text-slate-800">{{ $donation->donor->surname ?? '' }} {{ $donation->donor->name ?? '' }}</div>
                                    <div class="text-xs text-slate-500 mt-1">Ref: {{ $donation->payment_reference ?? '—' }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="font-bold text-emerald-600">₦{{ number_format($donation->amount, 0) }}</div>
                                    <div class="text-xs text-slate-500 mt-1">{{ $donation->created_at->format('M d, Y') }}</div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-slate-500">
                            <i class="fas fa-donate text-3xl text-slate-300 mb-2"></i>
                            <p>No donations found for this project.</p>
                        </div>
                    @endforelse
                </div>
            </div>
            <div class="flex justify-end gap-2 px-6 py-4 border-t border-slate-100 bg-slate-50">
                <button @click="$wire.set('showDonationsModal', false)" class="px-4 py-2 bg-white border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 transition-colors font-medium text-sm shadow-sm">Close</button>
            </div>
        </div>
    </div>
@endif


    @livewire('admin.manage-project-details')
</div>
