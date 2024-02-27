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
        Schema::table('saving_summaries', function (Blueprint $table) {
            $table->date('tanggal_kuitansi')->nullable();
            $table->date('tanggal_approval')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('saving_summaries', function (Blueprint $table) {
            $table->dropColumn('tanggal_kuitansi');
            $table->dropColumn('tanggal_approval');
        });
    }
};
