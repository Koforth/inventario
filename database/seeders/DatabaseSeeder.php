<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $permissions = [
            'view-catalog' => 'Ver catalogo',
            'create-products' => 'Agregar productos',
            'manage-products' => 'Administrar productos',
            'manage-inventory' => 'Administrar inventario',
            'manage-quotes' => 'Administrar cotizaciones',
            'manage-purchases' => 'Administrar compras',
            'manage-cash' => 'Administrar caja',
            'view-reports' => 'Ver reportes',
            'manage-users' => 'Administrar usuarios',
        ];

        foreach ($permissions as $name => $label) {
            Permission::updateOrCreate(['name' => $name], ['label' => $label]);
        }

        $roles = [
            'admin' => [
                'label' => 'Administrador',
                'permissions' => array_keys($permissions),
            ],
            'empleado' => [
                'label' => 'Empleado',
                'permissions' => ['view-catalog', 'manage-inventory', 'manage-quotes', 'manage-purchases', 'manage-cash'],
            ],
            'agregar-productos' => [
                'label' => 'Agregar productos',
                'permissions' => ['view-catalog', 'create-products'],
            ],
            'invitado' => [
                'label' => 'Invitado',
                'permissions' => ['view-catalog'],
            ],
        ];

        foreach ($roles as $name => $roleData) {
            $role = Role::updateOrCreate(['name' => $name], ['label' => $roleData['label']]);
            $role->permissions()->sync(Permission::whereIn('name', $roleData['permissions'])->pluck('id'));
        }

        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Administrador',
                'password' => 'password',
                'role' => 'admin',
            ]
        );

        $admin->roles()->syncWithoutDetaching([Role::where('name', 'admin')->value('id')]);

        foreach (['Cocina', 'Limpieza', 'Bano', 'Dormitorio', 'Sala'] as $category) {
            Category::updateOrCreate(
                ['nombre' => $category],
                ['descripcion' => "Productos para {$category}"]
            );
        }

        foreach (['Generica', 'HomeCare', 'CasaPlus', 'DuraHogar'] as $brand) {
            Brand::updateOrCreate(['nombre' => $brand]);
        }
    }
}
