@extends('layouts.admin')

@section('title', 'Notification Logs')

@section('content')
    @php $activeTab = request()->get('tab', 'email'); @endphp

    <div class="mb-6 flex flex-wrap items-center gap-3">
        <a href="{{ route('admin.notifications.logs', ['tab' => 'email']) }}" class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium {{ $activeTab === 'email' ? 'bg-indigo-600 text-white' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50' }}">
            Email Logs
        </a>
        <a href="{{ route('admin.notifications.logs', ['tab' => 'sms']) }}" class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium {{ $activeTab === 'sms' ? 'bg-indigo-600 text-white' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50' }}">
            SMS Logs
        </a>
    </div>

    @if ($activeTab === 'sms')
        @livewire('admin.notifications.sms-logs')
    @else
        @livewire('admin.notifications.email-logs')
    @endif
@endsection
