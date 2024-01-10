<?php

use Termwind\Components\Raw;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('donors', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid');
            $table->bigInteger('kode_donatur')->unique();
            $table->string('kode_donatur_lama', 20)->nullable();
            $table->string('donor_name');
            $table->string('sapaan', 10)->nullable();

            // $table->string('email', 100)->nullable()->unique();
            // $table->string('email2', 100)->nullable()->unique();
            // $table->string('mobile', 15)->nullable()->unique();
            // $table->string('mobile2', 15)->nullable()->unique();

            $table->string('email', 100)->nullable();
            $table->string('email2', 100)->nullable();
            $table->string('mobile', 100)->nullable();
            $table->string('mobile2', 100)->nullable();

            $table->string('npwp', 30)->nullable();
            $table->enum('gender', ['L', 'P', 'U'])->default('U');
            $table->string('suf', 10)->nullable();
            $table->string('tempat_lahir', 50)->nullable();
            $table->string('temp_nama_asli_donatur', 100)->nullable();
            $table->date('birthday')->nullable();
            $table->string('alamat')->nullable();
            $table->string('alamat2')->nullable();
            $table->string('kota_kabupaten')->nullable();
            $table->string('provinsi_address')->nullable();
            $table->string('kode_pos')->nullable();
            $table->string('wilayah_address')->nullable();
            // $table->string('home_phone', 30)->nullable()->unique();
            $table->string('home_phone', 100)->nullable();
            $table->string('pekerjaan')->nullable();
            $table->string('pekerjaan_detail')->nullable();
            $table->string('alamat_kantor')->nullable();
            $table->string('kota_kantor')->nullable();
            $table->string('kode_post_kantor')->nullable();
            $table->string('wilayah_kantor')->nullable();
            // $table->string('telp_kantor', 15)->nullable()->unique();
            $table->string('telp_kantor', 100)->nullable();
            $table->string('facebook')->nullable();
            $table->string('twitter')->nullable();
            $table->string('pendidikan')->nullable();
            $table->string('pendidikan_detail')->nullable();
            $table->string('paket_9in1', 100)->nullable();
            $table->timestamp('registerd_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamps();
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('restrict')
                ->onUpdate('cascade');
            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('restrict')
                ->onUpdate('cascade');
            $table->foreignId('deleted_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('restrict')
                ->onUpdate('cascade');
            $table->foreignId('asign_to')
                ->constrained('users')
                ->onUpdate('cascade')
                ->onDelete('restrict');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donors');
    }
};
