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
        Schema::create('purchases', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('supplier_id')->index('supplier_id');
            $table->integer('warehouse_id')->index('warehouse_id');
            $table->date('date')->index('date');
            $table->enum('payment_term', ['CONTADO', 'CREDITO'])->default('CONTADO');
            $table->date('due_date')->nullable();
            $table->decimal('subtotal', 12)->default(0);
            $table->decimal('tax', 12)->default(0);
            $table->decimal('total', 12)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
