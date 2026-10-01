@extends('layouts.admin')

@section('title', 'Manage Donors')

@section('content')
<div class="px-4 py-6 sm:px-0">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-slate-800">Manage Donors</h2>
    </div>

    <!-- Alumni Upload Component -->
    <div class="mb-8">
        @livewire('admin.alumni-upload')
    </div>

    <!-- Donors Table Section -->
    <div class="bg-white shadow-sm rounded-xl overflow-hidden border border-slate-200">
        <div class="p-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                <h3 class="text-xl font-bold text-slate-800">Alumni List</h3>
                
                <!-- Add Donor Button -->
                <button onclick="Livewire.dispatch('open-add-donor-modal')" 
                        class="w-full md:w-auto px-4 py-2 bg-blue-600 text-white font-semibold rounded-lg shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                    <i class="fas fa-plus mr-2"></i>Add Alumnus
                </button>
            </div>

            @livewire('admin.donors-table')
        </div>
    </div>
</div>

@livewire('admin.add-donor')
@endsection
