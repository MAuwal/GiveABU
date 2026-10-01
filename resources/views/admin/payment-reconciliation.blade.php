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
@forelse($pending as $payment)
<div class="py-3 border-t flex gap-4 justify-between"><span>{{ $payment->payment_reference }} · ₦{{ number_format($payment->amount, 2) }} · {{ $payment->created_at }}</span><form method="POST" action="{{ route('admin.reconciliation.verify', $payment->id) }}">@csrf<button class="text-emerald-700">Check gateway status</button></form></div>
@empty<p>No pending payments.</p>@endforelse
{{ $pending->withQueryString()->links() }}
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
