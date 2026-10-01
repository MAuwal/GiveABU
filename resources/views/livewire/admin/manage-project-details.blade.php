<div>
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm" 
         x-data="{ show: @entangle('showModal') }" 
         x-show="show" 
         x-cloak>
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden">
            <!-- Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-slate-50">
                <h3 class="text-lg font-bold text-slate-800">
                    Project Details: <span class="text-blue-600">{{ $project->project_title }}</span>
                </h3>
                <button wire:click="$set('showModal', false)" class="text-slate-400 hover:text-slate-600 text-2xl font-bold transition-colors">&times;</button>
            </div>

            <!-- Body -->
            <div class="overflow-y-auto p-6 flex-1 bg-white space-y-6">
                
                <!-- Background -->
                <div wire:key="background-{{ $project->id }}">
                    <label class="block text-sm font-bold text-slate-700 mb-2">Background</label>
                    <div wire:ignore x-data="{ value: @entangle('background') }" x-on:trix-change="value = $event.target.value">
                        <trix-editor input="background-{{ $project->id }}" class="trix-content min-h-[150px] border-slate-200 rounded-lg focus:border-blue-500 focus:ring-blue-500"></trix-editor>
                        <input id="background-{{ $project->id }}" type="hidden" :value="value">
                    </div>
                </div>

                <!-- Challenges -->
                <div wire:key="challenges-{{ $project->id }}">
                    <label class="block text-sm font-bold text-slate-700 mb-2">Challenges</label>
                    <div wire:ignore x-data="{ value: @entangle('challenges') }" x-on:trix-change="value = $event.target.value">
                        <trix-editor input="challenges-{{ $project->id }}" class="trix-content min-h-[150px] border-slate-200 rounded-lg focus:border-blue-500 focus:ring-blue-500"></trix-editor>
                        <input id="challenges-{{ $project->id }}" type="hidden" :value="value">
                    </div>
                </div>

                <!-- Proposed Interventions -->
                <div wire:key="proposed_interventions-{{ $project->id }}">
                    <label class="block text-sm font-bold text-slate-700 mb-2">Proposed Interventions</label>
                    <div wire:ignore x-data="{ value: @entangle('proposed_interventions') }" x-on:trix-change="value = $event.target.value">
                        <trix-editor input="proposed_interventions-{{ $project->id }}" class="trix-content min-h-[150px] border-slate-200 rounded-lg focus:border-blue-500 focus:ring-blue-500"></trix-editor>
                        <input id="proposed_interventions-{{ $project->id }}" type="hidden" :value="value">
                    </div>
                </div>

                 <!-- Expected Outcomes -->
                 <div wire:key="expected_outcomes-{{ $project->id }}">
                    <label class="block text-sm font-bold text-slate-700 mb-2">Expected Outcomes</label>
                    <div wire:ignore x-data="{ value: @entangle('expected_outcomes') }" x-on:trix-change="value = $event.target.value">
                        <trix-editor input="expected_outcomes-{{ $project->id }}" class="trix-content min-h-[150px] border-slate-200 rounded-lg focus:border-blue-500 focus:ring-blue-500"></trix-editor>
                        <input id="expected_outcomes-{{ $project->id }}" type="hidden" :value="value">
                    </div>
                </div>

                 <!-- Beneficiaries -->
                 <div wire:key="beneficiaries-{{ $project->id }}">
                    <label class="block text-sm font-bold text-slate-700 mb-2">Beneficiaries</label>
                    <div wire:ignore x-data="{ value: @entangle('beneficiaries') }" x-on:trix-change="value = $event.target.value">
                        <trix-editor input="beneficiaries-{{ $project->id }}" class="trix-content min-h-[150px] border-slate-200 rounded-lg focus:border-blue-500 focus:ring-blue-500"></trix-editor>
                        <input id="beneficiaries-{{ $project->id }}" type="hidden" :value="value">
                    </div>
                </div>

                 <!-- Budget Estimates -->
                 <div wire:key="budget_estimates-{{ $project->id }}">
                    <label class="block text-sm font-bold text-slate-700 mb-2">Budget Estimates</label>
                    <div wire:ignore x-data="{ value: @entangle('budget_estimates') }" x-on:trix-change="value = $event.target.value">
                        <trix-editor input="budget_estimates-{{ $project->id }}" class="trix-content min-h-[150px] border-slate-200 rounded-lg focus:border-blue-500 focus:ring-blue-500"></trix-editor>
                        <input id="budget_estimates-{{ $project->id }}" type="hidden" :value="value">
                    </div>
                </div>

            </div>

            <!-- Footer -->
            <div class="flex justify-end gap-3 px-6 py-4 border-t border-slate-100 bg-slate-50">
                <button wire:click="$set('showModal', false)" class="px-4 py-2 bg-white border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 transition-colors font-medium text-sm shadow-sm">Cancel</button>
                <button wire:click="save" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium text-sm shadow-sm flex items-center">
                    <i class="fas fa-save mr-2"></i> Save Details
                </button>
            </div>
        </div>
        

    </div>
    @endif
</div>
