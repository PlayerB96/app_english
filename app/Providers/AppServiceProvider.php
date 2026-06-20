<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Auth::guard('web')->setRememberDuration((int) config('auth.remember_lifetime', 43_200));

        VerifyEmail::toMailUsing(function (object $notifiable, string $url): MailMessage {
            return (new MailMessage)
                ->subject('Verifica tu correo electrónico')
                ->greeting('Hola '.$notifiable->name.'!')
                ->line('Gracias por registrarte. Haz clic en el botón para verificar tu correo.')
                ->action('Verificar correo', $url)
                ->line('Si no creaste esta cuenta, ignora este mensaje.');
        });

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('api', function (Request $request) {
            return $request->user()
                ? Limit::perMinute(120)->by((string) $request->user()->getAuthIdentifier())
                : Limit::perMinute(30)->by($request->ip());
        });
    }
}
