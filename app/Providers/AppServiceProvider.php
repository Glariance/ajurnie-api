<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\URL;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
        // ResetPassword::toMailUsing(function ($notifiable, $token) {
        //     $spaUrl = config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:3000'));
        //     $url = $spaUrl . '/reset-password?token=' . urlencode($token) . '&email=' . urlencode($notifiable->getEmailForPasswordReset());

        //     return (new \Illuminate\Notifications\Messages\MailMessage)
        //         ->subject('Reset Password Notification')
        //         ->line('You requested a password reset.')
        //         ->action('Reset Password', $url)
        //         ->line('If you did not request a password reset, no further action is required.');
        // });


    }
}
