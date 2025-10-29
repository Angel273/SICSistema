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
        Schema::table('purchases', function (Blueprint $table) {
            $table->foreign(['supplier_id'], 'fk_p_sup')->references(['id'])->on('suppliers')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign(['warehouse_id'], 'fk_p_wh')->references(['id'])->on('warehouses')->onUpdate('cascade')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropForeign('fk_p_sup');
            $table->dropForeign('fk_p_wh');
        });
    }
};
