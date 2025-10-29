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
        Schema::create('sales', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('customer_id')->index('customer_id');
            $table->integer('store_id')->index('store_id');
            $table->date('date')->index('date');
            $table->string('payment_term', 60)->nullable();
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
        Schema::dropIfExists('sales');
    }
};
