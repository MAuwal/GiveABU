<div>
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <h3 class="text-xl font-bold text-slate-800">Alumni Records</h3>
        <div class="w-full md:w-1/3">
            <div class="relative">
                <input wire:model.live="search" type="text" placeholder="Search donors..." class="w-full pl-10 pr-4 py-2 bg-white border border-slate-300 rounded-lg text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-slate-400"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="overflow-x-auto bg-white rounded-lg border border-slate-200 shadow-sm">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">
                        Name
                    </th>
                    <th scope="col" class="hidden md:table-cell px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">
                        Gender
                    </th>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">
                        Contact
                    </th>
                    <th scope="col" class="hidden lg:table-cell px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">
                        Location
                    </th>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">
                        Donations
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-slate-200">
                @forelse($donors as $donor)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-bold text-slate-800">
                                {{ $donor->surname }} {{ $donor->name }}
                                @if($donor->other_name)
                                    <span class="text-slate-500 font-normal">({{ $donor->other_name }})</span>
                                @endif
                            </div>
                            <div class="text-xs text-slate-500 mt-1">
                                {{ $donor->reg_number }}
                            </div>
                        </td>
                        <td class="hidden md:table-cell px-6 py-4 whitespace-nowrap">
                            @if($donor->gender)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ strtolower($donor->gender) === 'male' ? 'bg-blue-50 text-blue-700 border border-blue-100' : 'bg-pink-50 text-pink-700 border border-pink-100' }}">
                                    {{ ucfirst($donor->gender) }}
                                </span>
                            @else
                                <span class="text-slate-400 text-sm">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-slate-700">
                                {{ $donor->email }}
                            </div>
                            <div class="text-sm text-slate-500">
                                {{ $donor->phone }}
                            </div>
                        </td>
                        <td class="hidden lg:table-cell px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-slate-700">
                                {{ $donor->state ?? 'N/A' }}
                            </div>
                            <div class="text-sm text-slate-500">
                                {{ $donor->nationality ?? 'Nigerian' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2">
                                @if($donor->donations->count() === 0)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-500 border border-slate-200">None</span>
                                @else
                                    <button wire:click="showDonations({{ $donor->id }})" class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-medium bg-blue-50 text-blue-700 border border-blue-100 hover:bg-blue-100 transition-colors">
                                        Details
                                        <span class="ml-2 bg-blue-600 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full">{{ $donor->donations->count() }}</span>
                                    </button>
                                @endif
                                
                                <button 
                                    @click.prevent="window.dispatchEvent(new CustomEvent('open-sms-modal', { detail: { phone: '{{ $donor->phone }}' } }))"
                                    class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-100 hover:bg-emerald-100 transition-colors"
                                >
                                    <i class="fas fa-sms mr-1.5"></i> SMS
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-search text-4xl mb-3 text-slate-300"></i>
                                <p>No donors found matching your search.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $donors->links() }}
    </div>

    @if($showDonationsModal && $selectedDonor)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4" x-data="{ show: @entangle('showDonationsModal'), showWhatsAppForm: false }" x-init="window.addEventListener('message-sent', () => { showWhatsAppForm = false })" x-show="show" x-cloak>
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col border border-slate-100 overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-slate-50">
                    <h3 class="text-lg font-bold text-slate-800">Donations for {{ $selectedDonor->surname }} {{ $selectedDonor->name }}</h3>
                    <button @click="$wire.set('showDonationsModal', false)" class="text-slate-400 hover:text-slate-600 text-2xl font-bold transition-colors">&times;</button>
                </div>

                <!-- Sticky Total Contributions -->
                <div class="px-6 pt-6 pb-4 bg-white border-b border-slate-100 z-10">
                    <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                        <div>
                            <p class="text-sm text-slate-500 font-medium uppercase tracking-wide">Total Contributions</p>
                            <p class="text-3xl font-bold text-emerald-600">₦{{ number_format($selectedDonor->donations->sum('amount'), 2) }}</p>
                        </div>
                        <button @click="showWhatsAppForm = !showWhatsAppForm"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium shadow-sm">
                            <i class="fas fa-envelope mr-2"></i>
                            <span x-text="showWhatsAppForm ? 'Cancel Message' : 'Send Message'"></span>
                        </button>
                    </div>
                </div>

                <div class="overflow-y-auto px-6 pb-6 pt-4 flex-1">

                    <!-- Message Form -->
                    <div x-show="showWhatsAppForm" x-transition x-cloak class="mb-6 bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                        <form wire:submit.prevent="sendMessage" class="flex flex-col gap-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1">Subject</label>
                                <input type="text" wire:model.defer="messageSubject" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1">Message</label>
                                <textarea wire:model.defer="messageBody" rows="4" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                            </div>
                            <div class="flex justify-end gap-2">
                                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium shadow-sm">Send Message</button>
                            </div>
                        </form>
                    </div>

                    <div class="space-y-3">
                        @forelse($selectedDonations as $donation)
                            <div class="p-4 border border-slate-200 rounded-xl bg-white hover:border-blue-200 hover:shadow-sm transition-all duration-200">
                                <div class="flex justify-between items-start mb-2">
                                    <div class="font-bold text-emerald-600 text-lg">₦{{ number_format($donation->amount, 2) }}</div>
                                    <div class="text-xs text-slate-500">{{ $donation->created_at->format('M d, Y H:i') }}</div>
                                </div>
                                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center text-sm gap-2">
                                    <div class="text-slate-600">
                                        <span class="text-slate-400 font-medium">Project:</span> {{ $donation->project->name ?? 'Endowment' }}
                                    </div>
                                    <div class="text-slate-500 font-mono text-xs bg-slate-100 px-2 py-1 rounded border border-slate-200">
                                        {{ $donation->payment_reference }}
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8 text-slate-500">
                                <i class="fas fa-receipt text-4xl mb-3 text-slate-300"></i>
                                <p>No donations found for this donor.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
                <div class="flex justify-end px-6 py-4 border-t border-slate-100 bg-slate-50">
                    <button @click="$wire.set('showDonationsModal', false)" class="px-4 py-2 bg-white border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 transition-colors font-medium shadow-sm">Close</button>
                </div>
            </div>
        </div>
    @endif
</div>
