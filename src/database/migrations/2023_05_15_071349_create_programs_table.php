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
        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid');
            $table->string('program_name');
            $table->string('program_name_public')->nullable()->unique();
            $table->enum('status_target', ['0', '1'])->default("0");
            $table->enum('status_program', ['0', '1'])->default("1");
            $table->decimal('total_penghimpunan', 20, 8)->nullable()->default(00.00000000);  
            $table->decimal('price', 15, 4)->nullable()->default(00.0000);  
            $table->decimal('target_nominal', 15, 4)->nullable()->default(00.0000); 
            $table->smallInteger('campaign_type')->default(1);
            $table->date('from_date')->nullable();
            $table->date('to_date')->nullable();     
            $table->enum('is_savings', ['0', '1'])->default('0');
            $table->enum('publish_web', ['0', '1'])->default('0');
            $table->string('program_in_finance')->nullable();
            $table->string('slug')->unique();
            $table->string('image', 100)->nullable();

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

            $table->foreignId('program_category_id')
                    ->nullable()
                    ->constrained()
                    ->after('ulid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('programs');
    }
};
