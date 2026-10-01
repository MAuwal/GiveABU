<div>
    {{-- If your happiness depends on money, you will never be happy with yourself. --}}

    <!-- Modal toggle -->
    <button wire:click="openModal" type="button" class="bg-white overflow-hidden shadow-sm border border-slate-200 rounded-xl hover:shadow-md transition-all duration-200 block w-full text-left group">
        <div class="p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-blue-50 p-3 rounded-lg group-hover:bg-blue-100 transition-colors">
                    <i class="fas fa-upload text-blue-600 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-bold text-slate-800 group-hover:text-blue-600 transition-colors">Upload Alumni</h3>
                    <p class="text-sm text-slate-500">Import alumni data from Excel or CSV</p>
                </div>
            </div>
        </div>
    </button>

    @if($showModal)
    <!-- Main modal -->
    <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto overflow-x-hidden bg-black/50 backdrop-blur-sm p-4">
        <div class="relative w-full max-w-4xl h-auto max-h-[90vh] flex flex-col">
            <!-- Modal content -->
            <div class="relative bg-white rounded-2xl shadow-2xl border border-slate-100 flex flex-col max-h-full">
                <!-- Modal header -->
                <div class="flex justify-between items-center px-6 py-4 rounded-t-2xl border-b border-slate-100 bg-slate-50">
                    <h3 class="text-xl font-bold text-slate-800">
                        Upload Alumni Data
                    </h3>
                    <button wire:click="closeModal" type="button" class="text-slate-400 bg-transparent hover:bg-slate-100 hover:text-slate-600 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <!-- Modal body -->
                <div class="overflow-y-auto flex-1">
                    <form wire:submit.prevent="import">
                        <div class="p-6 space-y-6">
                            @if($importing && !$importFinished)
                                <div class="w-full text-center p-8">
                                    <i class="fas fa-spinner fa-spin text-4xl text-blue-600 mb-4"></i>
                                    <p class="text-lg font-medium text-slate-700">Importing... Please wait.</p>
                                </div>
                            @elseif($importFinished)
                                <div class="w-full text-center p-4">
                                    @if($successMessage)
                                    <div class="mb-4 inline-flex items-center justify-center w-16 h-16 rounded-full bg-emerald-100 mb-4">
                                        <i class="fas fa-check text-3xl text-emerald-600"></i>
                                    </div>
                                    <p class="text-lg font-bold text-emerald-700">{{ $successMessage }}</p>
                                    @endif
                                    @if($errorMessage)
                                    <div class="mb-4 inline-flex items-center justify-center w-16 h-16 rounded-full bg-rose-100 mb-4">
                                        <i class="fas fa-exclamation-triangle text-3xl text-rose-600"></i>
                                    </div>
                                    <p class="text-lg font-bold text-rose-700">{{ $errorMessage }}</p>
                                    @endif
                                    
                                    <!-- Import Statistics -->
                                    <div class="mt-6 grid grid-cols-3 gap-4 text-sm">
                                        <div class="bg-emerald-50 p-4 rounded-xl border border-emerald-100">
                                            <div class="text-3xl font-bold text-emerald-600">{{ $importedCount }}</div>
                                            <div class="text-emerald-700 font-medium">Imported</div>
                                        </div>
                                        <div class="bg-amber-50 p-4 rounded-xl border border-amber-100">
                                            <div class="text-3xl font-bold text-amber-600">{{ $skippedCount }}</div>
                                            <div class="text-amber-700 font-medium">Skipped</div>
                                        </div>
                                        <div class="bg-rose-50 p-4 rounded-xl border border-rose-100">
                                            <div class="text-3xl font-bold text-rose-600">{{ $failedCount }}</div>
                                            <div class="text-rose-700 font-medium">Failed</div>
                                        </div>
                                    </div>
                                    
                                    @if($failures && count($failures) > 0)
                                    <div class="mt-6 text-left">
                                        <h4 class="font-bold text-slate-800 mb-3">Error Details:</h4>
                                        <div class="max-h-60 overflow-y-auto border border-slate-200 rounded-xl p-4 bg-slate-50">
                                            @foreach($failures as $failure)
                                            <div class="mb-3 p-3 bg-white border-l-4 border-rose-500 rounded shadow-sm">
                                                <p class="text-sm font-bold text-rose-700">Row {{ $failure['row'] }}:</p>
                                                <ul class="text-xs text-slate-600 ml-4 mt-1 list-disc">
                                                    @foreach($failure['errors'] as $error)
                                                    <li>{{ $error }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            @else
                                <div class="w-full">
                                    <label for="upload" class="block mb-2 text-sm font-bold text-slate-700">Upload file</label>
                                    <input wire:model="upload" type="file" id="upload" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition-all duration-200 bg-white border border-slate-200 rounded-lg cursor-pointer">
                                    <p class="mt-2 text-xs text-slate-500" id="file_input_help">Allowed file types: XLS, XLSX, CSV (Max. 10MB).</p>
                                    @error('upload') <span class="text-rose-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                                </div>
                                <div class="text-sm text-slate-600">
                                    <p class="font-bold mb-3 text-slate-800">Please ensure your file has a header row with the following columns:</p>
                                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2">
                                        @foreach(['surname', 'name', 'other_name', 'gender (male|female)', 'reg_number', 'lga', 'nationality', 'state', 'address', 'email', 'phone', 'entry_year', 'graduation_year', 'faculty', 'department'] as $column)
                                            <div class="bg-slate-100 p-2 rounded border border-slate-200 text-center">
                                                <span class="font-mono text-xs text-slate-600">{{ $column }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="mt-4 p-4 bg-blue-50 border border-blue-100 rounded-xl flex items-start">
                                        <i class="fas fa-info-circle text-blue-500 mt-0.5 mr-3"></i>
                                        <p class="text-sm text-blue-700">
                                            <strong>Note:</strong> The system will automatically create faculties and departments if they don't exist. 
                                            Visions will be managed separately through the admin interface.
                                        </p>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <!-- Modal footer -->
                        <div class="flex items-center justify-end px-6 py-4 space-x-3 rounded-b-2xl border-t border-slate-100 bg-slate-50">
                            <button wire:click="closeModal" type="button" class="px-5 py-2.5 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-200 transition-colors">
                                {{ ($importFinished) ? 'Close' : 'Cancel' }}
                            </button>
                            @if(!$importing && !$importFinished)
                            <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm transition-colors disabled:opacity-50 disabled:cursor-not-allowed" @if(!$upload) disabled @endif>
                                Import Data
                            </button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
