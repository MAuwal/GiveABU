<div>
    @if ($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="relative w-full max-w-3xl h-auto max-h-[90vh] flex flex-col">
            <div class="relative bg-white rounded-2xl shadow-2xl border border-slate-100 flex flex-col max-h-full">
                <!-- Modal Header -->
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-slate-50 rounded-t-2xl">
                    <h3 class="text-xl font-bold text-slate-800">Alumnus/Donor Insert</h3>
                    <button type="button" class="text-slate-400 hover:text-slate-600 transition-colors" wire:click="closeModal">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="overflow-y-auto flex-1">
                    <form wire:submit.prevent="saveDonor" class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                            <!-- Surname -->
                            <div class="col-span-1">
                                <label for="surname" class="block text-sm font-bold text-slate-700 mb-1">Surname</label>
                                <input type="text" wire:model.defer="surname" id="surname" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                                @error('surname') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- Name -->
                            <div class="col-span-1">
                                <label for="name" class="block text-sm font-bold text-slate-700 mb-1">First Name</label>
                                <input type="text" wire:model.defer="name" id="name" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                                @error('name') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- Other Name -->
                            <div class="col-span-1">
                                <label for="other_name" class="block text-sm font-bold text-slate-700 mb-1">Other Name <span class="font-normal text-slate-400">(Optional)</span></label>
                                <input type="text" wire:model.defer="other_name" id="other_name" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                                @error('other_name') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- Gender -->
                            <div class="col-span-1">
                                <label for="gender" class="block text-sm font-bold text-slate-700 mb-1">Gender <span class="font-normal text-slate-400">(Optional)</span></label>
                                <select wire:model.defer="gender" id="gender" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                                    <option value="">Select Gender</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                </select>
                                @error('gender') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            
                            <!-- Reg Number -->
                            <div class="col-span-1">
                                <label for="reg_number" class="block text-sm font-bold text-slate-700 mb-1">Registration Number</label>
                                <input type="text" wire:model.defer="reg_number" id="reg_number" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                                @error('reg_number') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- Email -->
                            <div class="col-span-1">
                                <label for="email" class="block text-sm font-bold text-slate-700 mb-1">Email Address</label>
                                <input type="email" wire:model.defer="email" id="email" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                                @error('email') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- Phone -->
                            <div class="col-span-1">
                                <label for="phone" class="block text-sm font-bold text-slate-700 mb-1">Phone</label>
                                <input type="tel" wire:model.defer="phone" id="phone" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                                @error('phone') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- Address -->
                            <div class="col-span-2">
                                <label for="address" class="block text-sm font-bold text-slate-700 mb-1">Address</label>
                                <textarea wire:model.defer="address" id="address" rows="1" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"></textarea>
                                @error('address') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            
                            <!-- Nationality -->
                            <div class="col-span-1">
                                <label for="nationality" class="block text-sm font-bold text-slate-700 mb-1">Nationality</label>
                                <input type="text" wire:model.defer="nationality" id="nationality" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                                @error('nationality') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- State -->
                            <div class="col-span-1">
                                <label for="state" class="block text-sm font-bold text-slate-700 mb-1">State</label>
                                <input type="text" wire:model.defer="state" id="state" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                                @error('state') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- LGA -->
                            <div class="col-span-1">
                                <label for="lga" class="block text-sm font-bold text-slate-700 mb-1">LGA</label>
                                <input type="text" wire:model.defer="lga" id="lga" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                                @error('lga') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- Entry Year -->
                            <div class="col-span-1">
                                <label for="entry_year" class="block text-sm font-bold text-slate-700 mb-1">Entry Year</label>
                                <input type="number" wire:model.defer="entry_year" id="entry_year" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200" placeholder="YYYY">
                                @error('entry_year') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- Graduation Year -->
                            <div class="col-span-1">
                                <label for="graduation_year" class="block text-sm font-bold text-slate-700 mb-1">Graduation Year</label>
                                <input type="number" wire:model.defer="graduation_year" id="graduation_year" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200" placeholder="YYYY">
                                @error('graduation_year') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            
                            <!-- Faculty -->
                            <div class="col-span-3 md:col-span-1">
                                <label for="faculty_id" class="block text-sm font-bold text-slate-700 mb-1">Faculty</label>
                                <select wire:model.live="faculty_id" id="faculty_id" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                                    <option value="">Select Faculty</option>
                                    @foreach ($faculties as $faculty)
                                        <option value="{{ $faculty->id }}">{{ $faculty->current_name }}</option>
                                    @endforeach
                                </select>
                                @error('faculty_id') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- Department -->
                            <div class="col-span-3 md:col-span-2">
                                <label for="department_id" class="block text-sm font-bold text-slate-700 mb-1">Department</label>
                                <select wire:model.defer="department_id" id="department_id" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200" @if(empty($departments)) disabled @endif>
                                    <option value="">Select Department</option>
                                    @foreach ($departments as $department)
                                        <option value="{{ $department->id }}">{{ $department->current_name }}</option>
                                    @endforeach
                                </select>
                                @error('department_id') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Modal Footer -->
                        <div class="flex justify-end items-center pt-4 border-t border-slate-100 mt-6 space-x-3">
                            <button type="button" class="px-5 py-2.5 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-200 transition-colors" wire:click="closeModal">
                                Cancel
                            </button>
                            <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm transition-colors disabled:opacity-50" wire:loading.attr="disabled">
                                <span wire:loading wire:target="saveDonor" class="inline-block animate-spin mr-2"><i class="fas fa-spinner"></i></span>
                                Save Donor
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>