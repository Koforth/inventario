<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('product_supplier', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['product_id', 'supplier_id']);
        });

        $this->migrateExistingSuppliers();
    }

    public function down(): void
    {
        Schema::dropIfExists('product_supplier');
        Schema::dropIfExists('suppliers');
    }

    private function migrateExistingSuppliers(): void
    {
        if (! Schema::hasColumn('products', 'proveedor')) {
            return;
        }

        $now = now();

        DB::table('products')
            ->whereNotNull('proveedor')
            ->where('proveedor', '!=', '')
            ->select(['id', 'proveedor'])
            ->orderBy('id')
            ->each(function ($product) use ($now) {
                $supplierNames = collect(explode(',', $product->proveedor))
                    ->map(fn ($name) => trim($name))
                    ->filter()
                    ->unique(fn ($name) => mb_strtolower($name));

                foreach ($supplierNames as $supplierName) {
                    $supplier = DB::table('suppliers')->where('name', $supplierName)->first();

                    if (! $supplier) {
                        $supplierId = DB::table('suppliers')->insertGetId([
                            'name' => $supplierName,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                    } else {
                        $supplierId = $supplier->id;
                    }

                    DB::table('product_supplier')->insertOrIgnore([
                        'product_id' => $product->id,
                        'supplier_id' => $supplierId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            });
    }
};
