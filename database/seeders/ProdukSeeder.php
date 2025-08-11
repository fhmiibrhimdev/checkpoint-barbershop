<?php

namespace Database\Seeders;

use App\Models\Produk;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ProdukSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [];

        for ($i = 1; $i <= 5000; $i++) {
            $data[] = [
                'id_cabang' => 1,
                'id_user' => 1,
                'id_kategori' => 1,
                'id_satuan' => 1,
                'kode_item' => null,
                'nama_item' => 'Produk ke-' . $i,
                'harga_jasa' => rand(10000, 100000),
                'komisi' => rand(0, 100),
                'harga_pokok' => null,
                'harga_jual' => null,
                'stock' => 0,
                'deskripsi' => '-',
                'gambar' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('produk')->insert(array_chunk($data, 500)[0]); // Sisipkan 500 sekaligus

        foreach (array_chunk($data, 500) as $chunk) {
            DB::table('produk')->insert($chunk);
        }
    }
}
