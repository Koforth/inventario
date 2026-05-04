<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
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
        Schema::defaultStringLength(191);

        Gate::before(fn (User $user) => $user->hasRole('super-admin') ? true : null);

        $modulePermissions = [
            'products.manage',
            'warehouse.manage',
            'inventory.entries.manage',
            'kardex.manage',
            'customers.manage',
            'sales.manage',
            'cash.manage',
            'quotes.manage',
            'layaways.manage',
            'receipts.manage',
            'purchases.manage',
            'reports.manage',
            'technicians.manage',
            'workshop.manage',
            'users.manage',
        ];

        $additionalPermissions = [
            'catalog.view',
            'products.create',
            'roles.manage',
            'settings.manage',
            'records.delete',
        ];

        $permissions = array_merge($modulePermissions, $additionalPermissions);

        foreach ($permissions as $permission) {
            Gate::define($permission, fn (User $user) => $user->hasPermission($permission));
        }

    }
}
