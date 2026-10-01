<div>
    @if ($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm overflow-y-auto" id="add-project-modal">
        <div class="relative w-full max-w-lg mx-4 my-6 bg-white rounded-2xl shadow-2xl border border-slate-100 flex flex-col max-h-[90vh]">
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 rounded-t-2xl flex justify-between items-center">
                <h3 class="text-lg font-bold text-slate-800">
                    {{ $editingProjectId ? 'Edit Project' : 'Add New Project' }}
                </h3>
                <button wire:click="closeModal" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6 overflow-y-auto">
                <form wire:submit.prevent="saveProject">
                    @if (session()->has('message'))
                        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg mb-4 flex items-center" role="alert">
                            <i class="fas fa-check-circle mr-2"></i>
                            <span class="block sm:inline">{{ session('message') }}</span>
                        </div>
                    @endif
                    @if (session()->has('error'))
                        <div class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-lg mb-4 flex items-center" role="alert">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            <span class="block sm:inline">{{ session('error') }}</span>
                        </div>
                    @endif

                    <div class="mb-5">
                        <label for="projectTitle" class="block text-slate-700 text-sm font-bold mb-2">Project Title</label>
                        <input type="text" wire:model.defer="project_title" id="projectTitle" 
                               class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-lg text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 placeholder-slate-400" 
                               placeholder="e.g., Student Scholarship Program">
                        @error('project_title') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-5">
                        <label for="target" class="block text-slate-700 text-sm font-bold mb-2">Target Amount</label>
                        <div class="relative rounded-lg shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-slate-500 sm:text-sm font-bold">₦</span>
                            </div>
                            <input type="number" step="0.01" wire:model.defer="target" id="target" 
                                   class="w-full pl-8 pr-12 py-2.5 bg-white border border-slate-300 rounded-lg text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 placeholder-slate-400" 
                                   placeholder="0.00">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <span class="text-slate-400 sm:text-xs">NGN</span>
                            </div>
                        </div>
                        @error('target') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-5">
                        <label for="projectDescription" class="block text-slate-700 text-sm font-bold mb-2">Description</label>
                        <textarea id="projectDescription" wire:model.defer="project_description" rows="4" 
                                  class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-lg text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 placeholder-slate-400" 
                                  placeholder="Describe the project details..."></textarea>
                        @error('project_description') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-5">
                        <label for="iconImage" class="block text-slate-700 text-sm font-bold mb-2">Icon Image <span class="text-slate-400 font-normal">(Optional)</span></label>
                        <input type="file" wire:model="icon_image" id="iconImage"
                               class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition-all duration-200"
                               accept="image/*">
                        <div wire:loading wire:target="icon_image" class="flex items-center gap-2 mt-2 text-blue-600 text-xs">
                            <svg class="animate-spin h-3 w-3" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                            </svg>
                            Uploading image, please wait...
                        </div>
                        @error('icon_image') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror

                        {{-- Image preview --}}
                        @if ($icon_image)
                            <div class="mt-3 flex items-center gap-3">
                                <img src="{{ $icon_image->temporaryUrl() }}" alt="Preview" class="h-20 w-20 object-cover rounded-lg border border-slate-200 shadow-sm">
                                <span class="text-xs text-slate-500">New image selected</span>
                            </div>
                        @elseif ($existing_icon_image)
                            <div class="mt-3 flex items-center gap-3">
                                <img src="{{ asset('storage/' . $existing_icon_image) }}" alt="Current Icon" class="h-20 w-20 object-cover rounded-lg border border-slate-200 shadow-sm">
                                <span class="text-xs text-slate-500">Current icon (upload a new one to replace)</span>
                            </div>
                        @endif
                    </div>

                    <div class="mb-6">
                        <label for="status" class="block text-slate-700 text-sm font-bold mb-2">Status</label>
                        <select wire:model.defer="status" id="status" 
                                class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-lg text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 cursor-pointer">
                            <option value="active">Active</option>
                            <option value="closed">Closed</option>
                        </select>
                        @error('status') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex flex-col-reverse sm:flex-row gap-3 pt-2">
                        <button type="button" wire:click="closeModal" 
                                class="w-full sm:w-auto px-6 py-2.5 bg-white border border-slate-300 text-slate-700 font-semibold rounded-xl shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-200 transition-all duration-200">
                            Cancel
                        </button>
                        <button type="submit" id="save-project-btn"
                                wire:loading.attr="disabled"
                                wire:loading.class="opacity-60 cursor-not-allowed"
                                wire:target="icon_image,saveProject"
                                class="w-full sm:w-auto flex-1 px-6 py-2.5 bg-blue-600 text-white font-semibold rounded-xl shadow-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200">
                            <span wire:loading.remove wire:target="icon_image,saveProject">
                                {{ $editingProjectId ? 'Update Project' : 'Create Project' }}
                            </span>
                            <span wire:loading wire:target="icon_image,saveProject" class="flex items-center justify-center gap-2">
                                <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                                </svg>
                                {{ $editingProjectId ? 'Saving...' : 'Creating...' }}
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>