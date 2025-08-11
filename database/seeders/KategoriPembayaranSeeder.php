<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KategoriPembayaran;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class KategoriPembayaranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                // 'id_cabang'           => '1',
                'nama_kategori'       => 'Tunai',
                'deskripsi'           => 'Pembayaran langsung menggunakan uang fisik (cash) di tempat.',
                'can_update_delete'   => '0',
            ],
            [
                // 'id_cabang'           => '1',
                'nama_kategori'       => 'Transfer',
                'deskripsi'           => 'Pembayaran melalui transfer bank, mobile banking, internet banking, atau QRIS.',
                'can_update_delete'   => '0',
            ],
        ];

        KategoriPembayaran::insert($data);
    }
}
