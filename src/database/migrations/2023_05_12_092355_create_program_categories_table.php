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
        Schema::create('program_categories', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid');
            $table->string('category_name', 100)->unique();
            $table->enum('type', ['FIX', 'DYNAMIC'])->default('DYNAMIC');
            $table->decimal('bagian_pengelola', 15, 4)->default(00.0000);
            $table->decimal('total_penghimpunan', 20, 8)->default(00.00000000);

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->onUpdate('cascade');  
            $table->foreignId('deleted_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->onUpdate('cascade');
            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->onUpdate('cascade');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_categories');
    }
};
