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
        Schema::table('transaction_details', function (Blueprint $table) {
            $table->decimal('nominal', 30, 8)->nullable()->default(0.00000000)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaction_details', function (Blueprint $table) {
            $table->decimal('nominal', 20, 8)->nullable()->default(0.00000000)->change();
        });
    }
};
