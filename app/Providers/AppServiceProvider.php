<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

use App\Models\Company;
use App\Policies\CompanyPolicy;

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
        // 1. Admin & Owner universal access
        // Note: Both share the same features; checked globally via isAdmin()
        Gate::before(function ($user, $ability) {
            return $user->isAdmin() ? true : null;
        });

        // 2. Register Company Policy
        Gate::policy(Company::class, CompanyPolicy::class);
    }
}
