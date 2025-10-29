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
        Schema::table('warehouses', function (Blueprint $table) {
            $table->foreign(['store_id'], 'fk_warehouses_store')->references(['id'])->on('stores')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign(['store_id'], 'fk_wh_store')->references(['id'])->on('stores')->onUpdate('cascade')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropForeign('fk_warehouses_store');
            $table->dropForeign('fk_wh_store');
        });
    }
};
