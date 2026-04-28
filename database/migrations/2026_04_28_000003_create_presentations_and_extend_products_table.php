<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('presentations', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->string('descripcion')->nullable();
            $table->timestamps();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('presentation_id')->nullable()->after('brand_id')->constrained('presentations')->nullOnDelete()->cascadeOnUpdate();
            $table->decimal('purchase_price', 10, 2)->default(0)->after('precio');
            $table->decimal('sale_price_1', 10, 2)->default(0)->after('purchase_price');
            $table->decimal('sale_price_2', 10, 2)->nullable()->after('sale_price_1');
            $table->decimal('sale_price_3', 10, 2)->nullable()->after('sale_price_2');
            $table->boolean('taxable')->default(false)->after('sale_price_3');
            $table->boolean('perishable')->default(false)->after('taxable');
            $table->boolean('inventoryable')->default(true)->after('perishable');
            $table->date('expires_at')->nullable()->after('inventoryable');
        });

        DB::table('presentations')->insert([
            'nombre' => 'Unidad',
            'descripcion' => 'Venta por unidad',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $presentationId = DB::table('presentations')->where('nombre', 'Unidad')->value('id');

        DB::table('products')->update([
            'presentation_id' => $presentationId,
            'sale_price_1' => DB::raw('precio'),
        ]);
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('presentation_id');
            $table->dropColumn([
                'purchase_price',
                'sale_price_1',
                'sale_price_2',
                'sale_price_3',
                'taxable',
                'perishable',
                'inventoryable',
                'expires_at',
            ]);
        });

        Schema::dropIfExists('presentations');
    }
};
