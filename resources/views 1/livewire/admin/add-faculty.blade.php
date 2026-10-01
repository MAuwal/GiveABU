<div>
    {{-- Debug Info --}}
    @if(config('app.debug'))
        <div class="text-xs text-gray-500 mb-2">
            Debug: showModal = {{ $showModal ? 'true' : 'false' }}
        </div>
    @endif
    
    {{-- No button here - this component is triggered by events from other components --}}

    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto overflow-x-hidden bg-gray-900 bg-opacity-50" 
         wire:click.self="closeModal">
        <div class="relative p-4 w-full max-w-md h-full md:h-auto" wire:click.stop>
            <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                <!-- Modal header -->
                <div class="flex justify-between items-start p-4 rounded-t border-b dark:border-gray-600">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                        Add New Faculty
                    </h3>
                    <button wire:click="closeModal" type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <!-- Modal body -->
                <form wire:submit.prevent="save">
                    <div class="p-6 space-y-4">
                        <div>
                            <label for="current_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Faculty Name <span class="text-red-500">*</span>
                            </label>
                            <input 
                                wire:model="current_name" 
                                type="text" 
                                id="current_name" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-600 dark:border-gray-500 dark:text-white dark:placeholder-gray-400"
                                placeholder="Enter faculty name"
                                required
                            >
                            @error('current_name') 
                                <span class="text-red-500 text-xs mt-1">{{ $message }}</span> 
                            @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="started_at" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Start Year <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    wire:model="started_at" 
                                    type="number" 
                                    id="started_at" 
                                    min="1900"
                                    max="2100"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-600 dark:border-gray-500 dark:text-white dark:placeholder-gray-400"
                                    placeholder="e.g., 1975"
                                    required
                                >
                                @error('started_at') 
                                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span> 
                                @enderror
                            </div>

                            <div>
                                <label for="ended_at" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    End Year <span class="text-gray-400 text-xs">(Optional)</span>
                                </label>
                                <input 
                                    wire:model="ended_at" 
                                    type="number" 
                                    id="ended_at" 
                                    min="1900"
                                    max="2100"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-600 dark:border-gray-500 dark:text-white dark:placeholder-gray-400"
                                    placeholder="Leave empty if active"
                                >
                                @error('ended_at') 
                                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span> 
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <!-- Modal footer -->
                    <div class="flex items-center p-6 space-x-2 rounded-b border-t border-gray-200 dark:border-gray-600">
                        <button 
                            type="submit" 
                            class="text-white bg-indigo-600 hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-indigo-600 dark:hover:bg-indigo-700 dark:focus:ring-indigo-800"
                            wire:loading.attr="disabled"
                        >
                            <span wire:loading wire:target="save" class="inline-block animate-spin mr-2">
                                <i class="fas fa-spinner"></i>
                            </span>
                            <span wire:loading.remove wire:target="save">
                                <i class="fas fa-save mr-2"></i>
                            </span>
                            Save Faculty
                        </button>
                        
                        <button 
                            wire:click="closeModal" 
                            type="button" 
                            class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:ring-gray-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600"
                        >
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
