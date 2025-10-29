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
        Schema::table('journal_lines', function (Blueprint $table) {
            $table->foreign(['account_id'], 'fk_jl_acc')->references(['id'])->on('accounts')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign(['journal_entry_id'], 'fk_jl_je')->references(['id'])->on('journal_entries')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('journal_lines', function (Blueprint $table) {
            $table->dropForeign('fk_jl_acc');
            $table->dropForeign('fk_jl_je');
        });
    }
};
