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
        Schema::table('sale_items', function (Blueprint $table) {
            $table->foreign(['product_id'], 'fk_si_prod')->references(['id'])->on('products')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign(['sale_id'], 'fk_si_s')->references(['id'])->on('sales')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropForeign('fk_si_prod');
            $table->dropForeign('fk_si_s');
        });
    }
};
