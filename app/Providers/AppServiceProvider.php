<?php

namespace App\Providers;

use App\Support\PublicStorage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

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
        if (! app()->environment('testing')) {
            PublicStorage::ensureLinked();
        }

        if (! app()->runningInConsole()) {
            $request = request();
            $cfVisitor = (string) $request->headers->get('cf-visitor', '');
            $forwardedProto = $request->headers->get('x-forwarded-proto');

            if (
                str_ends_with($request->getHost(), '.trycloudflare.com') ||
                $forwardedProto === 'https' ||
                str_contains($cfVisitor, '"scheme":"https"')
            ) {
                URL::forceScheme('https');
            }
        }

        if (! app()->environment('local') && str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceRootUrl((string) config('app.url'));
            URL::forceScheme('https');
        }

        Password::defaults(fn () => Password::min(10)->letters()->mixedCase()->numbers());
    }
}
