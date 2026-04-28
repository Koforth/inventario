<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('label');
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('label');
            $table->timestamps();
        });

        Schema::create('permission_role', function (Blueprint $table) {
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['permission_id', 'role_id']);
        });

        Schema::create('role_user', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['role_id', 'user_id']);
        });

        $this->seedAccessControl();
        $this->migrateExistingUserRoles();
    }

    public function down(): void
    {
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }

    private function seedAccessControl(): void
    {
        $now = now();

        $roles = [
            'admin' => 'Administrador',
            'empleado' => 'Empleado',
            'invitado' => 'Invitado',
        ];

        foreach ($roles as $name => $label) {
            DB::table('roles')->insert([
                'name' => $name,
                'label' => $label,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $permissions = [
            'view-catalog' => 'Ver catalogo',
            'manage-products' => 'Administrar productos',
            'manage-inventory' => 'Administrar inventario',
            'view-reports' => 'Ver reportes',
            'manage-users' => 'Administrar usuarios',
        ];

        foreach ($permissions as $name => $label) {
            DB::table('permissions')->insert([
                'name' => $name,
                'label' => $label,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $rolePermissions = [
            'admin' => ['view-catalog', 'manage-products', 'manage-inventory', 'view-reports', 'manage-users'],
            'empleado' => ['view-catalog', 'manage-inventory'],
            'invitado' => ['view-catalog'],
        ];

        foreach ($rolePermissions as $roleName => $permissionNames) {
            $roleId = DB::table('roles')->where('name', $roleName)->value('id');

            foreach ($permissionNames as $permissionName) {
                DB::table('permission_role')->insert([
                    'role_id' => $roleId,
                    'permission_id' => DB::table('permissions')->where('name', $permissionName)->value('id'),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    private function migrateExistingUserRoles(): void
    {
        if (! Schema::hasColumn('users', 'role')) {
            return;
        }

        $now = now();
        $fallbackRoleId = DB::table('roles')->where('name', 'invitado')->value('id');

        DB::table('users')
            ->select(['id', 'role'])
            ->orderBy('id')
            ->each(function ($user) use ($fallbackRoleId, $now) {
                $roleId = DB::table('roles')
                    ->where('name', $user->role ?: 'invitado')
                    ->value('id') ?? $fallbackRoleId;

                DB::table('role_user')->insertOrIgnore([
                    'role_id' => $roleId,
                    'user_id' => $user->id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            });
    }
};
