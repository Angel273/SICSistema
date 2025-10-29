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
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->foreign(['purchase_id'], 'fk_pi_p')->references(['id'])->on('purchases')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['product_id'], 'fk_pi_prod')->references(['id'])->on('products')->onUpdate('cascade')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->dropForeign('fk_pi_p');
            $table->dropForeign('fk_pi_prod');
        });
    }
};
