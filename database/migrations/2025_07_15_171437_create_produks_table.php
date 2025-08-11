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
        Schema::create('produk', function (Blueprint $table) {
            $table->id();
            $table->text('id_cabang')->nullable();
            $table->text('id_user')->nullable();
            $table->text('id_kategori')->nullable();
            $table->text('id_satuan')->nullable();
            $table->text('kode_item')->nullable();
            $table->text('nama_item')->nullable();
            $table->text('harga_jasa')->nullable();
            $table->text('komisi')->nullable();
            $table->text('harga_pokok')->nullable();
            $table->text('harga_jual')->nullable();
            $table->text('stock')->nullable();
            $table->text('deskripsi')->nullable();
            $table->text('gambar')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produk');
    }
};
