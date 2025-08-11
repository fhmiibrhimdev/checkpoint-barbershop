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
        Schema::create('daftar_pelanggan', function (Blueprint $table) {
            $table->id();
            $table->text('id_user')->default('');
            $table->text('id_cabang')->default('');
            $table->text('nama_pelanggan')->default('');
            $table->enum('jk', ['Pria', 'Wanita'])->nullable();
            $table->text('no_telp')->default('62');
            $table->text('deskripsi')->default('-');
            $table->text('total_kunjungan')->nullable()->default('0');
            $table->text('gambar')->default('-');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daftar_pelanggan');
    }
};
