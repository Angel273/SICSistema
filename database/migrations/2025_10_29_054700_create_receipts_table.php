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
        Schema::create('receipts', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->integer('customer_id')->index('customer_id');
            $table->integer('sale_id')->nullable()->index('sale_id');
            $table->integer('account_id_cash_or_bank')->index('fk_r_acc');
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
        Schema::dropIfExists('receipts');
    }
};
