@extends('layouts.admin')

@section('title', 'Faculties Management')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">Manage Faculties</h2>
        @livewire('admin.faculty-upload')
    </div>
    @livewire('admin.faculties-table')
</div>
@endsection 