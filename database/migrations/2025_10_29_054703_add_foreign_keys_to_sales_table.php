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
        Schema::table('sales', function (Blueprint $table) {
            $table->foreign(['customer_id'], 'fk_s_cust')->references(['id'])->on('customers')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign(['store_id'], 'fk_s_store')->references(['id'])->on('stores')->onUpdate('cascade')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign('fk_s_cust');
            $table->dropForeign('fk_s_store');
        });
    }
};
