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
        Schema::create('detail_hutang', function (Blueprint $table) {
            $table->id();
            $table->text('id_hutang')->nullable();
            $table->text('tanggal_bayar')->nullable();
            $table->text('jumlah_bayar')->nullable();
            $table->text('keterangan')->nullable();
            $table->text('id_metode_pembayaran')->nullable();
            $table->text('created_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_hutang');
    }
};
