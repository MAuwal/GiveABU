<div>
    <button wire:click="open" class="inline-flex items-center px-3 py-1.5 border border-slate-200 text-xs font-semibold rounded-lg text-slate-600 bg-white hover:bg-slate-50 hover:text-blue-600 hover:border-blue-200 transition-all duration-200 shadow-sm" title="Manage Photos">
        <i class="fas fa-images mr-1.5"></i> Photos
    </button>
    
    @if ($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm overflow-y-auto" wire:click.self="closeModal">
        <div class="relative w-full max-w-4xl mx-4 my-6 bg-white rounded-2xl shadow-2xl border border-slate-100 flex flex-col max-h-[90vh]">
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 rounded-t-2xl flex justify-between items-center">
                <h3 class="text-lg font-bold text-slate-800">
                    Manage Photos for "{{ $project?->project_title ?? '' }}"
                </h3>
                <button type="button" class="text-slate-400 hover:text-slate-600 transition-colors" wire:click="closeModal">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6 overflow-y-auto">
                @if (session()->has('message'))
                    <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg text-sm flex items-center">
                        <i class="fas fa-check-circle mr-2"></i>
                        {{ session('message') }}
                    </div>
                @endif
                @if (session()->has('error'))
                    <div class="mb-4 p-3 bg-rose-50 border border-rose-200 text-rose-700 rounded-lg text-sm flex items-center">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        {{ session('error') }}
                    </div>
                @endif

                <!-- Upload Form -->
                <form wire:submit.prevent="savePhotos" class="mb-8 p-5 bg-slate-50 rounded-xl border border-slate-100">
                    <div class="mb-4">
                        <label for="photos{{ $project->id}}" class="block text-sm font-bold text-slate-700 mb-2">Select Photos to Upload</label>
                        <input type="file" wire:model="photos" id="photos{{ $project->id}}" multiple
                               class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition-all duration-200 bg-white border border-slate-200 rounded-lg cursor-pointer">
                        <div wire:loading wire:target="photos" class="flex items-center gap-2 mt-2 text-blue-600 text-xs">
                            <svg class="animate-spin h-3 w-3" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                            </svg>
                            Uploading photos, please wait...
                        </div>
                        @error('photos.*') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    @if ($photos && count($photos) > 0)
                        <div class="mb-4 space-y-4">
                            <label class="block text-sm font-bold text-slate-700 mb-2">Add Details for Selected Photos</label>
                            <div class="grid grid-cols-1 gap-4">
                                @foreach ($photos as $index => $photo)
                                    <div class="flex items-start space-x-4 p-3 bg-white rounded-xl border border-slate-200 shadow-sm" wire:key="preview-{{ $index }}-{{ $refreshKey ?? 0 }}">
                                        <div class="w-24 h-24 flex-shrink-0 rounded-lg overflow-hidden border border-slate-200 bg-slate-100">
                                            <img src="{{ $photo->temporaryUrl() }}" class="object-cover w-full h-full" alt="Preview">
                                        </div>
                                        <div class="flex-1 space-y-3">
                                            <input 
                                                type="text"
                                                wire:model="titles.{{ $index }}" 
                                                placeholder="Photo Title (optional)..."
                                                class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                            >
                                            <textarea 
                                                wire:model="descriptions.{{ $index }}" 
                                                placeholder="Photo Description (optional)..."
                                                class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                                rows="2"
                                            ></textarea>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit"
                                    wire:loading.attr="disabled"
                                    wire:loading.class="opacity-60 cursor-not-allowed"
                                    wire:target="photos,savePhotos"
                                    class="px-6 py-2.5 bg-blue-600 text-white font-semibold rounded-xl shadow-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 flex items-center gap-2">
                                <span wire:loading.remove wire:target="photos,savePhotos">Save Photos</span>
                                <span wire:loading wire:target="photos,savePhotos" class="flex items-center gap-2">
                                    <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                                    </svg>
                                    Saving...
                                </span>
                            </button>
                        </div>
                    @endif
                </form>

                <!-- Existing Photos Gallery -->
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-lg font-bold text-slate-800">
                        Saved Photos <span class="text-slate-400 text-sm font-normal ml-2">({{ $project?->photos?->count() ?? 0 }})</span>
                    </h4>
                </div>
                
                @if ($project?->photos && $project->photos->isEmpty())
                    <div class="text-center py-12 bg-slate-50 rounded-xl border border-slate-100 border-dashed">
                        <i class="fas fa-images text-4xl text-slate-300 mb-3"></i>
                        <p class="text-slate-500">No photos have been added yet. Upload photos above to get started.</p>
                    </div>
                @elseif ($project?->photos && $project->photos->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 max-h-[500px] overflow-y-auto p-1" wire:key="photos-grid-{{ $refreshKey ?? 0 }}">
                        @foreach ($project->photos as $photo)
                            <div wire:key="photo-{{ $photo->id }}-{{ $photo->updated_at?->timestamp ?? time() }}-{{ $refreshKey ?? 0 }}" class="bg-white p-3 rounded-xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow duration-200">
                                <div class="relative group mb-3 aspect-video rounded-lg overflow-hidden bg-slate-100">
                                    <img src="{{ $photo->image_url }}" 
                                         alt="Project Photo" 
                                         class="w-full h-full object-cover"
                                         loading="lazy">
                                    <div class="absolute inset-0 bg-black/60 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                        <button wire:click="deletePhoto({{ $photo->id }})" wire:confirm="Are you sure you want to delete this photo?" class="w-10 h-10 flex items-center justify-center bg-white/20 backdrop-blur-sm rounded-full text-white hover:bg-rose-500 hover:text-white transition-all duration-200 transform hover:scale-110">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                @if ($editingPhotoId === $photo->id)
                                    <div class="space-y-3">
                                        <input 
                                            type="text"
                                            wire:model="editingTitle" 
                                            class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                            placeholder="Photo Title"
                                        >
                                        <textarea 
                                            wire:model="editingDescription" 
                                            class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                            rows="2"
                                            placeholder="Photo Description"
                                        ></textarea>
                                        <div class="flex justify-end space-x-2">
                                            <button wire:click="cancelEdit" class="px-3 py-1.5 text-xs font-medium bg-slate-100 text-slate-600 rounded-lg hover:bg-slate-200 transition-colors">Cancel</button>
                                            <button wire:click="updatePhotoDetails" class="px-3 py-1.5 text-xs font-medium bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors">Update</button>
                                        </div>
                                    </div>
                                @else
                                    <div class="flex items-start justify-between group/info">
                                        <div class="flex-1 min-w-0 pr-2">
                                            <h5 class="text-sm font-bold text-slate-800 truncate" title="{{ $photo->title }}">
                                                {{ $photo->title ?: 'Untitled Photo' }}
                                            </h5>
                                            <p class="text-xs text-slate-500 line-clamp-2 mt-1" title="{{ $photo->description }}">
                                                {{ $photo->description ?: 'No description added.' }}
                                            </p>
                                        </div>
                                        <button wire:click="editPhotoDetails({{ $photo->id }})" class="text-slate-400 hover:text-blue-600 transition-colors opacity-0 group-hover/info:opacity-100" title="Edit Photo Details">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Modal Footer -->
            <div class="flex justify-end items-center px-6 py-4 border-t border-slate-100 bg-slate-50 rounded-b-2xl">
                <button type="button" 
                class="px-6 py-2.5 bg-white border border-slate-300 text-slate-700 font-semibold rounded-xl shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-200 transition-all duration-200" 
                wire:click="closeModal">
                    Close
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
