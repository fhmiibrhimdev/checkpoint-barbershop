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
        Schema::create('kas', function (Blueprint $table) {
            $table->id();
            $table->text('id_cabang')->nullable();
            $table->text('no_referensi')->nullable();
            $table->text('id_pembuat')->nullable();
            $table->text('tanggal')->default(date('Y-m-d'));
            $table->text('keterangan')->nullable();
            $table->text('jumlah')->nullable();
            $table->text('id_kategori_keuangan')->nullable();
            $table->enum('status', ['Balance', 'In', 'Out'])->default('Balance');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kas');
    }
};
