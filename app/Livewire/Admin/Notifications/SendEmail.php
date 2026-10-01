<?php

namespace App\Livewire\Admin\Notifications;

use Livewire\Component;

use App\Models\EmailTemplate;
use App\Models\Donor;
use App\Models\Project;
use App\Models\EmailLog;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendEmail extends Component
{
    public $step = 1;
    public $selectedTemplateId;
    public $recipientType = 'all'; // all, project, individual
    public $selectedProjectId;
    public $selectedDonorId;
    public $donorSearch = '';
    public $donorOptions = [];
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

    public function updatedRecipientType($value)
    {
        if ($value !== 'individual') {
            $this->selectedDonorId = null;
            $this->donorSearch = '';
            $this->donorOptions = [];
            return;
        }

        $this->loadDonorOptions();
    }

    public function updatedDonorSearch()
    {
        if ($this->recipientType !== 'individual') {
            return;
        }

        $this->selectedDonorId = null;
        $this->loadDonorOptions();
    }

    public function selectDonor($donorId)
    {
        $donor = Donor::query()
            ->where('id', $donorId)
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->first();

        if (!$donor) {
            return;
        }

        $this->selectedDonorId = $donor->id;
        $this->donorSearch = trim($donor->full_name . ' (' . $donor->email . ')');
        $this->donorOptions = [];
    }

    private function loadDonorOptions()
    {
        $term = trim($this->donorSearch);

        $query = Donor::query()
            ->whereNotNull('email')
            ->where('email', '!=', '');

        if ($term !== '') {
            $query->where(function ($subQuery) use ($term) {
                $subQuery->where('surname', 'like', '%' . $term . '%')
                    ->orWhere('name', 'like', '%' . $term . '%')
                    ->orWhere('other_name', 'like', '%' . $term . '%')
                    ->orWhere('email', 'like', '%' . $term . '%');
            });
        }

        $this->donorOptions = $query
            ->orderBy('surname')
            ->orderBy('name')
            ->limit(20)
            ->get()
            ->map(function ($donor) {
                return [
                    'id' => $donor->id,
                    'label' => trim($donor->full_name . ' (' . $donor->email . ')'),
                ];
            })
            ->toArray();
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
        if (!$template) {
            $this->sending = false;
            session()->flash('message', 'Selected template was not found.');
            return;
        }
        
        $recipientQuery = Donor::query()->whereNotNull('email')->where('email', '!=', '');

        if ($this->recipientType === 'all') {
            // Send to every donor with a usable email address.
        } elseif ($this->recipientType === 'project') {
            $this->validate(['selectedProjectId' => 'required']);
            $recipientQuery->whereHas('donations', function ($query) {
                $query->where('project_id', $this->selectedProjectId);
            });
        } elseif ($this->recipientType === 'individual') {
            $this->validate(['selectedDonorId' => 'required']);
            $recipientQuery->where('id', $this->selectedDonorId);
        }

        $total = (clone $recipientQuery)->count();
        if ($total === 0) {
            $this->sending = false;
            session()->flash('message', 'No valid donor email addresses found for the selected recipient group.');
            return;
        }

        $processed = 0;

        $recipientQuery->orderBy('id')->chunkById(100, function ($donors) use ($template, $total, &$processed) {
            foreach ($donors as $donor) {
                $donorName = trim(implode(' ', array_filter([
                    $donor->surname,
                    $donor->name,
                    $donor->other_name,
                ])));

                $data = [
                    'donor_name' => $donorName,
                    'donor_email' => $donor->email,
                    'amount' => 'N/A',
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
                        'recipient_name' => $donorName,
                        'template_id' => $template->id,
                        'status' => 'sent',
                        'sent_at' => now(),
                    ]);
                } catch (\Exception $e) {
                    Log::error('Bulk email send failed', [
                        'donor_id' => $donor->id,
                        'recipient_email' => $donor->email,
                        'template_id' => $template->id,
                        'error' => $e->getMessage(),
                    ]);

                    EmailLog::create([
                        'recipient_email' => $donor->email,
                        'recipient_name' => $donorName,
                        'template_id' => $template->id,
                        'status' => 'failed',
                        'error_message' => $e->getMessage(),
                    ]);
                }

                $processed++;
                $this->progress = ($processed / $total) * 100;
            }
        });

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
            'donors' => Donor::query()
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->orderBy('id')
                ->limit(50)
                ->get(),
        ]);
    }
}
