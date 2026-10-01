<div>
    <div x-data='{ 
        activeIndex: 0,
        showDesc: true,
        photos: @json($photos, JSON_HEX_APOS|JSON_HEX_QUOT)
    }' class="min-h-screen bg-slate-50 p-6">
        
        <div class="max-w-7xl mx-auto">
            <!-- Breadcrumb / Back Button -->
            <div class="mb-8 flex items-center justify-between">
                <a href="{{ route('admin.projects') }}" class="inline-flex items-center text-slate-500 hover:text-blue-600 transition-colors font-medium">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Projects
                </a>
                <div class="flex gap-3">
                    <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider {{ $project->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                        {{ $project->status }}
                    </span>
                </div>
            </div>
    
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Left Column: Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- Project Header Card -->
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 flex items-start gap-6">
                        <div class="w-20 h-20 flex-shrink-0 rounded-xl overflow-hidden border border-slate-100 shadow-sm bg-slate-50">
                            @if($project->icon_image)
                                <img src="{{ $project->icon_image_url }}" class="w-full h-full object-cover" alt="Icon">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-slate-400">
                                    <i class="fas fa-project-diagram text-2xl"></i>
                                </div>
                            @endif
                        </div>
                        <div class="flex-1">
                            <h1 class="text-2xl font-bold text-slate-800 mb-2 leading-tight">{{ $project->project_title }}</h1>
                            <p class="text-slate-500 text-sm leading-relaxed line-clamp-2">{{ $project->project_description }}</p>
                        </div>
                    </div>

                    <!-- Image Gallery -->
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                        <div class="p-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                            <h3 class="font-bold text-slate-700">Project Gallery</h3>
                            <span class="text-xs font-medium text-slate-500 bg-white px-2 py-1 rounded-md border border-slate-200 shadow-sm">
                                <i class="fas fa-camera mr-1"></i> <span x-text="photos.length"></span> Photos
                            </span>
                        </div>
                        
                        <div class="p-6">
                            <!-- Main Image Display -->
                            <div class="relative aspect-video rounded-xl overflow-hidden bg-slate-100 border border-slate-200 mb-4 group">
                                <template x-if="photos.length > 0">
                                    <img :src="photos[activeIndex].url" 
                                         class="w-full h-full object-contain bg-slate-900 transition-transform duration-500" 
                                         :alt="photos[activeIndex].title">
                                </template>
                                <template x-if="photos.length === 0">
                                    <div class="w-full h-full flex flex-col items-center justify-center text-slate-400">
                                        <i class="fas fa-images text-5xl mb-3 opacity-50"></i>
                                        <p class="font-medium">No photos available</p>
                                    </div>
                                </template>
        
                                <!-- Counter Badge -->
                                <template x-if="photos.length > 0">
                                    <div class="absolute top-4 left-4">
                                        <span class="px-3 py-1 bg-black/70 backdrop-blur-sm text-white text-xs font-bold rounded-full shadow-sm" x-text="(activeIndex + 1) + ' / ' + photos.length"></span>
                                    </div>
                                </template>

                                <!-- Navigation Arrows -->
                                <template x-if="photos.length > 1">
                                    <div class="absolute inset-0 flex items-center justify-between px-4 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
                                        <button @click="activeIndex = (activeIndex === 0) ? photos.length - 1 : activeIndex - 1" class="w-10 h-10 rounded-full bg-white/90 text-slate-800 shadow-lg flex items-center justify-center hover:bg-white pointer-events-auto transition-transform hover:scale-110">
                                            <i class="fas fa-chevron-left"></i>
                                        </button>
                                        <button @click="activeIndex = (activeIndex === photos.length - 1) ? 0 : activeIndex + 1" class="w-10 h-10 rounded-full bg-white/90 text-slate-800 shadow-lg flex items-center justify-center hover:bg-white pointer-events-auto transition-transform hover:scale-110">
                                            <i class="fas fa-chevron-right"></i>
                                        </button>
                                    </div>
                                </template>
                            </div>
        
                            <!-- Photo Description -->
                            <template x-if="photos.length > 0 && (photos[activeIndex].title || photos[activeIndex].description)">
                                <div class="mb-6 p-4 bg-blue-50 border border-blue-100 rounded-xl">
                                    <h4 class="text-sm font-bold text-blue-900 mb-1" x-text="photos[activeIndex].title || 'Photo Details'"></h4>
                                    <p class="text-sm text-blue-700 leading-relaxed" x-text="photos[activeIndex].description"></p>
                                </div>
                            </template>
        
                            <!-- Thumbnails -->
                            <div class="flex gap-3 overflow-x-auto pb-2 custom-scrollbar">
                                <template x-for="(photo, index) in photos" :key="index">
                                    <button @click="activeIndex = index" 
                                            class="relative flex-shrink-0 w-20 h-20 rounded-lg overflow-hidden border-2 transition-all duration-200"
                                            :class="activeIndex === index ? 'border-blue-500 ring-2 ring-blue-100 ring-offset-2' : 'border-transparent opacity-60 hover:opacity-100 hover:border-slate-300'">
                                        <img :src="photo.url" class="w-full h-full object-cover" alt="Thumbnail">
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>

                    @if($project->details)
                        <style>
                            .rich-content ul { list-style-type: disc; padding-left: 1.5rem; margin-bottom: 1rem; color: #475569; }
                            .rich-content ol { list-style-type: decimal; padding-left: 1.5rem; margin-bottom: 1rem; color: #475569; }
                            .rich-content p { margin-bottom: 1rem; color: #64748b; line-height: 1.7; }
                            .rich-content p:last-child { margin-bottom: 0; }
                            .rich-content strong { color: #1e293b; font-weight: 600; }
                        </style>

                        <!-- Project Comprehensive Details -->
                        <div class="space-y-6">
                            @if(!empty(strip_tags($project->details->background)))
                                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                                    <div class="p-6">
                                        <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center">
                                            <i class="fas fa-history text-blue-500 mr-3"></i> Background
                                        </h3>
                                        <div class="rich-content bg-slate-50/50 p-5 rounded-xl border border-slate-100 italic">
                                            {!! $project->details->background !!}
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if(!empty(strip_tags($project->details->challenges)))
                                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                                    <div class="p-6">
                                        <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center">
                                            <i class="fas fa-exclamation-triangle text-amber-500 mr-3"></i> Key Challenges
                                        </h3>
                                        <div class="rich-content">
                                            {!! $project->details->challenges !!}
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if(!empty(strip_tags($project->details->proposed_interventions)))
                                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                                    <div class="p-6">
                                        <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center">
                                            <i class="fas fa-tools text-indigo-500 mr-3"></i> Proposed Interventions
                                        </h3>
                                        <div class="rich-content">
                                            {!! $project->details->proposed_interventions !!}
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @php
                                $hasBeneficiaries = !empty(strip_tags($project->details->beneficiaries));
                                $hasBudget = !empty(strip_tags($project->details->budget_estimates));
                            @endphp

                            @if($hasBeneficiaries || $hasBudget)
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    @if($hasBeneficiaries)
                                        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden border-t-4 border-t-blue-500">
                                            <div class="p-6">
                                                <h3 class="text-base font-bold text-slate-800 mb-3 flex items-center">
                                                    <i class="fas fa-users text-blue-500 mr-2"></i> Beneficiaries
                                                </h3>
                                                <div class="rich-content text-sm">
                                                    {!! $project->details->beneficiaries !!}
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    @if($hasBudget)
                                        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden border-t-4 border-t-emerald-500">
                                            <div class="p-6">
                                                <h3 class="text-base font-bold text-slate-800 mb-3 flex items-center">
                                                    <i class="fas fa-coins text-emerald-500 mr-2"></i> Budget Estimates
                                                </h3>
                                                <div class="rich-content text-sm">
                                                    {!! $project->details->budget_estimates !!}
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            @if(!empty(strip_tags($project->details->expected_outcomes)))
                                <div class="bg-white rounded-2xl shadow-sm border border-emerald-200 overflow-hidden bg-emerald-50/20">
                                    <div class="p-6">
                                        <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center">
                                            <i class="fas fa-chart-line text-emerald-600 mr-3"></i> Expected Outcomes
                                        </h3>
                                        <div class="rich-content">
                                            {!! $project->details->expected_outcomes !!}
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- Right Column: Sidebar -->
                <div class="space-y-6">
                    <!-- Funding Status Card -->
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                        <div class="p-6">
                            <h3 class="font-bold text-slate-800 mb-6 flex items-center">
                                <span class="w-1 h-6 bg-blue-500 rounded-full mr-3"></span>
                                Funding Status
                            </h3>
                            
                            <div class="space-y-6">
                                <div>
                                    <div class="flex justify-between items-end mb-2">
                                        <span class="text-sm font-medium text-slate-500 uppercase tracking-wider">Raised</span>
                                        <span class="text-2xl font-bold text-emerald-600">₦{{ number_format($project->raised, 2) }}</span>
                                    </div>
                                    <div class="flex justify-between items-end mb-4">
                                        <span class="text-xs font-medium text-slate-400 uppercase tracking-wider">Target</span>
                                        <span class="text-sm font-bold text-slate-600">₦{{ number_format($project->target, 2) }}</span>
                                    </div>

                                    <div class="relative h-3 bg-slate-100 rounded-full overflow-hidden">
                                        <div class="absolute inset-y-0 left-0 bg-gradient-to-r from-blue-500 to-emerald-500 rounded-full transition-all duration-1000"
                                             style="width: {{ $project->target > 0 ? min(($project->raised / $project->target) * 100, 100) : 0 }}%">
                                        </div>
                                    </div>
                                    
                                    <div class="mt-2 flex justify-between text-xs font-medium">
                                        <span class="text-blue-600">{{ $project->target > 0 ? round(($project->raised / $project->target) * 100, 1) : 0 }}% Funded</span>
                                        <span class="text-slate-400">₦{{ number_format(max(0, $project->target - $project->raised)) }} to go</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-slate-50 px-6 py-4 border-t border-slate-100">
                             <div class="grid grid-cols-2 gap-4 text-center">
                                 <div>
                                     <span class="block text-xs text-slate-500 uppercase font-bold">Donors</span>
                                     <span class="block text-lg font-bold text-slate-700">{{ $project->donations()->count() }}</span>
                                 </div>
                                 <div>
                                     <span class="block text-xs text-slate-500 uppercase font-bold">Avg. Donation</span>
                                     <span class="block text-lg font-bold text-slate-700">₦{{ $project->donations()->count() > 0 ? number_format($project->raised / $project->donations()->count()) : '0' }}</span>
                                 </div>
                             </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                        <h3 class="font-bold text-slate-800 mb-4">Quick Actions</h3>
                        <div class="space-y-3">
                            <a href="{{ route('admin.projects') }}" class="block w-full py-2.5 px-4 bg-slate-50 hover:bg-slate-100 text-slate-600 font-medium rounded-xl text-center transition-colors border border-slate-200">
                                <i class="fas fa-list mr-2"></i> View All Projects
                            </a>
                            <!-- Add more actions if needed, e.g. Edit Project -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar {
            height: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 3px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</div>