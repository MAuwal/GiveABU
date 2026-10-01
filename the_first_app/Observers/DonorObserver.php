<?php

namespace App\Observers;

use App\Models\Donor;
use App\Models\DonorSession;
use App\Mail\AlumniWelcomeMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class DonorObserver
{
    /**
     * Handle the Donor "created" event.
     */
    public function created(Donor $donor): void
    {
        // Session creation and welcome mail are now handled explicitly 
        // in Api\DonorsController@store for consistency and security.
    }

    /**
     * Handle the Donor "updated" event.
     */
    public function updated(Donor $donor): void
    {
        //
    }

    /**
     * Handle the Donor "deleted" event.
     */
    public function deleted(Donor $donor): void
    {
        //
    }

    /**
     * Handle the Donor "restored" event.
     */
    public function restored(Donor $donor): void
    {
        //
    }

    /**
     * Handle the Donor "force deleted" event.
     */
    public function forceDeleted(Donor $donor): void
    {
        //
    }
}
