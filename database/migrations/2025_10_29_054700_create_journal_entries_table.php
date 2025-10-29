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
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->date('date')->index('date');
            $table->string('description')->nullable();
            $table->string('ref_type', 40)->nullable();
            $table->bigInteger('ref_id')->nullable();

            $table->index(['ref_type', 'ref_id'], 'ref_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};
