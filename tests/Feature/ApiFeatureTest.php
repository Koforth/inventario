<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $permissions = [
            'catalog.view', 'products.create', 'products.manage', 'warehouse.manage',
            'customers.manage', 'sales.manage', 'cash.manage', 'purchases.manage',
            'quotes.manage', 'layaways.manage', 'receipts.manage', 'reports.manage',
            'settings.manage', 'users.manage', 'kardex.manage', 'inventory.entries.manage',
            'records.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(['name' => $permission], ['label' => $permission]);
        }

        $superAdminRole = Role::updateOrCreate(['name' => 'super-admin'], ['label' => 'Super Admin']);
        $superAdminRole->permissions()->sync(Permission::pluck('id'));

        User::updateOrCreate(
            ['email' => 'admin@test.com'],
            ['name' => 'Admin Test', 'password' => 'password123', 'role' => 'super-admin']
        )->roles()->sync([$superAdminRole->id]);
    }

    public function test_login_api_returns_token(): void
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => 'admin@test.com',
            'password' => 'password123',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['token', 'user']);
    }

    public function test_login_api_rejects_invalid_credentials(): void
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => 'admin@test.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('email');
    }

    public function test_protected_api_requires_token(): void
    {
        $response = $this->getJson('/api/v1/products');

        $response->assertUnauthorized();
    }

    public function test_products_api_returns_catalog(): void
    {
        $token = $this->loginAndGetToken();

        $response = $this->withToken($token)->getJson('/api/v1/products');

        $response->assertOk();
    }

    public function test_dashboard_api_returns_stats(): void
    {
        $token = $this->loginAndGetToken();

        $response = $this->withToken($token)->getJson('/api/v1/dashboard');

        $response->assertOk()->assertJsonStructure(['stats', 'charts']);
    }

    public function test_categories_api_crud(): void
    {
        $token = $this->loginAndGetToken();

        $response = $this->withToken($token)->postJson('/api/v1/categories', [
            'nombre' => 'Celulares',
        ]);

        $response->assertCreated()->assertJson(['nombre' => 'Celulares']);

        $categoryId = $response->json('id');

        $this->withToken($token)->getJson('/api/v1/categories')->assertOk();

        $this->withToken($token)->putJson("/api/v1/categories/{$categoryId}", [
            'nombre' => 'Accesorios',
        ])->assertOk()->assertJson(['nombre' => 'Accesorios']);
    }

    public function test_report_api_movements(): void
    {
        $token = $this->loginAndGetToken();

        $this->withToken($token)->getJson('/api/v1/reports/movements')->assertOk();
        $this->withToken($token)->getJson('/api/v1/reports/low-stock')->assertOk();
        $this->withToken($token)->getJson('/api/v1/reports/top-selling')->assertOk();
        $this->withToken($token)->getJson('/api/v1/reports/debtors')->assertOk();
        $this->withToken($token)->getJson('/api/v1/reports/sales')->assertOk();
    }

    public function test_backup_status_api(): void
    {
        $token = $this->loginAndGetToken();

        $this->withToken($token)->getJson('/api/v1/backup/status')->assertOk()
            ->assertJsonStructure(['backups', 'tables', 'storage']);
    }

    private function loginAndGetToken(): string
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => 'admin@test.com',
            'password' => 'password123',
        ]);

        return $response->json('token');
    }
}