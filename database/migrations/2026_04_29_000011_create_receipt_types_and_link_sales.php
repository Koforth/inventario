<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receipt_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 20)->unique();
            $table->string('prefix', 20)->nullable();
            $table->unsignedBigInteger('current_number')->default(0);
            $table->unsignedInteger('padding')->default(8);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('receipt_type_id')->nullable()->after('receipt_type')->constrained('receipt_types')->nullOnDelete();
            $table->string('receipt_number')->nullable()->after('number');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropConstrainedForeignId('receipt_type_id');
            $table->dropColumn('receipt_number');
        });

        Schema::dropIfExists('receipt_types');
    }
};

