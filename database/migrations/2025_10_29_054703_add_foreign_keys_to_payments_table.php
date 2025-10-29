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
        Schema::table('payments', function (Blueprint $table) {
            $table->foreign(['account_id_cash_or_bank'], 'fk_pay_acc')->references(['id'])->on('accounts')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign(['purchase_id'], 'fk_pay_pur')->references(['id'])->on('purchases')->onUpdate('cascade')->onDelete('set null');
            $table->foreign(['supplier_id'], 'fk_pay_sup')->references(['id'])->on('suppliers')->onUpdate('cascade')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign('fk_pay_acc');
            $table->dropForeign('fk_pay_pur');
            $table->dropForeign('fk_pay_sup');
        });
    }
};
