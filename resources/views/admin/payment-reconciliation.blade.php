@extends('layouts.admin')
@section('title', 'Payment reconciliation')
@section('content')
<h1 class="text-2xl font-bold mb-6">Payment reconciliation</h1>
@if(session('success'))<p role="status" class="mb-4 text-emerald-700">{{ session('success') }}</p>@endif
<div class="grid md:grid-cols-2 gap-4 mb-6">
@foreach($queueSizes as $queue => $size)<div class="bg-white rounded-xl p-5 border">{{ ucfirst($queue) }} queue: <strong>{{ $size }}</strong></div>@endforeach
</div>
<p class="mb-6 text-slate-500">Queue sizes use the configured queue connection (Redis in production). Failed jobs and uncertain receipt claims require delivery reconciliation; retrying does not clear delivery claims.</p>
<section class="bg-white rounded-xl p-5 border mb-6 overflow-x-auto">
<h2 class="font-bold mb-4">Pending payment confirmations</h2>
<table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
    <thead class="bg-slate-50 dark:bg-slate-700">
        <tr>
            @foreach(['Date', 'Gateway', 'Event', 'Donor / Project', 'Reference', 'Amount', 'Status', 'Actions'] as $heading)
            <th class="px-4 py-3 {{ $heading === 'Actions' ? 'text-right' : 'text-left' }} text-xs font-medium text-slate-500 dark:text-slate-300 uppercase tracking-wider">{{ $heading }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
        @forelse($pending as $payment)
        @php
            $event = $payment->transactions->first();
            $gateways = $payment->transactions->pluck('payment_gateway')->filter()->unique();
            $gateway = $gateways->count() === 1 ? $gateways->first() : null;
        @endphp
        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700 transition">
            <td class="px-4 py-4 whitespace-nowrap text-sm text-slate-600 dark:text-slate-300">{{ $payment->created_at?->format('M d, Y H:i') ?? 'N/A' }}</td>
            <td class="px-4 py-4 whitespace-nowrap text-sm">
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium {{ $gateway === 'squad' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : ($gateway === 'interswitch' ? 'bg-cyan-100 text-cyan-800 dark:bg-cyan-900 dark:text-cyan-200' : 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200') }}">{{ $gateway ? ucfirst($gateway) : ($gateways->isEmpty() ? 'Unbound' : 'Multiple gateways') }}</span>
            </td>
            <td class="px-4 py-4 text-sm text-slate-600 dark:text-slate-300">{{ $event ? ucfirst(str_replace(['.', '_'], ' ', $event->event_type)) : 'No gateway event' }}</td>
            <td class="px-4 py-4 whitespace-nowrap text-sm text-slate-600 dark:text-slate-300">
                <div>{{ $payment->donor?->full_name ?? 'N/A' }}</div>
                <div class="text-xs text-slate-400 dark:text-slate-500">{{ $payment->project?->project_title ?? 'General' }}</div>
            </td>
            <td class="px-4 py-4 text-xs font-mono text-slate-600 dark:text-slate-300">{{ $payment->payment_reference }}</td>
            <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-slate-700 dark:text-slate-200">₦{{ number_format($payment->amount, 2) }}</td>
            <td class="px-4 py-4 whitespace-nowrap text-sm"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">{{ ucfirst($payment->status) }}</span></td>
            <td class="px-4 py-4 whitespace-nowrap text-right text-sm font-medium">
                <form method="POST" action="{{ route('admin.reconciliation.verify', $payment->id) }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 {{ $gateway === 'interswitch' ? 'text-cyan-700 hover:text-cyan-800 hover:bg-cyan-50 dark:hover:bg-cyan-900 dark:text-cyan-300' : 'text-blue-700 hover:text-blue-800 hover:bg-blue-50 dark:hover:bg-blue-900 dark:text-blue-300' }} px-3 py-1 rounded-lg transition mr-1"><i class="fas fa-sync-alt text-xs" aria-hidden="true"></i> Verify</button>
                </form>
            </td>
        </tr>
        @empty
        <tr><td colspan="8" class="px-4 py-10 text-center text-sm text-slate-500 dark:text-slate-400">No pending payments.</td></tr>
        @endforelse
    </tbody>
</table>
<div class="mt-4">
{{ $pending->withQueryString()->links() }}
</div>
</section>
<section class="bg-white rounded-xl p-5 border mb-6 overflow-x-auto">
<h2 class="font-bold mb-4">Receipt outbox</h2>
@forelse($receipts as $receipt)
<div class="py-3 border-t flex gap-4 justify-between"><span>Donation #{{ $receipt->donation_id }} · {{ $receipt->gateway }} · {{ $receipt->status }} · Last published: {{ $receipt->published_at ?? 'Not published' }}</span>
@if($receipt->status === 'pending')<form method="POST" action="{{ route('admin.reconciliation.publish', $receipt->id) }}">@csrf<button class="text-emerald-700">Publish pending receipt</button></form>@else<span>Inspect delivery claims and provider reports before recovery.</span>@endif</div>
@empty<p>No pending or failed receipts.</p>@endforelse
{{ $receipts->withQueryString()->links() }}
</section>
<section class="bg-white rounded-xl p-5 border overflow-x-auto">
<h2 class="font-bold mb-4">Failed queue jobs</h2>
@forelse($failed as $job)<p class="py-3 border-t">{{ $job->uuid }} · {{ $job->connection }} / {{ $job->queue }} · {{ $job->failed_at }}</p>@empty<p>No failed jobs.</p>@endforelse
{{ $failed->withQueryString()->links() }}
</section>
@endsection
