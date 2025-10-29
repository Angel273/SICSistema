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
        Schema::create('kardex', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->integer('product_id');
            $table->integer('warehouse_id')->index('fk_k_wh');
            $table->enum('movement_type', ['IN', 'OUT', 'ADJ']);
            $table->decimal('qty', 12, 3);
            $table->decimal('unit_cost', 12);
            $table->string('ref_type', 40);
            $table->bigInteger('ref_id');
            $table->dateTime('occurred_at')->useCurrent();

            $table->index(['product_id', 'warehouse_id'], 'product_id');
            $table->index(['ref_type', 'ref_id'], 'ref_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kardex');
    }
};
