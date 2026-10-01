<?php

namespace App\Livewire\Admin\Notifications;

use Livewire\Component;

use App\Models\EmailTemplate;
use App\Models\Donor;
use App\Models\Project;
use App\Models\EmailLog;
use Illuminate\Support\Facades\Mail;

class SendEmail extends Component
{
    public $step = 1;
    public $selectedTemplateId;
    public $recipientType = 'all'; // all, project, individual
    public $selectedProjectId;
    public $selectedDonorId;
    public $testEmail;
    public $recipientCount = 0;
    public $sending = false;
    public $progress = 0;

    public function mount()
    {
        //
    }

    public function nextStep()
    {
        if ($this->step === 1) {
            $this->validate(['selectedTemplateId' => 'required']);
        } elseif ($this->step === 2) {
            $this->calculateRecipientCount();
        }
        $this->step++;
    }

    public function prevStep()
    {
        $this->step--;
    }

    public function calculateRecipientCount()
    {
        if ($this->recipientType === 'all') {
            $this->recipientCount = Donor::count();
        } elseif ($this->recipientType === 'project') {
            $this->validate(['selectedProjectId' => 'required']);
            // Assuming donors are linked to projects via donations. 
            // This logic might need adjustment based on actual relationships.
            // For now, let's assume we can get unique donor IDs from donations table filtered by project_id
            $this->recipientCount = \DB::table('donations')
                ->where('project_id', $this->selectedProjectId)
                ->distinct('donor_id')
                ->count('donor_id');
        } elseif ($this->recipientType === 'individual') {
            $this->validate(['selectedDonorId' => 'required']);
            $this->recipientCount = 1;
        }
    }

    public function sendTestEmail()
    {
        $this->validate(['testEmail' => 'required|email']);
        
        $template = EmailTemplate::find($this->selectedTemplateId);
        if (!$template) return;

        // Mock data for test
        $data = [
            'donor_name' => 'Test Donor',
            'donor_email' => $this->testEmail,
            'amount' => '1000',
            'donation_date' => now()->format('Y-m-d'),
            'reference' => 'TEST-REF-123',
            'project_name' => 'Test Project',
            'organization_name' => 'ABU Endowment',
        ];

        $content = $this->replaceVariables($template->body_html, $data);
        $subject = $this->replaceVariables($template->subject, $data);

        try {
            Mail::html($content, function ($message) use ($subject) {
                $message->to($this->testEmail)
                    ->subject($subject);
            });
            session()->flash('test_message', 'Test email sent successfully!');
        } catch (\Exception $e) {
            session()->flash('test_error', 'Failed to send test email: ' . $e->getMessage());
        }
    }

    public function sendEmails()
    {
        $this->sending = true;
        $template = EmailTemplate::find($this->selectedTemplateId);
        
        $donors = collect();
        if ($this->recipientType === 'all') {
            $donors = Donor::all();
        } elseif ($this->recipientType === 'project') {
             $donorIds = \DB::table('donations')
                ->where('project_id', $this->selectedProjectId)
                ->distinct('donor_id')
                ->pluck('donor_id');
            $donors = Donor::whereIn('id', $donorIds)->get();
        } elseif ($this->recipientType === 'individual') {
            $donors = Donor::where('id', $this->selectedDonorId)->get();
        }

        $total = $donors->count();
        $processed = 0;

        foreach ($donors as $donor) {
            // Prepare data
            $data = [
                'donor_name' => $donor->first_name . ' ' . $donor->last_name,
                'donor_email' => $donor->email,
                'amount' => 'N/A', // Context specific, maybe fetch last donation?
                'donation_date' => now()->format('Y-m-d'),
                'reference' => 'N/A',
                'project_name' => 'General Update',
                'organization_name' => 'ABU Endowment',
            ];

            $content = $this->replaceVariables($template->body_html, $data);
            $subject = $this->replaceVariables($template->subject, $data);

            try {
                Mail::html($content, function ($message) use ($donor, $subject) {
                    $message->to($donor->email)
                        ->subject($subject);
                });

                EmailLog::create([
                    'recipient_email' => $donor->email,
                    'recipient_name' => $donor->first_name . ' ' . $donor->last_name,
                    'template_id' => $template->id,
                    'status' => 'sent',
                    'sent_at' => now(),
                ]);
            } catch (\Exception $e) {
                EmailLog::create([
                    'recipient_email' => $donor->email,
                    'recipient_name' => $donor->first_name . ' ' . $donor->last_name,
                    'template_id' => $template->id,
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                ]);
            }

            $processed++;
            $this->progress = ($processed / $total) * 100;
        }

        $this->sending = false;
        session()->flash('message', "Emails sent to {$processed} recipients.");
        return redirect()->route('admin.notifications.logs');
    }

    private function replaceVariables($content, $data)
    {
        foreach ($data as $key => $value) {
            $content = str_replace('{{' . $key . '}}', $value, $content);
        }
        return $content;
    }

    public function render()
    {
        return view('livewire.admin.notifications.send-email', [
            'templates' => EmailTemplate::where('is_active', true)->get(),
            'projects' => Project::all(),
            'donors' => $this->recipientType === 'individual' ? Donor::all() : [],
        ]);
    }
}
