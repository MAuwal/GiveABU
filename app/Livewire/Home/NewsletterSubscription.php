<?php

namespace App\Livewire\Home;

use Livewire\Component;
use App\Models\Newsletter;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewsletterWelcome;

class NewsletterSubscription extends Component
{
    public $email;
    public $successMessage = false;

    protected $rules = [
        'email' => 'required|email|unique:newsletters,email',
    ];

    public function subscribe()
    {
        $this->validate();

        Newsletter::create([
            'email' => $this->email,
            'is_active' => true,
        ]);

        // Send welcome email
        try {
            Mail::to($this->email)->send(new NewsletterWelcome());
        } catch (\Exception $e) {
            // Log error but don't stop the process
            \Log::error('Newsletter email failed: ' . $e->getMessage());
        }

        $this->successMessage = true;
        $this->email = '';

        // Hide success message after 3 seconds
        $this->dispatch('newsletter-subscribed');
    }

    public function render()
    {
        return view('livewire.home.newsletter-subscription');
    }
}
