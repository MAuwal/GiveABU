@extends('layouts.admin')

@section('title', 'Reports')

@section('content')
<div class="px-4 py-6 sm:px-0">
    <!-- Page Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Reports</h1>
    </div>

    <!-- Main Content -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        @livewire('admin.reports-manager')
    </div>
</div>
@endsection
