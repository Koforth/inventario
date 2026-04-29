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
        Gate::before(fn (User $user) => $user->hasRole('super-admin') ? true : null);

        $permissions = [
            'catalog.view',
            'products.create',
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
            'reports.view',
            'technicians.manage',
            'workshop.manage',
            'users.manage',
            'roles.manage',
            'settings.manage',
            'records.delete',
            // legacy compatibility
            'view-catalog',
            'create-products',
            'manage-products',
            'manage-inventory',
            'manage-quotes',
            'manage-purchases',
            'manage-cash',
            'view-reports',
            'manage-users',
        ];

        foreach ($permissions as $permission) {
            Gate::define($permission, fn (User $user) => $user->hasPermission($permission));
        }
    }
}
