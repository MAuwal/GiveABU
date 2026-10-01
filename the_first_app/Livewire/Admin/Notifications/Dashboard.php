<?php

namespace App\Livewire\Admin\Notifications;

use Livewire\Component;

use App\Models\EmailTemplate;
use App\Models\EmailLog;

class Dashboard extends Component
{
    public $totalTemplates;
    public $emailsSentThisMonth;
    public $failedEmails;

    public function mount()
    {
        $this->totalTemplates = EmailTemplate::count();
        $this->emailsSentThisMonth = EmailLog::where('status', 'sent')
            ->whereMonth('created_at', now()->month)
            ->count();
        $this->failedEmails = EmailLog::where('status', 'failed')->count();
    }

    public function render()
    {
        return view('livewire.admin.notifications.dashboard');
    }
}
