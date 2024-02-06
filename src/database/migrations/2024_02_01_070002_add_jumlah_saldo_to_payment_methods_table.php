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
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->decimal('jumlah_saldo', 30, 8)->default(0.00000000);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            if (Schema::hasColumn('payment_methods', 'jumlah_saldo')) {
                $table->dropColumn('jumlah_saldo');
            }
        });
    }
};
