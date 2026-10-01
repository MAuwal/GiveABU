<?php

namespace App\Livewire\Home;

use Livewire\Component;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Donation;
use App\Models\DonorSession;
use App\Models\Donor;

class MakeDonationArea extends Component
{
    public $amount;
    public $email;
    public $name;
    public $phone;

    protected $listeners = ['payment-success' => 'verifyPayment'];

    public function mount()
    {
        $this->checkAuth();
    }

    public function checkAuth()
    {
        if (Session::has('donor_token')) {
            $token        = Session::get('donor_token');
            $donorSession = app(\App\Services\DonorTokenService::class)->resolve($token)?->load('donor');

            if ($donorSession && $donorSession->donor) {
                $this->email = $donorSession->donor->email;
                $this->name  = $donorSession->donor->name;
                $this->phone = $donorSession->donor->phone;
            } elseif ($donorSession) {
                $this->email = $donorSession->username;
            }
        }
    }

    public function payWithPaystack()
    {
        $this->validate([
            'amount' => 'required|numeric|min:100',
            'email'  => 'required|email',
            'phone' => ['required', 'string', 'max:30', 'regex:/\A\+?[0-9 ()-]{7,30}\z/'],
        ]);

        $reference = 'ABU_' . time() . '_' . uniqid();
        $donorId   = $this->resolveDonorId();

        $donation = Donation::create([
            'receipt_phone' => $this->phone,
            'donor_id'          => $donorId,
            'amount'            => $this->amount,
            'type'              => 'endowment',
            'frequency'         => 'onetime',
            'endowment'         => 'yes',
            'status'            => 'pending',
            'payment_reference' => $reference,
        ]);

        $this->dispatch('initiate-paystack', [
            'key'      => config('services.paystack.public_key'),
            'email'    => $this->email,
            'amount'   => $this->amount * 100, // kobo
            'ref'      => $reference,
            'currency' => 'NGN',
            'metadata' => [
                'donation_id'   => $donation->id,
                'custom_fields' => [[
                    'display_name'  => 'Donation Type',
                    'variable_name' => 'donation_type',
                    'value'         => 'Endowment',
                ]],
            ],
        ]);
    }

    public function payWithSquad()
    {
        $this->validate([
            'amount' => 'required|numeric|min:100',
            'email'  => 'required|email',
            'phone' => ['required', 'string', 'max:30', 'regex:/\A\+?[0-9 ()-]{7,30}\z/'],
        ]);

        // JS handler calls /api/squad/pay and redirects to Squad checkout page
        $this->dispatch('initiate-squad', [
            'email'         => $this->email,
            'amount'        => $this->amount,
            'phone' => $this->phone,
            'customer_name' => $this->name ?? '',
        ]);
    }

    public function payWithInterswitch()
    {
        $this->validate([
            'amount' => 'required|numeric|min:100',
            'email'  => 'required|email',
            'phone' => ['required', 'string', 'max:30', 'regex:/\A\+?[0-9 ()-]{7,30}\z/'],
        ]);

        $this->dispatch('initiate-interswitch', [
            'email'         => $this->email,
            'amount'        => $this->amount,
            'phone' => $this->phone,
            'customer_name' => $this->name ?? '',
        ]);
    }

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
                $txData = $response->json()['data'];

                if ($txData['status'] === 'success') {
                    $donation = Donation::where('payment_reference', $reference)->first();

                    if ($donation) {
                        \Illuminate\Support\Facades\DB::transaction(function () use ($donation) {
                            $donation->update([
                                'status'      => 'completed',
                                'verified_at' => now(),
                                'paid_at'     => now(),
                            ]);
                            app(\App\Services\PaymentNotificationOutbox::class)->enqueue($donation, 'paystack');
                        });
                        $this->dispatch('donation-completed');
                    }

                    return redirect()->route('donation.thankyou.paystack', ['ref' => $reference]);
                }

                session()->flash('error', 'Payment could not be verified. Please contact support.');
            }
        } catch (\Exception $e) {
            Log::error('Paystack verification error: ' . $e->getMessage());
            session()->flash('error', 'An error occurred during verification.');
        }
    }

    private function resolveDonorId(): ?int
    {
        if (Session::has('donor_token')) {
            $session = app(\App\Services\DonorTokenService::class)->resolve(Session::get('donor_token'));
            if ($session?->donor_id) {
                return $session->donor_id;
            }
        }

        return Donor::firstOrCreate(
            ['email' => $this->email],
            ['name' => 'Guest', 'surname' => 'Donor', 'donor_type' => 'supporter']
        )->id;
    }

    public function render()
    {
        return view('livewire.home.make-donation-area');
    }
}
