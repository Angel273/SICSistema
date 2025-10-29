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
        Schema::table('product_stocks', function (Blueprint $table) {
            $table->foreign(['product_id'], 'fk_ps_prod')->references(['id'])->on('products')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign(['warehouse_id'], 'fk_ps_wh')->references(['id'])->on('warehouses')->onUpdate('cascade')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_stocks', function (Blueprint $table) {
            $table->dropForeign('fk_ps_prod');
            $table->dropForeign('fk_ps_wh');
        });
    }
};
