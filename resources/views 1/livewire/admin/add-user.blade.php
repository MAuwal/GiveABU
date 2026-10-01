<div>
    @if ($showModal)
    <div class="fixed inset-0 bg-gray-600 bg-opacity-75 overflow-y-auto h-full w-full z-50 flex items-center justify-center">
        <div class="relative p-5 border w-full max-w-lg shadow-lg rounded-md bg-white dark:bg-gray-800">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Add New User</h3>
            <form wire:submit.prevent="saveUser">
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label for="user_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
                        <input type="text" wire:model.defer="name" id="user_name" class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm dark:bg-gray-700 dark:text-white">
                        @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="user_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                        <input type="email" wire:model.defer="email" id="user_email" class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm dark:bg-gray-700 dark:text-white">
                        @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
                        <input type="password" wire:model.defer="password" id="password" class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm dark:bg-gray-700 dark:text-white">
                        @error('password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Confirm Password</label>
                        <input type="password" wire:model.defer="password_confirmation" id="password_confirmation" class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm dark:bg-gray-700 dark:text-white">
                    </div>
                    <div>
                        <label for="role_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Role</label>
                        <select wire:model.defer="role_id" id="role_id" class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm dark:bg-gray-700 dark:text-white">
                            <option value="">Select Role</option>
                            @if($roles)
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}">{{ $role->role_title }}</option>
                                @endforeach
                            @endif
                        </select>
                        @error('role_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="flex justify-end mt-4">
                    <button type="button" class="px-4 py-2 bg-gray-500 text-white rounded-md mr-2" wire:click="closeModal">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md">Create User</button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div> 