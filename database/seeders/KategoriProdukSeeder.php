<?php

namespace Database\Seeders;

use App\Models\KategoriProduk;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class KategoriProdukSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                // 'id_cabang'           => '1',
                'nama_kategori'       => 'Produk Barbershop',
                'deskripsi'           => 'Produk yang digunakan atau dijual khusus untuk keperluan potong rambut dan perawatan di barbershop (contoh: pomade, minyak rambut, shampoo khusus).',
                'can_update_delete'   => '0',
            ],
            [
                // 'id_cabang'           => '1',
                'nama_kategori'       => 'Jasa Barbershop',
                'deskripsi'           => 'Layanan potong rambut, cukur, styling, atau jasa grooming lainnya yang dilakukan oleh barber.',
                'can_update_delete'   => '0',
            ],
            [
                // 'id_cabang'           => '1',
                'nama_kategori'       => 'Treatment',
                'deskripsi'           => 'Layanan perawatan tambahan seperti warna rambut, creambath, masker rambut, massage kepala, atau perawatan wajah.',
                'can_update_delete'   => '0',
            ],
            [
                // 'id_cabang'           => '1',
                'nama_kategori'       => 'Produk Umum',
                'deskripsi'           => 'Produk yang tidak termasuk kategori khusus barbershop, seperti minuman, aksesoris, atau barang kebutuhan umum lainnya.',
                'can_update_delete'   => '0',
            ],
        ];

        KategoriProduk::insert($data);
    }
}
