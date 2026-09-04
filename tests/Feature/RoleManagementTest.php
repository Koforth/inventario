<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['roles.manage', 'catalog.view', 'users.manage', 'sales.manage'] as $permission) {
            Permission::updateOrCreate(['name' => $permission], ['label' => $permission]);
        }

        $role = Role::updateOrCreate(['name' => 'super-admin'], ['label' => 'Super Admin']);
        $role->permissions()->sync(Permission::pluck('id'));

        User::updateOrCreate(
            ['email' => 'admin@test.com'],
            ['name' => 'Admin', 'password' => 'password123', 'role' => 'super-admin']
        )->roles()->sync([$role->id]);
    }

    public function test_roles_index_renders(): void
    {
        $admin = User::where('email', 'admin@test.com')->first();

        $this->actingAs($admin)
            ->get(route('roles.index'))
            ->assertOk()
            ->assertSee('Super Admin');
    }

    public function test_roles_create_form_renders(): void
    {
        $admin = User::where('email', 'admin@test.com')->first();

        $this->actingAs($admin)
            ->get(route('roles.create'))
            ->assertOk()
            ->assertSee('Permisos por modulo');
    }

    public function test_store_creates_role_with_permissions(): void
    {
        $admin = User::where('email', 'admin@test.com')->first();
        $permissions = Permission::whereIn('name', ['catalog.view', 'sales.manage'])->pluck('id')->all();

        $this->actingAs($admin)
            ->post(route('roles.store'), [
                'name' => 'jefe-ventas',
                'label' => 'Jefe de Ventas',
                'permissions' => $permissions,
            ])
            ->assertRedirect(route('roles.index'));

        $this->assertDatabaseHas('roles', ['name' => 'jefe-ventas', 'label' => 'Jefe de Ventas']);

        $role = Role::where('name', 'jefe-ventas')->first();
        $this->assertEqualsCanonicalizing($permissions, $role->permissions()->pluck('id')->all());
    }

    public function test_update_modifies_role_permissions(): void
    {
        $admin = User::where('email', 'admin@test.com')->first();
        $role = Role::create(['name' => 'consulta', 'label' => 'Consulta']);
        $catalogView = Permission::where('name', 'catalog.view')->value('id');
        $salesManage = Permission::where('name', 'sales.manage')->value('id');
        $role->permissions()->sync([$catalogView, $salesManage]);

        $this->actingAs($admin)
            ->from(route('roles.edit', $role))
            ->put(route('roles.update', $role), [
                'name' => 'consulta',
                'label' => 'Consulta avanzada',
                'permissions' => [$salesManage],
            ])
            ->assertRedirect(route('roles.index'));

        $role->refresh();

        $this->assertSame('Consulta avanzada', $role->label);
        $this->assertEqualsCanonicalizing([$salesManage], $role->permissions()->pluck('id')->all());
    }

    public function test_store_rejects_invalid_role_name(): void
    {
        $admin = User::where('email', 'admin@test.com')->first();

        $response = $this->actingAs($admin)
            ->from(route('roles.create'))
            ->post(route('roles.store'), [
                'name' => 'Nombre Invalido!',
                'label' => 'Rol invalido',
            ]);

        $response->assertSessionHasErrors('name');
        $this->assertDatabaseMissing('roles', ['label' => 'Rol invalido']);
    }

    public function test_destroy_blocks_super_admin(): void
    {
        $admin = User::where('email', 'admin@test.com')->first();
        $superAdmin = Role::where('name', 'super-admin')->first();

        $this->actingAs($admin)
            ->from(route('roles.index'))
            ->delete(route('roles.destroy', $superAdmin))
            ->assertSessionHasErrors('role');

        $this->assertDatabaseHas('roles', ['name' => 'super-admin']);
    }

    public function test_destroy_removes_role_without_users(): void
    {
        $admin = User::where('email', 'admin@test.com')->first();
        $role = Role::create(['name' => 'temporal', 'label' => 'Temporal']);

        $this->actingAs($admin)
            ->delete(route('roles.destroy', $role))
            ->assertRedirect(route('roles.index'));

        $this->assertDatabaseMissing('roles', ['name' => 'temporal']);
    }

    public function test_roles_requires_permission(): void
    {
        $role = Role::updateOrCreate(['name' => 'consulta'], ['label' => 'Consulta']);
        $role->permissions()->sync([Permission::where('name', 'catalog.view')->value('id')]);

        $user = User::updateOrCreate(
            ['email' => 'reader@test.com'],
            ['name' => 'Lector', 'password' => 'password123', 'role' => 'consulta']
        );
        $user->roles()->sync([$role->id]);

        $this->actingAs($user)
            ->get(route('roles.index'))
            ->assertForbidden();
    }
}