<div>
    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
        Project Donations Overview
    </h2>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @foreach($projects as $project)
            <div class="bg-white dark:bg-gray-800 shadow-lg rounded-xl overflow-hidden flex flex-col hover:shadow-2xl transition-shadow duration-300">
                
                {{-- Project Icon --}}
                @if($project->icon_image_url)
                    <img src="{{ $project->icon_image_url }}" alt="Project Icon" class="w-full h-48 object-cover">
                @else
                    <div class="w-full h-48 flex items-center justify-center bg-gray-100 dark:bg-gray-700">
                        <i class="fas fa-university text-6xl text-gray-300 dark:text-gray-500"></i>
                    </div>
                @endif

                <div class="p-6 flex-1 flex flex-col">
                    <h3 class="text-xl font-semibold text-indigo-700 dark:text-indigo-400 mb-2">
                        {{ $project->project_title }}
                    </h3>
                    <p class="text-gray-600 dark:text-gray-300 mb-4 flex-1">
                        {{ $project->project_description }}
                    </p>

                    {{-- Stats --}}
                    <div class="flex flex-col gap-2 mt-2">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">Total Donations</span>
                            <span class="text-lg font-bold text-green-600">
                                ₦{{ number_format($project->total_donations, 2) }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">Number of Donations</span>
                            <span class="text-base font-semibold text-blue-600">
                                {{ $project->donations->count() }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">Created</span>
                            <span class="text-sm text-gray-100">
                                {{ $project->created_at->format('M d, Y') }}
                            </span>
                        </div>
                    </div>

                    {{-- Toggle Button (instant color/text change) --}}
                    <button
                        wire:click="toggleStatus({{ $project->id }})"
                        class="mt-4 px-4 py-2 rounded-lg text-white font-semibold
                            {{ $project->deleted_at ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700' }}">
                        {{ $project->deleted_at ? 'Activate' : 'Deactivate' }}
                    </button>
                </div>
            </div>
        @endforeach
    </div>

    <script>
        Livewire.on('notify', data => {
            console.log(data.message);
        });
    </script>
</div>


