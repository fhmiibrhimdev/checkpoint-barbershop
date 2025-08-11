<?php

namespace Database\Seeders;

use App\Models\CabangLokasi;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CabangLokasiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'nama_cabang'               => 'CheckPoint Barbershop',
                'subtitle_cabang'           => '',
                'alamat'                    => 'Jl. Otto Iskandardinata, Karanganyar, Kec. Subang, Kabupaten Subang, Jawa Barat',
                'email'                     => '',
                'syarat_nota_1'             => '',
                'template_pesan_pembayaran' => '',
                'subtitle_cabang'           => '',
                'status'                    => 'aktif',
                'no_telp'                   => '085216003456',
            ],
            // [
            //     'nama_cabang'         => 'CheckPoint Barbershop II',
            //     'alamat'              => 'Jl. Cipinang Muara III, Cipinang Muara, Kec. Jatinegara, DKI Jakarta 13420',
            //     'status'              => 'aktif',
            //     'no_telp'             => '089601922906',
            // ],
            // [
            //     'nama_cabang'         => 'CheckPoint Barbershop III',
            //     'alamat'              => 'Universitas Indonesia, Jl. Prof. DR. G.A. Siwabessy, Kukusan, Kecamatan Beji, Kota Depok, Jawa Barat 16425',
            //     'status'              => 'aktif',
            //     'no_telp'             => '085691253593',
            // ],
        ];

        CabangLokasi::insert($data);
    }
}
