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
        Schema::table('account_payments', function (Blueprint $table) {
            $table->integer('jumlah_transaksi')->unsigned()->nullable()->comment('banyak nya jumlah transaksi pada akun ini');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('account_payments', function (Blueprint $table) {
            if (Schema::hasColumn('account_payments', 'jumlah_transaksi')) {
                $table->dropColumn('jumlah_transaksi');
            }
        });
    }
};
