<div>
    @if ($showModal)
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center" id="add-department-modal">
        <div class="relative p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Add Department to {{ $faculty->current_name ?? '' }}</h3>
                <div class="mt-2 px-7 py-3">
                    <form wire:submit.prevent="saveDepartment">
                        @if ($successMessage)
                            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                                <span class="block sm:inline">{{ $successMessage }}</span>
                            </div>
                        @endif
                        @if ($errorMessage)
                            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                                <span class="block sm:inline">{{ $errorMessage }}</span>
                            </div>
                        @endif

                        <div class="mb-4">
                            <label for="departmentName" class="block text-gray-700 text-sm font-bold mb-2 text-left">Department Name:</label>
                            <input type="text" wire:model.defer="departmentName" id="departmentName" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="e.g., Computer Science">
                            @error('departmentName') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="started_at" class="block text-gray-700 text-sm font-bold mb-2 text-left">
                                    Start Year <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    wire:model.defer="started_at" 
                                    type="number" 
                                    id="started_at" 
                                    min="1900"
                                    max="2100"
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                    placeholder="e.g., 1980"
                                    required
                                >
                                @error('started_at') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="ended_at" class="block text-gray-700 text-sm font-bold mb-2 text-left">
                                    End Year <span class="text-gray-400 text-xs">(Optional)</span>
                                </label>
                                <input 
                                    wire:model.defer="ended_at" 
                                    type="number" 
                                    id="ended_at" 
                                    min="1900"
                                    max="2100"
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                    placeholder="Leave empty if active"
                                >
                                @error('ended_at') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="items-center px-4 py-3">
                            <button id="save-department-btn" class="px-4 py-2 bg-blue-500 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-300">
                                Save Department
                            </button>
                        </div>
                    </form>
                </div>
                <div class="items-center px-4 py-3">
                    <button wire:click="$set('showModal', false)" class="px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-300">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
