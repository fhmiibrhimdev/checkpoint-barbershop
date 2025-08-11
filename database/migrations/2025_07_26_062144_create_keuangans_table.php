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
        Schema::create('keuangan', function (Blueprint $table) {
            $table->id();
            $table->text('id_cabang');
            $table->text('id_user');
            $table->text('no_referensi');
            $table->text('tanggal');
            $table->text('keterangan');
            $table->text('id_kategori_keuangan');
            $table->text('jumlah');
            $table->enum('status', ['Balance', 'In', 'Out']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('keuangan');
    }
};
