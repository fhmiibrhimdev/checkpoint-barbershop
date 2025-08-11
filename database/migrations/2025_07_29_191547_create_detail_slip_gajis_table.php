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
        Schema::create('detail_slip_gaji', function (Blueprint $table) {
            $table->id();
            $table->text('id_slip_gaji')->nullable();
            $table->text('nama_komponen')->nullable();
            $table->text('jumlah')->nullable();
            $table->enum('tipe', ['tunjangan', 'potongan'])->default('tunjangan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_slip_gaji');
    }
};
