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
                'template_pesan_booking'    => 'Halo, [nama_pelanggan]\nNo Transaksi : [no_transaksi]\n\nBooking Anda telah tercatat di [nama_cabang].\n\nJika ada pertanyaan silakan hubungi admin.',
                'template_pesan_belum_lunas' => 'Halo, [nama_pelanggan]\nNo Transaksi : [no_transaksi]\n\nKami dari [nama_cabang]\nStatus pembayaran anda *BELUM LUNAS*.\nTotal tagihan : Rp. [total_tagihan]\nPembayaran Senilai Rp. [total_bayar],- via [metode_pembayaran] TELAH KAMI TERIMA.\nSisa bayar : Rp. [sisa_bayar]\n\nSilakan lakukan pelunasan sesuai ketentuan.\nBerikut link nota digital: [link_nota]',
                'template_pesan_lunas'      => 'Halo, [nama_pelanggan]\nNo Transaksi : [no_transaksi]\n\nKami dari [nama_cabang]\nPembayaran Senilai Rp. [total_bayar],- via [metode_pembayaran] TELAH KAMI TERIMA.\n\nBerikut link nota digital: [link_nota]',
                'template_pesan_dibatalkan' => 'Halo, [nama_pelanggan]\nNo Transaksi : [no_transaksi]\n\nKami dari [nama_cabang]\nMohon maaf, transaksi anda telah DIBATALKAN.\n\nJika ada pertanyaan silakan hubungi admin.',
                'subtitle_cabang'           => '',
                'status'                    => 'aktif',
                'no_telp'                   => '6285216003456',
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
