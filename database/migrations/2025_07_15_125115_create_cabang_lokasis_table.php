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
        Schema::create('cabang_lokasi', function (Blueprint $table) {
            $table->id();
            $table->text('nama_cabang')->nullable('');
            $table->text('subtitle_cabang')->nullable('');
            $table->text('alamat')->nullable('');
            $table->text('no_telp')->nullable('');
            $table->text('email')->nullable('');
            $table->text('syarat_nota_1')->nullable('');
            $table->text('template_pesan_booking')->default('Halo, [nama_pelanggan]\nNo Transaksi : [no_transaksi]\nBooking Anda telah tercatat di [nama_cabang].\n\nJika ada pertanyaan silakan hubungi admin.');
            $table->text('template_pesan_belum_lunas')->default('Halo, [nama_pelanggan]\nNo Transaksi : [no_transaksi]\nKami dari [nama_cabang]\nStatus pembayaran anda BELUM LUNAS.\nTotal tagihan : Rp. [total_tagihan]\nPembayaran Senilai Rp. [total_bayar],- via [metode_pembayaran] TELAH KAMI TERIMA.\nSisa bayar : Rp. [sisa_bayar]\n\nSilakan lakukan pelunasan sesuai ketentuan.\nBerikut link nota digital: [link_nota]');
            $table->text('template_pesan_lunas')->default('Halo, [nama_pelanggan]\nNo Transaksi : [no_transaksi]\nKami dari [nama_cabang]\nPembayaran Senilai Rp. [total_bayar],- via [metode_pembayaran] TELAH KAMI TERIMA.\n\nBerikut link nota digital: [link_nota]');
            $table->text('template_pesan_dibatalkan')->default('Halo, [nama_pelanggan]\nNo Transaksi : [no_transaksi]\nKami dari [nama_cabang]\nMohon maaf, transaksi anda telah DIBATALKAN.\n\nJika ada pertanyaan silakan hubungi admin.');
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cabang_lokasi');
    }
};
