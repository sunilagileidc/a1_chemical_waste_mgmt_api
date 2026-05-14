<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->registerPolicies();

        // Access token expiry (8 hours)
        Passport::tokensExpireIn(now()->addHours(8));

        // Refresh token expiry (7 days)
        Passport::refreshTokensExpireIn(now()->addDays(7));
    }
}