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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->bigInteger('kode_transaksi')->unique();
            $table->string('subject')->nullable();
            $table->foreignId('donor_id')->constrained();
            $table->foreignId('payment_method_id')->constrained();
            $table->foreignId('account_payment_id')->constrained();
            $table->date('tanggal_kuitansi')->nullable();
            $table->date('tanggal_approval')->nullable();
            $table->decimal('total_donasi', 20, 8)->nullable()->default(0.00000000);
            $table->string('status');
            $table->text('description')->nullable()->default(null);
            $table->string('no_kuitansi')->nullable();
            $table->string('tanggal_hijriah')->nullable();

            $table->foreignId('updated_by')
                ->nullable()
                ->default(null)
                ->constrained('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreignId('created_by')
                ->nullable()
                ->default(null)
                ->constrained('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            
            $table->foreignId('deleted_by')
                ->nullable()
                ->default(null)
                ->constrained('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreignId('approved_by')
                ->nullable()
                ->default(null)
                ->constrained('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            
            $table->foreignId('reject_by')
                ->nullable()
                ->default(null)
                ->constrained('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreignId('unapproved_by')
                ->nullable()
                ->default(null)
                ->constrained('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
