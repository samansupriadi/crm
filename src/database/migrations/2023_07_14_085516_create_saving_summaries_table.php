<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   
    public function up(): void
    {
        Schema::create('saving_summaries', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid');
            $table->foreignId('transaction_detail_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->bigInteger('parent_transaction_id');
            $table->bigInteger('kode_transaksi');
            $table->decimal('nominal', 20, 8)->default(0.00000000);
            $table->integer('payment_to');
            $table->timestamps();
            $table->softDeletes();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('saving_summaries');
    }
};
