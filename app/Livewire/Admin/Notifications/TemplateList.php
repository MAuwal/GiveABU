<?php

namespace App\Livewire\Admin\Notifications;

use Livewire\Component;

use App\Models\EmailTemplate;
use Livewire\WithPagination;

class TemplateList extends Component
{
    use WithPagination;

    public function deleteTemplate($id)
    {
        $template = EmailTemplate::find($id);
        if ($template) {
            $template->delete();
            session()->flash('message', 'Template deleted successfully.');
        }
    }

    public function toggleStatus($id)
    {
        $template = EmailTemplate::find($id);
        if ($template) {
            $template->is_active = !$template->is_active;
            $template->save();
        }
    }

    public function duplicateTemplate($id)
    {
        $template = EmailTemplate::find($id);
        if ($template) {
            $newTemplate = $template->replicate();
            $newTemplate->name = $template->name . ' (Copy)';
            $newTemplate->slug = $template->slug . '-copy-' . time();
            $newTemplate->save();
            session()->flash('message', 'Template duplicated successfully.');
        }
    }

    public function render()
    {
        return view('livewire.admin.notifications.template-list', [
            'templates' => EmailTemplate::latest()->paginate(10),
        ]);
    }
}
