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
        Schema::table('donors', function (Blueprint $table) {
            $table->string('status_donatur', 100)->nullable()->comment('status donatur berdasarkan kapan terkahir kali dia berdonasi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('donors', function (Blueprint $table) {
            if (Schema::hasColumn('donors', 'status_donatur')) {
                $table->dropColumn('status_donatur');
            }
        });
    }
};
