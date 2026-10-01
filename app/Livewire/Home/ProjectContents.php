<?php

namespace App\Livewire\Home;

use Livewire\Component;
use App\Models\Project;
use App\Models\Donation;
use Illuminate\Support\Facades\Session;
use App\Models\DonorSession;
use App\Models\Donor;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProjectContents extends Component
{
    public $project;
    public $otherProjects;
    
    // Donation properties
    public $showModal = false;
    public $amount = 1000;
    public $customAmount;
    public $selectedAmount = 1000;
    public $email;
    public $name;
    public $phone;
    public $paymentReference; // Store reference for manual verification
    
    // Gallery properties
    public $showImageGallery = false;
    public $galleryProject = null;

    // protected $listeners = ['project-payment-success' => 'verifyPayment']; // Removed in favor of attribute

    public function mount($project)
    {
        $this->project = $project;
        $this->loadOtherProjects();
        $this->checkAuth();
    }
    
    public function loadOtherProjects()
    {
        $this->otherProjects = Project::where('id', '!=', $this->project->id)
                                      ->take(3)
                                      ->get();
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

    public function openDonationModal()
    {
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['amount', 'customAmount', 'selectedAmount']);
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

        $this->dispatch('initiate-squad', [
            'email'         => $this->email,
            'amount'        => $this->amount,
            'customer_name' => '',
            'project_id'    => $this->project->id,
        ]);
    }

    public function payWithInterswitch()
    {
        $this->validate([
            'amount' => 'required|numeric|min:100',
            'email'  => 'required|email',
        ]);

        $this->dispatch('initiate-interswitch', [
            'email'         => $this->email,
            'amount'        => $this->amount,
            'customer_name' => '',
            'project_id'    => $this->project->id,
        ]);
    }

    public function donate()
    {
        $this->validate([
            'amount' => 'required|numeric|min:100',
            'email' => 'required|email',
        ]);

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
            'project_id' => $this->project->id,
            'amount' => $this->amount,
            'type' => 'project',
            'frequency' => 'onetime',
            'endowment' => 'no',
            'status' => 'pending',
            'payment_reference' => $reference,
        ]);
        
        // Dispatch event to frontend to open Paystack
        $this->dispatch('initiate-paystack', [
            'key' => config('services.paystack.public_key'),
            'email' => $this->email,
            'amount' => $this->amount * 100, // In kobo
            'ref' => $reference,
            'currency' => 'NGN',
            'metadata' => [
                'donation_id' => $donation->id,
                'project_id' => $this->project->id,
                'project_name' => $this->project->project_title,
                'custom_fields' => [
                    [
                        'display_name' => "Project",
                        'variable_name' => "project_name",
                        'value' => $this->project->project_title
                    ]
                ]
            ]
        ]);
    }

    #[\Livewire\Attributes\On('project-payment-success')]
    public function verifyPayment($data = null)
    {
        Log::info('verifyPayment called in ProjectContents', ['data' => $data]);

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
                            app(\App\Services\ProjectFundingService::class)->rebuild($this->project->id);
                            $this->project->refresh();
                            $totalRaised = $this->project->raised;
                            Log::info('Project raised amount updated to: ' . $totalRaised);
                            
                            $this->showModal = false;
                            $this->dispatch('close-donation-modal');
                            
                            $this->dispatch('show-toast', [
                                'type' => 'success',
                                'message' => 'Thank you for your donation! Your support means the world to us.'
                            ]);
                            
                            // Reset form fields
                            $this->reset(['amount', 'customAmount', 'selectedAmount', 'paymentReference']);
                            
                            // Refresh page to show updated raised amount
                            // return redirect()->route('project.single', ['id' => $this->project->id]); // Removed redirect to keep modal/toast flow consistent
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
    
    public function openImageGallery($projectId)
    {
        // For other projects section
        $this->galleryProject = Project::with('photos')->find($projectId);
        $this->showImageGallery = true;
    }

    public function closeImageGallery()
    {
        $this->showImageGallery = false;
        $this->galleryProject = null;
    }

    public function render()
    {
        return view('livewire.home.project-contents');
    }
}
