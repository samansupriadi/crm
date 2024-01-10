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
        Schema::table('saving_summaries', function (Blueprint $table) {
            $table->date('settled_date')->nullable();
            $table->enum('finish', ['0', '1'])->nullable()->default('0');
            $table->decimal('saving_total', 20, 8)->default(0.00000000);
            $table->string('desc')->nullable();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('saving_summaries', function (Blueprint $table) {
            $table->dropColumn('settled_date');
            $table->dropColumn('finish');
            $table->dropColumn('saving_total');
            $table->dropColumn('desc');
        });
    }
};
