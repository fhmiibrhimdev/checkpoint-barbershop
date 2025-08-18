<?php

namespace App\Services;

use App\Models\Produk;
use App\Models\CabangLokasi;
use App\Models\DaftarKaryawan;
use App\Models\KategoriProduk;
use App\Models\KategoriSatuan;
use Illuminate\Support\Facades\DB;

class GlobalDataService
{
    // Fungsi untuk mengambil data cabang
    public function getCabangs()
    {
        return DB::table('cabang_lokasi')->select('id', 'nama_cabang')->get();
    }

    // Fungsi untuk mengambil data kategori produk
    public function getKategoris()
    {
        return DB::table('kategori_produk')->select('id', 'nama_kategori')->get();
    }

    // Fungsi untuk mengambil data kategori produk
    public function getKategorisCustom()
    {
        return DB::table('kategori_produk')->select('id', 'nama_kategori');
    }

    // Fungsi untuk mengambil data kategori satuan
    public function getSatuans()
    {
        return DB::table('kategori_satuan')->select('id', 'nama_satuan')->get();
    }

    public function getProduks()
    {
        return DB::table('produk')->select('id', 'nama_item')->get();
    }

    public function getPelanggans()
    {
        return DB::table('daftar_pelanggan')->select('id', 'nama_pelanggan')->get();
    }

    public function getPelanggansCustom($id_cabang)
    {
        return DB::table('daftar_pelanggan')->select('id', 'nama_pelanggan')->where('id_cabang', $id_cabang)->get();
    }

    public function getKaryawans()
    {
        return DB::table('daftar_karyawan')->select('daftar_karyawan.id', 'name', 'nama_cabang')
            ->join('cabang_lokasi', 'cabang_lokasi.id', 'daftar_karyawan.id_cabang')
            ->join('users', 'users.id', 'daftar_karyawan.id_user')
            ->get();
    }

    public function getSuppliers()
    {
        return DB::table('daftar_supplier')->select('daftar_supplier.id', 'nama_supplier', 'nama_cabang')
            ->join('cabang_lokasi', 'cabang_lokasi.id', 'daftar_supplier.id_cabang')
            ->get();
    }

    public function getKaryawansCustom($id_cabang)
    {
        return DB::table('daftar_karyawan')->select('daftar_karyawan.id', 'name')
            ->join('users', 'users.id', 'daftar_karyawan.id_user')
            ->where('role_id', 'capster')
            ->where('daftar_karyawan.id_cabang', $id_cabang)
            ->get();
    }

    public function getProdukAndKategori()
    {
        return DB::table('produk')->select('produk.id', 'nama_item', 'harga_jasa', 'nama_kategori', 'produk.deskripsi')
            ->join('kategori_produk', 'kategori_produk.id', 'produk.id_kategori')
            ->get();
    }

    public function getProdukAndKategoriCustom($id_cabang)
    {
        return DB::table('produk')->select('produk.id', 'nama_item', 'harga_jasa', 'nama_kategori', 'produk.deskripsi', 'produk.stock')
            ->join('kategori_produk', 'kategori_produk.id', 'produk.id_kategori')
            ->where('produk.id_cabang', $id_cabang)
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->where('nama_kategori', 'Produk Barbershop')
                        ->where('stock', '>', 0);
                })
                    ->orWhere(function ($q) {
                        $q->where('nama_kategori', 'Produk Umum')
                            ->where('stock', '>', 0);
                    })
                    ->orWhereNotIn('nama_kategori', ['Produk Barbershop', 'Produk Umum']);
            })
            ->get();
    }

    public function getMetodePembayaran()
    {
        return DB::table('kategori_pembayaran')->select('id', 'nama_kategori')->get();
    }

    public function getKategoriKeuangan($kategori)
    {
        return DB::table('kategori_keuangan')
            ->select('id', 'nama_kategori')
            ->where('kategori', $kategori)
            ->where('header', 'no')
            ->get();
    }

    public function waBase()
    {
        return "http://0.0.0.0:5000";
    }
}
