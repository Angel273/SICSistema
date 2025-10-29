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
        Schema::create('journal_lines', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->bigInteger('journal_entry_id')->index('journal_entry_id');
            $table->integer('account_id')->index('account_id');
            $table->decimal('debit', 12)->default(0);
            $table->decimal('credit', 12)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_lines');
    }
};
