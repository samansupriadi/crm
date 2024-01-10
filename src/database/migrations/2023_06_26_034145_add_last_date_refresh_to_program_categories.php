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
        Schema::table('program_categories', function (Blueprint $table) {
            $table->timestamp('last_refresh_total')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('program_categories', function (Blueprint $table) {
            if (Schema::hasColumn('program_categories', 'last_refresh_total')) {
                $table->dropColumn('last_refresh_total');
            }
        });
    }
};
