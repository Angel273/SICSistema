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
        Schema::table('receipts', function (Blueprint $table) {
            $table->foreign(['account_id_cash_or_bank'], 'fk_r_acc')->references(['id'])->on('accounts')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign(['customer_id'], 'fk_r_cust')->references(['id'])->on('customers')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign(['sale_id'], 'fk_r_sale')->references(['id'])->on('sales')->onUpdate('cascade')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('receipts', function (Blueprint $table) {
            $table->dropForeign('fk_r_acc');
            $table->dropForeign('fk_r_cust');
            $table->dropForeign('fk_r_sale');
        });
    }
};
