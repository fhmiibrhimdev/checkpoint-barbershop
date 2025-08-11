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
        Schema::create('persediaan', function (Blueprint $table) {
            $table->id();
            $table->text('id_cabang')->default('');
            $table->text('id_user')->default('');
            $table->text('id_produk')->default('');
            $table->text('tanggal')->default(date('Y-m-d'));
            $table->text('qty')->default('0');
            $table->text('keterangan')->default('-');
            $table->text('buku')->default('-');
            $table->text('fisik')->default('-');
            $table->text('selisih')->default('-');
            $table->text('opname')->default('no');
            $table->enum('status', ['Balance', 'In', 'Out'])->default('Balance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('persediaan');
    }
};
