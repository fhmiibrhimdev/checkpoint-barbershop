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
        Schema::create('detail_transaksi', function (Blueprint $table) {
            $table->id();
            $table->text('id_transaksi')->nullable();
            $table->text('id_produk')->nullable();
            $table->text('nama_item')->nullable();
            $table->text('kategori_item')->nullable();
            $table->text('deskripsi_item')->nullable();
            $table->text('harga')->nullable();
            $table->text('harga_pokok')->nullable();
            $table->text('jumlah')->nullable();
            $table->text('sub_total')->nullable();
            $table->text('diskon')->nullable();
            $table->text('total_harga')->nullable();
            $table->text('id_karyawan')->nullable();
            $table->text('nama_karyawan')->nullable();
            $table->text('komisi_persen')->nullable();
            $table->text('komisi_nominal')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_transaksi');
    }
};
