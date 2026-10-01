<?php

namespace App\Livewire\Admin\Notifications;

use Livewire\Component;

use App\Models\EmailLog;
use Livewire\WithPagination;

class EmailLogs extends Component
{
    use WithPagination;

    public $selectedLog;
    public $showLogModal = false;

    public function viewLog($id)
    {
        $this->selectedLog = EmailLog::with('template')->find($id);
        $this->showLogModal = true;
    }

    public function closeLogModal()
    {
        $this->showLogModal = false;
        $this->selectedLog = null;
    }

    public function render()
    {
        return view('livewire.admin.notifications.email-logs', [
            'logs' => EmailLog::with('template')->latest()->paginate(15),
        ]);
    }
}
