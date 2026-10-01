<div
    x-data="{
        show: false,
        init() {
            Livewire.on('open-profile-modal', () => { this.show = true })
        }
    }"
    x-show="show"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
    style="display: none;"
>
    <div class="bg-white rounded-lg shadow-lg w-full max-w-lg p-0 relative">
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b">
            <h3 class="text-xl font-semibold">Edit Profile</h3>
            <button @click="show = false" class="text-gray-400 hover:text-gray-600 text-2xl font-bold">&times;</button>
        </div>
        <!-- Form -->
        <form wire:submit.prevent="updateProfile" enctype="multipart/form-data" class="px-6 py-4 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex flex-col gap-2">
                    <label for="name" class="text-sm font-medium">Name</label>
                    <input type="text" id="name" wire:model.defer="name" class="form-input rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" />
                    @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="flex flex-col gap-2">
                    <label for="email" class="text-sm font-medium">Email</label>
                    <input type="email" id="email" wire:model.defer="email" class="form-input rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" />
                    @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="flex flex-col gap-2">
                    <label for="password" class="text-sm font-medium">Password <span class="text-xs text-gray-400">(leave blank to keep current)</span></label>
                    <input type="password" id="password" wire:model.defer="password" class="form-input rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" />
                    @error('password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="flex flex-col gap-2">
                    <label for="phone" class="text-sm font-medium">Phone</label>
                    <input type="text" id="phone" wire:model.defer="phone" class="form-input rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" />
                    @error('phone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="flex flex-col gap-2">
                    <label for="donor_type" class="text-sm font-medium">Donor Type</label>
                    <input type="text" id="donor_type" wire:model.defer="donor_type" class="form-input rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" />
                    @error('donor_type') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="flex flex-col gap-2">
                    <label for="role_id" class="text-sm font-medium">Role</label>
                    <input type="number" id="role_id" wire:model.defer="role_id" class="form-input rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" />
                    @error('role_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>
            <!-- Profile Photo Upload -->
            <div class="flex w-full max-w-xl text-center flex-col gap-1">
                <span class="w-fit pl-0.5 text-sm text-on-surface dark:text-on-surface-dark">Profile Picture</span>
                <div class="flex w-full flex-col items-center justify-center gap-2 rounded-radius border border-dashed border-outline p-8 text-on-surface dark:border-outline-dark dark:text-on-surface-dark">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true" fill="currentColor" class="w-12 h-12 opacity-75">
                        <path fill-rule="evenodd" d="M10.5 3.75a6 6 0 0 0-5.98 6.496A5.25 5.25 0 0 0 6.75 20.25H18a4.5 4.5 0 0 0 2.206-8.423 3.75 3.75 0 0 0-4.133-4.303A6.001 6.001 0 0 0 10.5 3.75Zm2.03 5.47a.75.75 0 0 0-1.06 0l-3 3a.75.75 0 1 0 1.06 1.06l1.72-1.72v4.94a.75.75 0 0 0 1.5 0v-4.94l1.72 1.72a.75.75 0 1 0 1.06-1.06l-3-3Z" clip-rule="evenodd"/>
                    </svg>
                    <div class="group">
                        <label for="fileInputDragDrop" class="font-medium text-primary group-focus-within:underline dark:text-primary-dark cursor-pointer">
                            <input id="fileInputDragDrop" type="file" class="sr-only" wire:model="profile_photo" aria-describedby="validFileFormats" />
                            Browse
                        </label>
                         or drag and drop here
                    </div>
                    <small id="validFileFormats">PNG, JPG, WebP - Max 5MB</small>
                    @if ($profile_photo)
                        <div class="mt-2">
                            <img src="{{ $profile_photo->temporaryUrl() }}" alt="Preview" class="mx-auto rounded-full h-20 w-20 object-cover" />
                        </div>
                    @elseif ($current_photo)
                        <div class="mt-2">
                            <img src="{{ asset('storage/' . $current_photo) }}" alt="Profile Photo" class="mx-auto rounded-full h-20 w-20 object-cover" />
                        </div>
                    @endif
                    @error('profile_photo') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>
        </form>
        <!-- Footer -->
        <div class="flex justify-end gap-2 px-6 py-4 border-t">
            <button type="button" @click="show = false" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Close</button>
            <button type="button" @click="() => { $el.closest('form').requestSubmit() }" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Save</button>
        </div>
    </div>
</div>
