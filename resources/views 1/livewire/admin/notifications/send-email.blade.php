<div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
    <!-- Steps Indicator -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div class="flex flex-col items-center">
                <div class="w-8 h-8 rounded-full flex items-center justify-center {{ $step >= 1 ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-600' }}">1</div>
                <div class="text-xs mt-1 dark:text-gray-300">Template</div>
            </div>
            <div class="flex-1 h-1 mx-4 {{ $step >= 2 ? 'bg-indigo-600' : 'bg-gray-200' }}"></div>
            <div class="flex flex-col items-center">
                <div class="w-8 h-8 rounded-full flex items-center justify-center {{ $step >= 2 ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-600' }}">2</div>
                <div class="text-xs mt-1 dark:text-gray-300">Recipients</div>
            </div>
            <div class="flex-1 h-1 mx-4 {{ $step >= 3 ? 'bg-indigo-600' : 'bg-gray-200' }}"></div>
            <div class="flex flex-col items-center">
                <div class="w-8 h-8 rounded-full flex items-center justify-center {{ $step >= 3 ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-600' }}">3</div>
                <div class="text-xs mt-1 dark:text-gray-300">Test & Send</div>
            </div>
        </div>
    </div>

    <!-- Step 1: Select Template -->
    @if ($step === 1)
        <div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Select Email Template</h3>
            <div class="grid grid-cols-1 gap-4">
                <select wire:model="selectedTemplateId" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm">
                    <option value="">-- Select a Template --</option>
                    @foreach ($templates as $template)
                        <option value="{{ $template->id }}">{{ $template->name }}</option>
                    @endforeach
                </select>
                @error('selectedTemplateId') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror

                @if ($selectedTemplateId)
                    @php
                        $previewTemplate = $templates->find($selectedTemplateId);
                    @endphp
                    <div class="mt-4 border rounded p-4 bg-gray-50 dark:bg-gray-700 dark:border-gray-600">
                        <p class="font-bold dark:text-white">Subject: {{ $previewTemplate->subject }}</p>
                        <hr class="my-2 dark:border-gray-600">
                        <div class="prose dark:prose-invert max-w-none">
                            {!! $previewTemplate->body_html !!}
                        </div>
                    </div>
                @endif
            </div>
            <div class="mt-6 flex justify-end">
                <button wire:click="nextStep" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Next <i class="fas fa-arrow-right ml-2"></i>
                </button>
            </div>
        </div>
    @endif

    <!-- Step 2: Select Recipients -->
    @if ($step === 2)
        <div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Select Recipients</h3>
            <div class="space-y-4">
                <div>
                    <label class="inline-flex items-center">
                        <input type="radio" wire:model="recipientType" value="all" class="form-radio text-indigo-600">
                        <span class="ml-2 dark:text-gray-300">All Donors</span>
                    </label>
                </div>
                <div>
                    <label class="inline-flex items-center">
                        <input type="radio" wire:model="recipientType" value="project" class="form-radio text-indigo-600">
                        <span class="ml-2 dark:text-gray-300">Donors by Project</span>
                    </label>
                    @if ($recipientType === 'project')
                        <select wire:model="selectedProjectId" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm">
                            <option value="">-- Select Project --</option>
                            @foreach ($projects as $project)
                                <option value="{{ $project->id }}">{{ $project->title }}</option>
                            @endforeach
                        </select>
                        @error('selectedProjectId') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    @endif
                </div>
                <div>
                    <label class="inline-flex items-center">
                        <input type="radio" wire:model="recipientType" value="individual" class="form-radio text-indigo-600">
                        <span class="ml-2 dark:text-gray-300">Individual Donor</span>
                    </label>
                    @if ($recipientType === 'individual')
                        <select wire:model="selectedDonorId" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm">
                            <option value="">-- Select Donor --</option>
                            @foreach ($donors as $donor)
                                <option value="{{ $donor->id }}">{{ $donor->first_name }} {{ $donor->last_name }} ({{ $donor->email }})</option>
                            @endforeach
                        </select>
                        @error('selectedDonorId') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    @endif
                </div>
            </div>
            <div class="mt-6 flex justify-between">
                <button wire:click="prevStep" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                    <i class="fas fa-arrow-left mr-2"></i> Previous
                </button>
                <button wire:click="nextStep" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Next <i class="fas fa-arrow-right ml-2"></i>
                </button>
            </div>
        </div>
    @endif

    <!-- Step 3: Test & Send -->
    @if ($step === 3)
        <div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Review & Send</h3>
            
            <div class="bg-blue-50 dark:bg-blue-900 border-l-4 border-blue-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700 dark:text-blue-200">
                            You are about to send emails to <strong>{{ $recipientCount }}</strong> recipient(s).
                        </p>
                    </div>
                </div>
            </div>

            <!-- Test Email Section -->
            <div class="mb-6 border-b border-gray-200 dark:border-gray-700 pb-6">
                <h4 class="text-md font-medium text-gray-900 dark:text-white mb-2">Send Test Email</h4>
                <div class="flex gap-2">
                    <input type="email" wire:model="testEmail" placeholder="Enter admin email" class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm">
                    <button wire:click="sendTestEmail" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                        Send Test
                    </button>
                </div>
                @error('testEmail') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                @if (session()->has('test_message'))
                    <span class="text-green-500 text-xs mt-1 block">{{ session('test_message') }}</span>
                @endif
                @if (session()->has('test_error'))
                    <span class="text-red-500 text-xs mt-1 block">{{ session('test_error') }}</span>
                @endif
            </div>

            <div class="flex justify-between items-center">
                <button wire:click="prevStep" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                    <i class="fas fa-arrow-left mr-2"></i> Previous
                </button>
                
                @if ($sending)
                    <div class="flex items-center text-indigo-600">
                        <i class="fas fa-spinner fa-spin mr-2"></i> Sending... {{ number_format($progress, 0) }}%
                    </div>
                @else
                    <button wire:click="sendEmails" onclick="return confirm('Are you sure you want to send these emails? This action cannot be undone.')" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        <i class="fas fa-paper-plane mr-2"></i> Send to {{ $recipientCount }} Recipients
                    </button>
                @endif
            </div>
        </div>
    @endif
</div>
