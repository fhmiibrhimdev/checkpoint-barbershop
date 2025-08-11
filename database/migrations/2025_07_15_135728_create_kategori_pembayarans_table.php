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
        Schema::create('kategori_pembayaran', function (Blueprint $table) {
            $table->id();
            // $table->text('id_cabang')->default('');
            $table->text('nama_kategori')->default('');
            $table->text('deskripsi')->default('');
            $table->enum('can_update_delete', ['0', '1'])->default('1');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kategori_pembayaran');
    }
};
