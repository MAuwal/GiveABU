<div>
    @if ($showModal)
    <div class="fixed inset-0 bg-gray-600 bg-opacity-75 overflow-y-auto h-full w-full z-50 flex items-center justify-center">
        <div class="relative p-5 border w-full max-w-md shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Add New Role</h3>
                <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 text-2xl font-bold">
                    &times;
                </button>
            </div>
            <form wire:submit.prevent="saveRole">
                <div class="space-y-4">
                    <div>
                        <label for="role_title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Role Title</label>
                        <input type="text" wire:model.defer="role_title" id="role_title" 
                               class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                        @error('role_title') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="permission_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Permission</label>
                        <select wire:model.defer="permission_id" id="permission_id" 
                                class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select Permission</option>
                            @if($permissions)
                                @foreach ($permissions as $permission)
                                    <option value="{{ $permission->id }}">{{ $permission->permission_title }}</option>
                                @endforeach
                            @endif
                        </select>
                        @error('permission_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="flex justify-end gap-2 mt-6">
                    <button type="button" wire:click="closeModal" 
                            class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-300">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
                        <i class="fas fa-save mr-2"></i> Save Role
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div> 