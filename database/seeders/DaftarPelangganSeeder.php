<?php

namespace Database\Seeders;

use App\Models\DaftarPelanggan;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DaftarPelangganSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'id_user'             => '1',
                'id_cabang'           => '1',
                'nama_pelanggan'      => 'UMUM',
                'no_telp'             => '62',
                'deskripsi'           => 'Pelanggan Umum',
                'gambar'              => '-',
            ],
        ];

        DaftarPelanggan::insert($data);
    }
}
