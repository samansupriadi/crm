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
        Schema::create('donor_information_details', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid');
            $table->tinyInteger('sumber_informasi')->nullable();
            $table->decimal('totaldonasi', 20, 8)->nullable()->default(00.00000000);
            $table->timestamps();
            $table->softDeletes();

            $table->foreignId('donor_id')
                ->constrained('donors')
                ->onUpdate('restrict')
                ->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donor_information_details');
    }
};
