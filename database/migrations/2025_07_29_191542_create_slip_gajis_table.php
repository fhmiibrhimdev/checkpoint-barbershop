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
        Schema::create('slip_gaji', function (Blueprint $table) {
            $table->id();
            $table->text('id_cabang')->nullable();
            $table->text('no_referensi')->nullable();
            $table->text('periode_mulai')->nullable();
            $table->text('periode_selesai')->nullable();
            $table->text('id_karyawan')->nullable();
            $table->text('total_tunjangan')->nullable();
            $table->text('total_potongan')->nullable();
            $table->text('total_gaji')->nullable();
            $table->enum('status', ['draft', 'final'])->default('draft');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('slip_gaji');
    }
};
