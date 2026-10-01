<div>
    @if ($showModal)
    <div class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form wire:submit.prevent="save">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    Add Vision for {{ $departmentName }}
                                </h3>
                                <div class="mt-2">
                                    <div class="mb-4">
                                        <label for="faculty_vision_id" class="block text-gray-700 text-sm font-bold mb-2">Faculty Vision:</label>
                                        <select wire:model="faculty_vision_id" id="faculty_vision_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                            <option value="">Select a Faculty Vision</option>
                                            @foreach($facultyVisions as $vision)
                                                <option value="{{ $vision->id }}">{{ $vision->name }} ({{ $vision->start_year }} - {{ $vision->end_year }})</option>
                                            @endforeach
                                        </select>
                                        @error('faculty_vision_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="mb-4">
                                        <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Department Vision Name:</label>
                                        <input type="text" wire:model="name" id="name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                        @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="mb-4">
                                        <label for="start_year" class="block text-gray-700 text-sm font-bold mb-2">Start Year:</label>
                                        <input type="date" wire:model="start_year" id="start_year" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                        @error('start_year') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="mb-4">
                                        <label for="end_year" class="block text-gray-700 text-sm font-bold mb-2">End Year:</label>
                                        <input type="date" wire:model="end_year" id="end_year" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                        @error('end_year') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Save
                        </button>
                        <button type="button" wire:click="close" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
