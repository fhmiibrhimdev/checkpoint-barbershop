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
        Schema::create('daftar_karyawan', function (Blueprint $table) {
            $table->id();
            $table->text('id_user')->default('');
            $table->text('role_id')->default('');
            $table->text('id_cabang')->default('');
            $table->text('saldo_kasbon')->default('');
            $table->text('tgl_lahir')->default('-');
            $table->text('jk')->default('-');
            $table->text('alamat')->default('-');
            $table->text('no_telp')->default('62');
            $table->text('deskripsi')->default('-');
            $table->text('gambar')->default('-');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daftar_karyawan');
    }
};
