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
        Schema::create('donor_program', function (Blueprint $table) {
            $table->id();
            $table->foreignId('donor_id')->constrained();
            $table->foreignId('program_id')->constrained();
            $table->decimal('total_donasi_program', 30, 8)->nullable()->default(0.00000000);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donor_program');
    }
};
