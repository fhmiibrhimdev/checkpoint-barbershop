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
            $table->text('template_pesan_pembayaran')->nullable('');
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
