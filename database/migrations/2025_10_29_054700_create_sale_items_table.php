<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sale_items', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('sale_id')->index('sale_id');
            $table->integer('product_id')->index('product_id');
            $table->decimal('qty', 12, 3);
            $table->decimal('unit_price', 12);
            $table->decimal('discount', 12)->default(0);
            $table->decimal('tax_rate', 5)->default(13);
            $table->decimal('line_subtotal', 12);
            $table->decimal('line_tax', 12);
            $table->decimal('line_total', 12);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_items');
    }
};
