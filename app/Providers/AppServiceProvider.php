<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        \Illuminate\Support\Facades\RateLimiter::for('payment-receipts', function () {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(
                max(1, (int) config('hardening.receipts_per_minute', 60))
            )->by('payment-receipts-global');
        });
        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            return route('password.reset', ['token' => $token, 'email' => $notifiable->getEmailForPasswordReset()]);
        });

        \Livewire\Livewire::addPersistentMiddleware([\App\Http\Middleware\RequireRole::class]);

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
