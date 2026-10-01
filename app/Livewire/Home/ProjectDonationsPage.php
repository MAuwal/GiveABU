<?php

namespace App\Livewire\Home;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Donation;
use App\Models\DonorSession;
use App\Models\Donor;
use App\Models\Project;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;

class ProjectDonationsPage extends Component
{
    use WithPagination;

    public $showModal = false;
    public $selectedProject = null;
    
    // Image gallery modal
    public $showImageGallery = false;
    public $galleryProject = null;
    
    // Donation form fields
    public $amount = 1000;
    public $customAmount;
    public $selectedAmount = 1000;
    public $email;
    public $name;
    public $phone;
    public $paymentReference; // Store reference for manual verification
    
    public function mount()
    {
        $this->syncRaisedAmounts();
        $this->checkAuth();
    }
    
    public function syncRaisedAmounts()
    {
        foreach (Project::pluck('id') as $projectId) {
            app(\App\Services\ProjectFundingService::class)->rebuild($projectId);
        }
    }

    public function checkAuth()
    {
        if (Session::has('donor_token')) {
            $token = Session::get('donor_token');
            $donorSession = DonorSession::with('donor')->find($token);
            
            if ($donorSession && $donorSession->donor) {
                $this->email = $donorSession->donor->email;
                $this->name = $donorSession->donor->name;
                $this->phone = $donorSession->donor->phone;
            } elseif ($donorSession) {
                $this->email = $donorSession->username;
            }
        }
    }

    public function openDonationModal($projectId)
    {
        $this->selectedProject = Project::find($projectId);
        $this->showModal = true;
        // $this->dispatch('open-donation-modal'); // If needed
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedProject = null;
        $this->reset(['amount', 'customAmount', 'selectedAmount']);
    }

    public function openImageGallery($projectId)
    {
        $this->galleryProject = Project::with('photos')->find($projectId);
        $this->showImageGallery = true;
    }

    public function closeImageGallery()
    {
        $this->showImageGallery = false;
        $this->galleryProject = null;
    }

    public function updatedSelectedAmount($value)
    {
        if ($value === 'custom') {
            $this->amount = $this->customAmount;
        } else {
            $this->amount = $value;
            $this->customAmount = null;
        }
    }

    public function updatedCustomAmount($value)
    {
        $this->selectedAmount = 'custom';
        $this->amount = $value;
    }

    public function payWithSquad()
    {
        $this->validate([
            'amount' => 'required|numeric|min:100',
            'email'  => 'required|email',
        ]);

        if (!$this->selectedProject) {
            session()->flash('error', 'Please select a project to donate to.');
            return;
        }

        $this->dispatch('initiate-squad', [
            'email'         => $this->email,
            'amount'        => $this->amount,
            'customer_name' => $this->name ?? '',
            'project_id'    => $this->selectedProject->id,
        ]);
    }

    public function payWithInterswitch()
    {
        $this->validate([
            'amount' => 'required|numeric|min:100',
            'email'  => 'required|email',
        ]);

        if (!$this->selectedProject) {
            session()->flash('error', 'Please select a project to donate to.');
            return;
        }

        $this->dispatch('initiate-interswitch', [
            'email'         => $this->email,
            'amount'        => $this->amount,
            'customer_name' => $this->name ?? '',
            'project_id'    => $this->selectedProject->id,
        ]);
    }

    public function donate()
    {
        $this->validate([
            'amount' => 'required|numeric|min:100',
            'email' => 'required|email',
        ]);

        if (!$this->selectedProject) {
            session()->flash('error', 'Please select a project to donate to.');
            return;
        }

        // Create pending donation
        $reference = 'ABU_PRJ_' . time() . '_' . uniqid();
        $this->paymentReference = $reference; // Store for manual verification
        
        // Find or create donor
        $donorId = null;
        if (Session::has('donor_token')) {
            $token = Session::get('donor_token');
            $session = DonorSession::find($token);
            $donorId = $session ? $session->donor_id : null;
        }

        if (!$donorId) {
            $donor = Donor::firstOrCreate(
                ['email' => $this->email],
                ['name' => $this->name ?? 'Guest', 'surname' => 'Donor', 'donor_type' => 'supporter']
            );
            $donorId = $donor->id;
        }

        $donation = Donation::create([
            'donor_id' => $donorId,
            'project_id' => $this->selectedProject->id,
            'amount' => $this->amount,
            'type' => 'project',
            'frequency' => 'onetime',
            'endowment' => 'no',
            'status' => 'pending',
            'payment_reference' => $reference,
        ]);

        // Close modal before opening Paystack
        // $this->showModal = false; // Kept open to show progress/wait for completion
        
        // Dispatch event to frontend to open Paystack
        $this->dispatch('initiate-paystack', [
            'key' => config('services.paystack.public_key'),
            'email' => $this->email,
            'amount' => $this->amount * 100, // In kobo
            'ref' => $reference,
            'currency' => 'NGN',
            'metadata' => [
                'donation_id' => $donation->id,
                'project_id' => $this->selectedProject->id,
                'project_name' => $this->selectedProject->name,
                'custom_fields' => [
                    [
                        'display_name' => "Project",
                        'variable_name' => "project_name",
                        'value' => $this->selectedProject->name
                    ]
                ]
            ]
        ]);
    }

    #[On('project-payment-success')]
    public function verifyPayment($data = null)
    {
        if (!$data) {
            return;
        }

        $reference = is_array($data) ? $data['reference'] : $data;
        
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.paystack.secret_key'),
            ])->get("https://api.paystack.co/transaction/verify/{$reference}");

            if ($response->successful()) {
                $paymentData = $response->json()['data'];
                
                if ($paymentData['status'] === 'success') {
                    $donation = Donation::where('payment_reference', $reference)->first();
                    
                    if ($donation && $donation->status !== 'completed') {
                        $donation->update([
                            'status' => 'completed',
                            'verified_at' => now(),
                            'paid_at' => now(),
                        ]);

                        // Update Project Raised Amount - Recalculate total from donations table
                        if ($donation->project_id) {
                            $project = Project::find($donation->project_id);
                            if ($project) {
                                app(\App\Services\ProjectFundingService::class)->rebuild($project->id);
                                $project->refresh();
                                $totalRaised = $project->raised;
                            }
                        }
                        
                        // Close the modal
                        $this->showModal = false;
                        
                        // Refresh projects to show updated progress
                        $this->loadProjects();
                        
                        // Dispatch event to close modal on frontend
                        $this->dispatch('close-donation-modal');
                        
                        // Flash success message for toast
                        $this->dispatch('show-toast', [
                            'type' => 'success',
                            'message' => 'Thank you for your donation! Your support means the world to us.'
                        ]);
                    }
                } else {
                    $this->dispatch('show-toast', [
                        'type' => 'error',
                        'message' => 'Payment verification failed. Please contact support.'
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Payment verification error: ' . $e->getMessage());
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'An error occurred during verification.'
            ]);
        }
    }

    public function render()
    {
        $projects = Project::with('photos')->orderBy('created_at', 'desc')->paginate(4);

        return view('livewire.home.project-donations-page', [
            'projects' => $projects,
        ]);
    }
}
