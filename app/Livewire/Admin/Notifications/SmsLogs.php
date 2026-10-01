<?php

namespace App\Livewire\Admin\Notifications;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\SmsLog;

class SmsLogs extends Component
{
    use WithPagination;

    public $selectedLog;
    public $showLogModal = false;

    public function viewLog($id)
    {
        $this->selectedLog = SmsLog::find($id);
        $this->showLogModal = true;
    }

    public function closeLogModal()
    {
        $this->showLogModal = false;
        $this->selectedLog = null;
    }

    public function render()
    {
        return view('livewire.admin.notifications.sms-logs', [
            'logs' => SmsLog::latest()->paginate(15),
        ]);
    }
}
