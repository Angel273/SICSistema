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
        Schema::create('warehouses', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('store_id')->index('idx_wh_store');
            $table->string('name', 100)->index('idx_wh_name');
            $table->string('address')->nullable();
            $table->string('code', 20)->unique('code');

            $table->index(['store_id'], 'store_id');
            $table->unique(['code'], 'uq_warehouses_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouses');
    }
};
