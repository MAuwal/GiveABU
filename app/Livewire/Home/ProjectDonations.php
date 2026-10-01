<?php

namespace App\Livewire\Home;

use Livewire\Component;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Donation;
use App\Models\DonorSession;
use App\Models\Donor;
use App\Models\Project;
use Livewire\Attributes\On;

class ProjectDonations extends Component
{
    public $projects = [];
    public $totalProjects = 0;
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
    
    public $limit = null; // Add limit property

    public function mount($limit = null)
    {
        $this->limit = $limit; // Set limit from parameter
        $this->loadProjects();
        $this->checkAuth();
    }
    
    public function loadProjects()
    {
        foreach (Project::pluck('id') as $projectId) {
            app(\App\Services\ProjectFundingService::class)->rebuild($projectId);
        }

        // Load projects from database
        $query = Project::with('photos')->orderBy('created_at', 'desc');
        
        if ($this->limit) {
            $query->take($this->limit);
        }
        
        $this->projects = $query->get();
        $this->totalProjects = Project::count();
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
        $this->dispatch('open-donation-modal');
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
        Log::info('verifyPayment called in ProjectDonations', ['data' => $data]);

        if (!$data) {
            Log::warning('verifyPayment called with no data');
            return;
        }

        $reference = is_array($data) ? $data['reference'] : $data;
        Log::info('Verifying payment reference: ' . $reference);
        
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.paystack.secret_key'),
            ])->get("https://api.paystack.co/transaction/verify/{$reference}");

            Log::info('Paystack API response status: ' . $response->status());
            Log::info('Paystack API response body: ' . $response->body());

            if ($response->successful()) {
                $paymentData = $response->json()['data'];
                
                if ($paymentData['status'] === 'success') {
                    $donation = Donation::where('payment_reference', $reference)->first();
                    
                    if ($donation) {
                        Log::info('Donation found: ' . $donation->id . ', Current Status: ' . $donation->status);
                        
                        if ($donation->status !== 'completed') {
                            $donation->update([
                                'status' => 'completed',
                                'verified_at' => now(),
                                'paid_at' => now(),
                            ]);
                            Log::info('Donation updated to completed');

                            // Update Project Raised Amount
                            if ($donation->project_id) {
                                $project = Project::find($donation->project_id);
                                if ($project) {
                                    app(\App\Services\ProjectFundingService::class)->rebuild($project->id);
                                    $project->refresh();
                                    $totalRaised = $project->raised;
                                    Log::info('Project raised amount updated to: ' . $totalRaised);
                                }
                            }
                            
                            $this->showModal = false;
                            $this->loadProjects();
                            $this->dispatch('close-donation-modal');
                            
                            $this->dispatch('show-toast', [
                                'type' => 'success',
                                'message' => 'Thank you for your donation! Your support means the world to us.'
                            ]);
                        } else {
                            Log::info('Donation was already completed');
                        }
                    } else {
                        Log::error('Donation not found for reference: ' . $reference);
                        $this->dispatch('show-toast', [
                            'type' => 'error',
                            'message' => 'Donation record not found.'
                        ]);
                    }
                } else {
                    Log::error('Paystack payment status is not success: ' . $paymentData['status']);
                    $this->dispatch('show-toast', [
                        'type' => 'error',
                        'message' => 'Payment verification failed: ' . $paymentData['status']
                    ]);
                }
            } else {
                Log::error('Paystack API request failed');
                $this->dispatch('show-toast', [
                    'type' => 'error',
                    'message' => 'Unable to verify payment with payment provider.'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Payment verification exception: ' . $e->getMessage());
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'An error occurred during verification.'
            ]);
        }
    }

    public function render()
    {
        return view('livewire.home.project-donations');
    }
}
