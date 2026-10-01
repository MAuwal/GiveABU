<div wire:init="initComponent">
    
    {{-- Flash Messages --}}
    @if (session()->has('message'))
    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
        <span class="block sm:inline">{{ session('message') }}</span>
    </div>
    @endif
    
    @if (session()->has('error'))
    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
        <span class="block sm:inline">{{ session('error') }}</span>
    </div>
    @endif
    
    <div>
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white">Departments & Visions</h3>
            <div class="flex items-center space-x-4">
                <input wire:model="search" type="text" placeholder="Search departments..." class="w-64 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                <button wire:click="openModal" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <i class="fas fa-plus mr-2"></i>
                    Add Department
                </button>
            </div>
        </div>
        
        {{-- Add Department Modal --}}   
        @if($showModal)
        <div class="fixed inset-0 z-50 bg-gray-900 bg-opacity-50">
            <div class="flex items-center justify-center min-h-screen">
                <div class="bg-white rounded-lg shadow-lg max-w-md w-full m-4">
                    <div class="p-4 border-b">
                        <h3 class="text-lg font-semibold">Add New Department</h3>
                        <button wire:click="closeModal" class="float-right -mt-6 text-gray-500 hover:text-gray-700">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
    
                    <div class="p-4">
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-2">Faculty *</label>
                            <select wire:model="faculty_id" class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                                <option value="">Select Faculty</option>
                                @foreach($faculties as $faculty)
                                    <option value="{{ $faculty->id }}">{{ $faculty->current_name }}</option>
                                @endforeach
                            </select>
                            @error('faculty_id') 
                                <span class="text-red-500 text-xs">{{ $message }}</span> 
                            @enderror
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-2">Department Name *</label>
                            <input wire:model="current_name" type="text" class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Enter department name" required>
                            @error('current_name') 
                                <span class="text-red-500 text-xs">{{ $message }}</span> 
                            @enderror
                        </div>
                        
                        <div class="flex justify-end space-x-2">
                            <button wire:click="closeModal" class="px-4 py-2 text-gray-600 border rounded hover:bg-gray-50">Cancel</button>
                            <button wire:click="saveDepartment" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700" wire:loading.attr="disabled">
                                <span wire:loading wire:target="saveDepartment" class="inline-block animate-spin mr-2">
                                    <i class="fas fa-spinner"></i>
                                </span>
                                <span wire:loading.remove wire:target="saveDepartment">
                                    <i class="fas fa-save mr-2"></i>
                                </span>
                                Save Department
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="overflow-x-auto bg-white dark:bg-gray-800 shadow-md rounded-lg">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">
                        Department Name
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">
                        Faculty
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">
                        Date Created
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">
                        Actions
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">
                        Visions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                @forelse($departments as $department)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ $department->current_name }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $department->faculty->current_name ?? 'N/A' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ $department->created_at->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button wire:click="$dispatch('showAddDepartmentVisionModal', { departmentId: {{ $department->id }} })" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-200">
                                Add Vision
                            </button>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex flex-wrap gap-1">
                                @forelse($department->visions as $vision)
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                        {{ $vision->name }}
                                    </span>
                                @empty
                                    <span class="text-xs text-gray-500 dark:text-gray-400">No visions</span>
                                @endforelse
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                            No departments found. Try adjusting your search.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $departments->links() }}
    </div>

    @livewire('admin.add-department-vision')
</div>
