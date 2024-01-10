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
            // $table->foreignId('linked')->nullable()->constrained('transaction_details')->onUpdate('cascade')->onDelete('cascade');
            $table->bigInteger('linked')->nullable();
            $table->enum('main', ['0', '1'])->default('1');
            $table->enum('settled', ['0', '1'])->default('1');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaction_details', function (Blueprint $table) {
            $table->dropColumn('main');
            $table->dropColumn('linked');
            $table->dropColumn('settled');
        });
    }
};
