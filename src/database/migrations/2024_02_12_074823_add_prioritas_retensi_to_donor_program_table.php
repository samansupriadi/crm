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
        Schema::table('donor_program', function (Blueprint $table) {
            $table->integer('retensi')->unsigned()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('donor_program', function (Blueprint $table) {
            if (Schema::hasColumn('donor_program', 'retensi')) {
                $table->dropColumn('retensi');
            }
        });
    }
};
