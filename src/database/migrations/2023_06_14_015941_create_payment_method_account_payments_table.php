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
        Schema::create('payment_method_account_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_method_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('account_payment_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_method_account_payments');
    }
};
