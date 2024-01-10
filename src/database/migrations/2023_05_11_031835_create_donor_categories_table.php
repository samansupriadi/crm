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
        Schema::create('donor_categories', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid');
            $table->string('category_name', 100)->unique();
            $table->decimal('rules_nominal', 30, 8)->nullable()->default(0.00000000);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donor_categories');
    }
};
