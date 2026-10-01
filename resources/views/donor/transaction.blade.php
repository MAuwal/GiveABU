@extends('layouts.donor')
@section('title', 'Donation Details')
@section('content')
<a href="{{ route('donor.donations') }}" class="text-emerald-700">← All donations</a>
<h1 class="text-2xl font-bold my-6">Donation details</h1>
<div class="bg-white shadow-sm rounded-xl border border-slate-200 p-6 mb-8">
    <dl class="grid sm:grid-cols-2 gap-5">
        @foreach(['Payment reference' => $donation->payment_reference ?? '—', 'Amount' => '₦'.number_format($donation->amount, 2), 'Status' => ucfirst($donation->status), 'Project / fund' => $donation->project?->project_title ?? 'GiveABU Fund', 'Donation type' => $donation->endowment === 'yes' ? 'Endowment' : 'Project donation', 'Created' => $donation->created_at?->format('d M Y H:i'), 'Paid' => $donation->paid_at?->format('d M Y H:i') ?? 'Not confirmed', 'Verified' => $donation->verified_at?->format('d M Y H:i') ?? 'Not confirmed'] as $label => $value)
        <div><dt class="text-sm text-slate-500">{{ $label }}</dt><dd class="mt-1 font-semibold break-all">{{ $value }}</dd></div>
        @endforeach
    </dl>
</div>
<section class="bg-white shadow-sm rounded-xl border border-slate-200 p-6">
    <h2 class="text-lg font-semibold mb-4">Payment transactions</h2>
    @forelse($transactions as $transaction)
    <dl class="grid sm:grid-cols-2 gap-4 py-5 border-t">
        @foreach(['Gateway' => ucfirst($transaction->payment_gateway), 'Gateway reference' => $transaction->gateway_reference ?? '—', 'Amount' => number_format($transaction->amount, 2).' '.$transaction->currency, 'Status' => ucfirst($transaction->status), 'Channel' => $transaction->channel ?? '—', 'Fee' => $transaction->fee !== null ? number_format($transaction->fee, 2).' '.$transaction->currency : '—', 'Recorded' => $transaction->created_at?->format('d M Y H:i')] as $label => $value)
        <div><dt class="text-sm text-slate-500">{{ $label }}</dt><dd class="break-all">{{ $value }}</dd></div>
        @endforeach
    </dl>
    @empty
    <p class="text-slate-500">No payment transaction details have been recorded for this donation.</p>
    @endforelse
    {{ $transactions->links() }}
</section>
@endsection
