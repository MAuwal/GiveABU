@extends('layouts.admin')

@section('title', 'Donations Overview')

@section('content')

    <div class="mb-8">
        <h2 class="text-2xl font-bold text-slate-800">Donations Overview</h2>
        <p class="text-sm text-slate-500 mt-1">View all donations with project details, targets, and raised amounts</p>
    </div>

    @livewire('admin.donations-table')

@endsection

