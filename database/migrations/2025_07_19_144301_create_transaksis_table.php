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
        Schema::create('transaksi', function (Blueprint $table) {
            $table->id();
            $table->text('id_cabang')->nullable();
            $table->text('id_user')->nullable();
            $table->text('no_transaksi')->nullable();
            $table->text('tanggal')->nullable();
            $table->text('id_pelanggan')->nullable();
            $table->text('catatan')->nullable();
            $table->text('total_pesanan')->nullable();
            $table->text('total_komisi_karyawan')->nullable();
            $table->text('total_sub_total')->nullable();
            $table->text('total_diskon')->nullable();
            $table->text('total_akhir')->nullable();
            $table->text('total_hpp')->nullable();
            $table->text('laba_bersih')->nullable();
            $table->text('id_metode_pembayaran')->nullable();
            $table->text('jumlah_dibayarkan')->nullable();
            $table->text('kembalian')->nullable();
            $table->enum('status', ['1', '2', '3', '4'])->default('3')->comment('1 = booking, 2 = belum lunas, 3 = lunas, 4 = dibatalkan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi');
    }
};
