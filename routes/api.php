<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CabangLokasiController;
use App\Http\Controllers\DaftarKaryawanController;
use App\Http\Controllers\DaftarPelangganController;
use App\Http\Controllers\KategoriPembayaranController;
use App\Http\Controllers\KategoriPengeluaranController;
use App\Http\Controllers\KategoriProdukController;
use App\Http\Controllers\KategoriSatuanController;
use App\Http\Controllers\SaldoAwalItemController;

Route::resource('custom-url', CabangLokasiController::class);
Route::resource('custom-url', KategoriProdukController::class);
Route::resource('custom-url', KategoriPengeluaranController::class);
Route::resource('custom-url', KategoriPembayaranController::class);
Route::resource('custom-url', KategoriSatuanController::class);
Route::resource('custom-url', DaftarKaryawanController::class);
Route::resource('custom-url', DaftarPelangganController::class);
Route::resource('custom-url', SaldoAwalItemController::class);
