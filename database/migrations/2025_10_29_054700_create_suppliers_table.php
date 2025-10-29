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
        Schema::create('suppliers', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('name', 120)->index('idx_suppliers_name');
            $table->string('email', 120)->nullable()->unique('uq_suppliers_email');
            $table->string('phone', 40)->nullable();
            $table->string('address')->nullable();
            $table->string('tax_id', 50)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
