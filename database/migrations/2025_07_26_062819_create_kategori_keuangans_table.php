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
        Schema::create('kategori_keuangan', function (Blueprint $table) {
            $table->id();
            // $table->text('id_cabang')->nullable();
            $table->text('nama_kategori')->nullable();
            $table->text('kategori')->nullable();
            $table->text('deskripsi')->nullable();
            $table->enum('header', ['yes', 'no'])->default('no');
            $table->enum('can_update_delete', ['0', '1'])->default('1');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kategori_keuangan');
    }
};
