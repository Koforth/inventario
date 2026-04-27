<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
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
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Administrador',
                'password' => 'password',
                'role' => 'admin',
            ]
        );

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
