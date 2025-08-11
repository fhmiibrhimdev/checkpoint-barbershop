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
        Schema::create('kasbon', function (Blueprint $table) {
            $table->id();
            $table->text('no_referensi')->nullable();
            $table->text('id_karyawan')->nullable();
            $table->text('jumlah')->nullable();
            $table->text('keterangan')->nullable();
            $table->text('tgl_pengajuan')->nullable();
            $table->enum('status', ['pending', 'disetujui', 'ditolak'])->default('pending');
            $table->text('tgl_disetujui')->nullable();
            $table->text('id_disetujui')->nullable();
            $table->enum('metode_input', ['manual', 'pengajuan'])->default('manual');
            $table->enum('kategori', ['pengajuan', 'pelunasan'])->default('pengajuan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kasbon');
    }
};
