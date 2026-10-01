@extends('layouts.admin')

@section('title', 'Payment Transactions')

@section('content')
<div class="px-4 py-6 sm:px-0">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-slate-800 dark:text-white">Payment Transactions</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400">Track all gateway events for donations and payments.</p>
    </div>

    @livewire('admin.payment-transactions')
</div>
@endsection