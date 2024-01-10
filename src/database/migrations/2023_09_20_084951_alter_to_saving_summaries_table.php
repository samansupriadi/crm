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
            $table->dropForeign(['transaction_detail_id']);
            $table->dropColumn('transaction_detail_id');
            $table->dropColumn('parent_transaction_id');
            $table->foreignId('transaction_id_linked')
                ->nullable()->constrained('transaction_details')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('saving_summaries', function (Blueprint $table) {
            $table->foreignId('transaction_detail_id')
                ->nullable()->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->bigInteger('parent_transaction_id');
            $table->dropForeign(['transaction_id_linked']);
            $table->dropColumn('transaction_id_linked');
        });
    }
};
