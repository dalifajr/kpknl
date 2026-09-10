<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\OAuth\SsoProvider;
use Laravel\Socialite\Contracts\Factory;

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
        if (config('app.env') !== 'production' || request()->server('HTTP_X_FORWARDED_PROTO') !== 'https') {
            \Illuminate\Support\Facades\URL::forceScheme('http');
        }

        $socialite = $this->app->make(Factory::class);
        $socialite->extend(
            'sso',
            function ($app) use ($socialite) {
                $config = $app['config']['services.sso'];
                return $socialite->buildProvider(SsoProvider::class, $config);
            }
        );
    }
}
