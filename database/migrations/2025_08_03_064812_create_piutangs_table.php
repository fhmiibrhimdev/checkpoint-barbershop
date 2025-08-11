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
        Schema::create('piutang', function (Blueprint $table) {
            $table->id();
            $table->text('no_referensi')->nullable();
            $table->text('id_transaksi')->nullable();
            $table->text('tanggal_bayar')->nullable();
            $table->text('jumlah_bayar')->nullable();
            $table->text('keterangan')->nullable();
            $table->text('id_metode_pembayaran')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('piutang');
    }
};
