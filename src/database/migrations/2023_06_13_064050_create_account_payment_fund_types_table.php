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
        Schema::create('fund_type_account_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_payment_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('fund_type_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->decimal('saldo_tipe_dana', 20, 4)->default(0.0000);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_payment_fund_types');
    }
};
