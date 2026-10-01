<div>
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg hover:shadow-lg transition-shadow duration-200">
        <button wire:click="openModal" type="button" class="w-full text-left p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-building text-indigo-600 dark:text-indigo-400 text-3xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Upload Departments</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Import a list of departments</p>
                </div>
            </div>
        </button>
    </div>

    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto overflow-x-hidden bg-gray-900 bg-opacity-50" 
         wire:click.self="$set('showModal', false)">
        <div class="relative p-4 w-full max-w-lg h-full md:h-auto" wire:click.stop>
            <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                <div class="flex justify-between items-start p-4 rounded-t border-b dark:border-gray-600">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Upload Departments</h3>
                    <button wire:click="$set('showModal', false)" type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form wire:submit.prevent="import">
                    <div class="p-6 space-y-4">
                        <div wire:loading wire:target="import" class="w-full">
                            <i class="fas fa-spinner fa-spin text-2xl text-indigo-500"></i> Importing...
                        </div>
                        @if ($importFinished)
                             <div class="text-{{ $errorMessage ? 'red' : 'green' }}-600">
                                {!! $successMessage ?: $errorMessage !!}
                            </div>
                        @else
                            <div>
                                <input wire:model="upload" type="file" id="department_upload" class="block w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 cursor-pointer dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400">
                                <p class="mt-1 text-xs text-gray-500">XLS, XLSX, CSV. Headers: 'department_name', 'faculty_name'</p>
                                @error('upload') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        @endif
                    </div>
                    <div class="flex items-center p-6 space-x-2 rounded-b border-t border-gray-200 dark:border-gray-600">
                         @if (!$importing && !$importFinished)
                            <button type="submit" class="text-white bg-indigo-600 hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center" @if(!$upload) disabled @endif>
                                Import
                            </button>
                        @endif
                        <button wire:click="$set('showModal', false)" type="button" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:ring-gray-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
