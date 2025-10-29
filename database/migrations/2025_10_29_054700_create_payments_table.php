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
        Schema::create('payments', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->integer('supplier_id')->index('supplier_id');
            $table->integer('purchase_id')->nullable()->index('purchase_id');
            $table->integer('account_id_cash_or_bank')->index('fk_pay_acc');
            $table->date('date');
            $table->decimal('amount', 12);
            $table->string('notes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
