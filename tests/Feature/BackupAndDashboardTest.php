<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BackupAndDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['catalog.view', 'reports.manage', 'settings.manage', 'sales.manage', 'products.create'] as $permission) {
            Permission::updateOrCreate(['name' => $permission], ['label' => $permission]);
        }

        $role = Role::updateOrCreate(['name' => 'super-admin'], ['label' => 'Super Admin']);
        $role->permissions()->sync(Permission::pluck('id'));

        User::updateOrCreate(
            ['email' => 'admin@test.com'],
            ['name' => 'Admin', 'password' => 'password123', 'role' => 'super-admin']
        )->roles()->sync([$role->id]);
    }

    public function test_backup_page_renders(): void
    {
        $admin = User::where('email', 'admin@test.com')->first();

        $this->actingAs($admin)
            ->get(route('backup.index'))
            ->assertOk()
            ->assertSee('Backup y Restauracion');
    }

    public function test_dashboard_shows_charts_data(): void
    {
        $admin = User::where('email', 'admin@test.com')->first();

        $this->actingAs($admin)
            ->get(route('home'))
            ->assertOk();
    }

    public function test_reports_sales_page_renders(): void
    {
        $admin = User::where('email', 'admin@test.com')->first();

        $this->actingAs($admin)
            ->get(route('reports.sales'))
            ->assertOk();
    }

    public function test_reports_catalog_page_renders(): void
    {
        $admin = User::where('email', 'admin@test.com')->first();

        $this->actingAs($admin)
            ->get(route('reports.catalog'))
            ->assertOk();
    }

    public function test_reports_debtors_page_renders(): void
    {
        $admin = User::where('email', 'admin@test.com')->first();

        $this->actingAs($admin)
            ->get(route('reports.debtors'))
            ->assertOk();
    }

    public function test_reports_purchases_page_renders(): void
    {
        $admin = User::where('email', 'admin@test.com')->first();

        $this->actingAs($admin)
            ->get(route('reports.purchases'))
            ->assertOk();
    }
}