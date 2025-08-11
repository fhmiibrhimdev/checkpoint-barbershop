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
        Schema::create('hutang', function (Blueprint $table) {
            $table->id();
            $table->text('id_cabang')->nullable();
            $table->text('no_referensi')->nullable();
            $table->text('id_supplier')->nullable();
            $table->text('tanggal_beli')->nullable();
            $table->text('total_tagihan')->nullable();
            $table->text('total_dibayarkan')->nullable();
            $table->text('sisa_hutang')->nullable();
            $table->enum('status', ['Sudah Lunas', 'Belum Lunas'])->default('Belum Lunas');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hutang');
    }
};
