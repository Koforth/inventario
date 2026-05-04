<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Permission;
use App\Models\ReceiptType;
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
        $modulePermissions = [
            'products.manage' => 'Gestionar modulo de productos',
            'warehouse.manage' => 'Gestionar modulo de almacen',
            'inventory.entries.manage' => 'Gestionar modulo de entradas de inventario',
            'kardex.manage' => 'Gestionar modulo de kardex',
            'customers.manage' => 'Gestionar modulo de clientes',
            'sales.manage' => 'Gestionar modulo de ventas',
            'cash.manage' => 'Gestionar modulo de caja',
            'quotes.manage' => 'Gestionar modulo de cotizaciones',
            'layaways.manage' => 'Gestionar modulo de apartados',
            'receipts.manage' => 'Gestionar modulo de comprobantes',
            'purchases.manage' => 'Gestionar modulo de compras',
            'reports.manage' => 'Gestionar modulo de reportes',
            'technicians.manage' => 'Gestionar modulo de tecnicos',
            'workshop.manage' => 'Gestionar modulo de taller',
            'users.manage' => 'Gestionar modulo de usuarios',
        ];

        $additionalPermissions = [
            'catalog.view' => 'Ver catalogo',
            'products.create' => 'Crear productos',
            'roles.manage' => 'Gestionar roles sensibles',
            'settings.manage' => 'Gestionar configuraciones sensibles',
            'records.delete' => 'Eliminar registros',
        ];

        $permissions = array_merge($modulePermissions, $additionalPermissions);

        foreach ($permissions as $name => $label) {
            Permission::updateOrCreate(['name' => $name], ['label' => $label]);
        }

        $roles = [
            'super-admin' => [
                'label' => 'Super Admin',
                'permissions' => array_keys($permissions),
            ],
            'administrador' => [
                'label' => 'Administrador',
                'permissions' => [
                    'catalog.view', 'products.create', 'products.manage', 'warehouse.manage',
                    'customers.manage', 'sales.manage', 'cash.manage', 'purchases.manage', 'quotes.manage',
                    'technicians.manage', 'workshop.manage', 'layaways.manage', 'kardex.manage', 'receipts.manage',
                    'inventory.entries.manage',
                ],
            ],
            'vendedor-cajero' => [
                'label' => 'Vendedor / Cajero',
                'permissions' => [
                    'customers.manage', 'sales.manage', 'cash.manage', 'quotes.manage', 'layaways.manage', 'receipts.manage',
                ],
            ],
            'bodega-inventario' => [
                'label' => 'Bodega / Inventario',
                'permissions' => [
                    'catalog.view', 'products.create', 'products.manage', 'warehouse.manage', 'inventory.entries.manage', 'kardex.manage', 'purchases.manage',
                ],
            ],
            'tecnico' => [
                'label' => 'Tecnico',
                'permissions' => [
                    'technicians.manage', 'workshop.manage', 'customers.manage', 'receipts.manage',
                ],
            ],
            'contabilidad-caja' => [
                'label' => 'Contabilidad / Caja',
                'permissions' => [
                    'cash.manage', 'sales.manage', 'purchases.manage', 'receipts.manage',
                ],
            ],
            'gerencia' => [
                'label' => 'Gerencia',
                'permissions' => [
                    'sales.manage', 'purchases.manage', 'cash.manage', 'kardex.manage', 'customers.manage',
                ],
            ],
            'invitado' => [
                'label' => 'Invitado',
                'permissions' => ['catalog.view'],
            ],
        ];

        foreach ($roles as $name => $roleData) {
            $role = Role::updateOrCreate(['name' => $name], ['label' => $roleData['label']]);
            $role->permissions()->sync(Permission::whereIn('name', $roleData['permissions'])->pluck('id'));
        }

        $adminPassword = env('ADMIN_PASSWORD');

        if ($adminPassword) {
            $admin = User::updateOrCreate(
                ['email' => env('ADMIN_EMAIL', 'admin@example.com')],
                [
                    'name' => env('ADMIN_NAME', 'Administrador'),
                    'password' => $adminPassword,
                    'role' => 'super-admin',
                ]
            );

            $admin->roles()->syncWithoutDetaching([Role::where('name', 'super-admin')->value('id')]);
        }

        foreach (['Cocina', 'Limpieza', 'Bano', 'Dormitorio', 'Sala'] as $category) {
            Category::updateOrCreate(
                ['nombre' => $category],
                ['descripcion' => "Productos para {$category}"]
            );
        }

        foreach (['Generica', 'HomeCare', 'CasaPlus', 'DuraHogar'] as $brand) {
            Brand::updateOrCreate(['nombre' => $brand]);
        }

        $receiptTypes = [
            ['name' => 'FACTURA', 'code' => 'factura', 'prefix' => 'F001'],
            ['name' => 'BOLETA', 'code' => 'boleta', 'prefix' => 'B001'],
            ['name' => 'TICKET', 'code' => 'ticket', 'prefix' => 'T001'],
        ];

        foreach ($receiptTypes as $receiptType) {
            ReceiptType::updateOrCreate(
                ['code' => strtoupper($receiptType['code'])],
                [
                    'name' => $receiptType['name'],
                    'code' => strtoupper($receiptType['code']),
                    'prefix' => $receiptType['prefix'],
                    'current_number' => 0,
                    'padding' => 8,
                    'is_active' => true,
                ]
            );
        }
    }
}
