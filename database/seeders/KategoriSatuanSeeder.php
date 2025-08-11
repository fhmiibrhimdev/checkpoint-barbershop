<?php

namespace Database\Seeders;

use App\Models\KategoriSatuan;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class KategoriSatuanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                // 'id_cabang'           => '1',
                'nama_satuan'         => 'Pcs',
                'deskripsi'           => '',
            ],
            [
                // 'id_cabang'           => '1',
                'nama_satuan'         => 'Kali',
                'deskripsi'           => '',
            ],
        ];

        KategoriSatuan::insert($data);
    }
}
