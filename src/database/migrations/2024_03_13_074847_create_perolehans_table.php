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
        Schema::create('perolehans', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->foreignId('transaction_id')
                ->constrained()
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreignId('transaction_detail_id')
                ->constrained()
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreignId('program_id')
                ->constrained()
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreignId('program_category_id')
                ->constrained()
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreignId('donor_id')
                ->constrained()
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreignId('payment_method_id')
                ->constrained()
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreignId('account_payment_id')
                ->constrained()
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreignId('entity_id')
                ->constrained()
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->unsignedBigInteger('approved_by');
            $table->foreign('approved_by')->references('id')->on('users')->cascadeOnUpdate()->cascadeOnDelete();

            $table->unsignedBigInteger('update_by');
            $table->foreign('update_by')->references('id')->on('users')->cascadeOnUpdate()->cascadeOnDelete();

            $table->bigInteger('kode_transaksi');
            $table->bigInteger('kode_donatur');
            $table->string('program_name', 100);
            $table->string('category_program', 100);
            $table->string('payment_method', 100);
            $table->string('account_payment', 100);
            $table->string('operator', 100);
            $table->decimal('bagian_penyaluran', 20, 8)->nullable()->default(0.00000000);
            $table->decimal('bagian_pengelola', 20, 8)->nullable()->default(0.00000000);;
            $table->decimal('nominal_donasi', 20, 8)->nullable()->default(0.00000000);
            $table->date('tanggal_transakasi');
            $table->date('tanggal_approval');
            $table->longText('keterangan');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perolehans');
    }
};
