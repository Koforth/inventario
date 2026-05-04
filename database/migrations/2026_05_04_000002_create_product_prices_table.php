<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('label', 80);
            $table->decimal('amount', 12, 2);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->foreignId('product_price_id')->nullable()->after('product_id')->constrained('product_prices')->nullOnDelete();
        });

        $now = now();

        DB::table('products')
            ->select(['id', 'sale_price_1', 'sale_price_2', 'sale_price_3', 'precio'])
            ->orderBy('id')
            ->chunkById(100, function ($products) use ($now) {
                foreach ($products as $product) {
                    $prices = [
                        ['label' => 'Precio 1', 'amount' => (float) ($product->sale_price_1 ?: $product->precio ?: 0), 'sort_order' => 1],
                        ['label' => 'Precio 2', 'amount' => $product->sale_price_2, 'sort_order' => 2],
                        ['label' => 'Precio 3', 'amount' => $product->sale_price_3, 'sort_order' => 3],
                    ];

                    foreach ($prices as $price) {
                        if ($price['sort_order'] > 1 && ($price['amount'] === null || (float) $price['amount'] <= 0)) {
                            continue;
                        }

                        DB::table('product_prices')->insert([
                            'product_id' => $product->id,
                            'label' => $price['label'],
                            'amount' => (float) $price['amount'],
                            'sort_order' => $price['sort_order'],
                            'is_active' => true,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                    }
                }
            }, 'id');
    }

    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_price_id');
        });

        Schema::dropIfExists('product_prices');
    }
};
