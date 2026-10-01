<div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-slate-900">Send SMS Notification</h2>
            <p class="text-sm text-slate-500 mt-1">Use ABU as the fixed sender ID and send a one-off SMS to a recipient.</p>
        </div>
        <div class="text-right text-xs text-slate-400">
            <span class="block">Sender ID</span>
            <span class="font-semibold text-slate-800">ABU</span>
        </div>
    </div>

    @if ($statusMessage)
        <div class="mb-4 rounded-lg px-4 py-3 text-sm {{ $statusType === 'success' ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-rose-50 text-rose-700 border border-rose-100' }}">
            {{ $statusMessage }}
        </div>
    @endif

    <form wire:submit.prevent="sendSms" class="space-y-6">
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Receiver Phone Number(s)</label>
            <input wire:model.defer="receiver" type="text" placeholder="e.g. +2348012345678, 08012345678, 2348012345678"
                   class="block w-full rounded-xl border border-slate-300 px-4 py-3 text-slate-900 focus:border-indigo-500 focus:ring-indigo-500 focus:outline-none"
                   autocomplete="tel">
            @error('receiver') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
            <p class="mt-2 text-xs text-slate-500">Enter one or more phone numbers separated by commas, semicolons, spaces, or new lines. Country code is automatically normalized to Nigeria (234) if omitted.</p>
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Message</label>
            <textarea wire:model.defer="message" rows="6" maxlength="918"
                      class="block w-full rounded-xl border border-slate-300 px-4 py-3 text-slate-900 focus:border-indigo-500 focus:ring-indigo-500 focus:outline-none"></textarea>
            @error('message') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
            <p class="mt-2 text-xs text-slate-500">Maximum 918 characters. Messages are sent with sender ID <strong>ABU</strong>.</p>
        </div>

        <div class="flex items-center justify-between gap-4">
            <a href="{{ route('admin.notifications.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                <i class="fas fa-arrow-left mr-2"></i> Back to Notifications
            </a>
            <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 disabled:opacity-60" {{ $sending ? 'disabled' : '' }}>
                @if($sending)
                    <i class="fas fa-spinner fa-spin mr-2"></i> Sending...
                @else
                    <i class="fas fa-paper-plane mr-2"></i> Send SMS
                @endif
            </button>
        </div>
    </form>
</div>
