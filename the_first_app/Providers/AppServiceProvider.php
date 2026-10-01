<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            return config('app.frontend_url')."/password-reset/$token?email={$notifiable->getEmailForPasswordReset()}";
        });

        \App\Models\Donor::observe(\App\Observers\DonorObserver::class);

        $this->ensureStorageDirectories();
    }

    private function ensureStorageDirectories(): void
    {
        $dirs = [
            public_path('storage/projects/icons'),    // public disk root = public/storage
            public_path('storage/projects/photos'),
            storage_path('app/private/livewire-tmp'), // Livewire temp: local disk root = app/private
            storage_path('app/livewire-tmp'),          // fallback for older configs
        ];

        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0775, true);
            }
        }
    }
}
