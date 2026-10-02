@extends('layouts.donor')
@section('title', 'My Donations')
@section('content')
<h1 class="text-2xl font-bold mb-2">Donor Dashboard</h1>
<p class="text-slate-500 mb-6">Your contributions to the ABU legacy.</p>
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
    @foreach(['Total donated' => '₦'.number_format($stats['total'], 2), 'All donations' => $stats['count'], 'Completed donations' => $stats['completed'], 'Pending payments' => $stats['pending']] as $label => $value)
    <div class="bg-white shadow-sm rounded-xl border border-slate-200 p-5"><p class="text-sm text-slate-500">{{ $label }}</p><p class="text-2xl font-bold mt-3">{{ $value }}</p></div>
    @endforeach
</div>
<section class="bg-white shadow-sm rounded-xl border border-slate-200 overflow-hidden">
    <h2 class="text-lg font-semibold p-5 border-b">Donation history</h2>
    <div class="overflow-x-auto"><table class="w-full text-left text-sm">
        <thead class="bg-slate-50 text-slate-500"><tr><th class="p-4">Date</th><th class="p-4">Project / fund</th><th class="p-4">Reference</th><th class="p-4">Amount</th><th class="p-4">Status</th><th class="p-4">Transaction</th></tr></thead>
        <tbody>
        @forelse($donations as $donation)
        <tr class="border-t"><td class="p-4 whitespace-nowrap">{{ $donation->created_at?->format('d M Y') }}</td><td class="p-4">{{ $donation->project?->project_title ?? 'GiveABU Fund' }}</td><td class="p-4 break-all">{{ $donation->payment_reference ?? '—' }}</td><td class="p-4 whitespace-nowrap">₦{{ number_format($donation->amount, 2) }}</td><td class="p-4">{{ ucfirst($donation->status) }}</td><td class="p-4"><a class="text-emerald-700 font-semibold whitespace-nowrap" href="{{ route('donor.donation.show', $donation) }}">View details</a></td></tr>
        @empty
        <tr><td colspan="6" class="p-8 text-center text-slate-500">You have no donations yet. <a href="{{ url('/') }}" class="text-emerald-700">Make a donation</a></td></tr>
        @endforelse
        </tbody>
    </table></div>
    <div class="p-5">{{ $donations->links() }}</div>
</section>
@endsection
