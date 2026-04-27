<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

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
        Gate::define('view-catalog', fn (?User $user = null) => true);
        Gate::define('manage-products', fn (User $user) => $user->isAdmin());
        Gate::define('view-reports', fn (User $user) => $user->isAdmin());
        Gate::define('manage-inventory', fn (User $user) => $user->isEmployee());
    }
}
