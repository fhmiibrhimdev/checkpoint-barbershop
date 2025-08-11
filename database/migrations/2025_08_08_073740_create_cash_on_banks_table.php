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
        Schema::create('cash_on_bank', function (Blueprint $table) {
            $table->id();
            $table->text('id_cabang')->nullable();
            $table->text('tanggal')->nullable();
            $table->text('no_referensi')->nullable();
            $table->enum('jenis', ['In', 'Out'])->nullable();
            $table->text('jumlah')->nullable();
            $table->text('keterangan')->nullable();
            $table->enum('sumber_tabel', ['Hutang', 'Piutang', 'Kas Masuk', 'Kas Keluar', 'Kasbon (Pengajuan)', 'Kasbon (Pelunasan)', 'Slip Gaji', 'Setor Tunai', 'Setor Transfer'])->nullable();
            $table->text('id_sumber')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_on_bank');
    }
};
