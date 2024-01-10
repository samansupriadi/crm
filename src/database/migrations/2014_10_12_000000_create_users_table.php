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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid');
            $table->string('name');
            $table->string('email');
            $table->enum('status', ['0', '1'])->default('1');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('telp')->nullable();
            $table->foreignId('created_by')
                ->nullable()
                ->default(null)
                ->constrained('users')
                ->onUpdate('cascade')
                ->nullOnDelete();
            $table->foreignId('updated_by')
                ->nullable()
                ->default(null)
                ->constrained('users')
                ->onUpdate('cascade')
                ->nullOnDelete();
            $table->foreignId('deleted_by')
                ->nullable()
                ->default(null)
                ->constrained('users')
                ->onUpdate('cascade')
                ->nullOnDelete();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
