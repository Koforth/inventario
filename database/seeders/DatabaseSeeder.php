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
        $permissions = [
            'catalog.view' => 'Ver catalogo',
            'products.create' => 'Crear productos',
            'products.manage' => 'Gestionar productos',
            'warehouse.manage' => 'Gestionar almacen',
            'inventory.entries.manage' => 'Gestionar entradas de inventario',
            'kardex.manage' => 'Gestionar kardex',
            'customers.manage' => 'Gestionar clientes',
            'sales.manage' => 'Gestionar ventas',
            'cash.manage' => 'Gestionar caja',
            'quotes.manage' => 'Gestionar cotizaciones',
            'layaways.manage' => 'Gestionar apartados',
            'receipts.manage' => 'Gestionar comprobantes',
            'purchases.manage' => 'Gestionar compras',
            'reports.view' => 'Ver reportes',
            'technicians.manage' => 'Gestionar tecnicos',
            'workshop.manage' => 'Gestionar taller',
            'users.manage' => 'Gestionar usuarios',
            'roles.manage' => 'Gestionar roles y permisos',
            'settings.manage' => 'Gestionar configuraciones sensibles',
            'records.delete' => 'Eliminar registros',
            // legacy compatibility
            'view-catalog' => 'Legacy: Ver catalogo',
            'create-products' => 'Legacy: Crear productos',
            'manage-products' => 'Legacy: Gestionar productos',
            'manage-inventory' => 'Legacy: Gestionar inventario',
            'manage-quotes' => 'Legacy: Gestionar cotizaciones',
            'manage-purchases' => 'Legacy: Gestionar compras',
            'manage-cash' => 'Legacy: Gestionar caja',
            'view-reports' => 'Legacy: Ver reportes',
            'manage-users' => 'Legacy: Gestionar usuarios',
        ];

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
                    'catalog.view', 'products.create', 'products.manage', 'warehouse.manage', 'reports.view',
                    'customers.manage', 'sales.manage', 'cash.manage', 'purchases.manage', 'quotes.manage',
                    'technicians.manage', 'workshop.manage', 'layaways.manage', 'kardex.manage', 'receipts.manage',
                    'inventory.entries.manage',
                    'view-catalog', 'create-products', 'manage-products', 'manage-inventory', 'manage-quotes',
                    'manage-purchases', 'manage-cash', 'view-reports',
                ],
            ],
            'vendedor-cajero' => [
                'label' => 'Vendedor / Cajero',
                'permissions' => [
                    'customers.manage', 'sales.manage', 'cash.manage', 'quotes.manage', 'layaways.manage', 'receipts.manage',
                    'manage-inventory', 'manage-quotes', 'manage-cash',
                ],
            ],
            'bodega-inventario' => [
                'label' => 'Bodega / Inventario',
                'permissions' => [
                    'catalog.view', 'products.create', 'products.manage', 'warehouse.manage', 'inventory.entries.manage', 'kardex.manage', 'purchases.manage',
                    'view-catalog', 'create-products', 'manage-products', 'manage-purchases',
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
                    'cash.manage', 'reports.view', 'sales.manage', 'purchases.manage', 'receipts.manage',
                    'manage-cash', 'view-reports', 'manage-purchases',
                ],
            ],
            'gerencia' => [
                'label' => 'Gerencia',
                'permissions' => [
                    'reports.view', 'sales.manage', 'purchases.manage', 'cash.manage', 'kardex.manage', 'customers.manage',
                    'view-reports',
                ],
            ],
            'invitado' => [
                'label' => 'Invitado',
                'permissions' => ['catalog.view', 'view-catalog'],
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
                'role' => 'super-admin',
            ]
        );

        $admin->roles()->syncWithoutDetaching([Role::where('name', 'super-admin')->value('id')]);

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
