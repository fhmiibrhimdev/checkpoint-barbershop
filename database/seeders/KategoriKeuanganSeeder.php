<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KategoriKeuangan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class KategoriKeuanganSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'nama_kategori'       => 'Setor Tunai',
                'kategori'            => 'Pemasukan',
                'deskripsi'           => 'Uang tunai hasil penjualan yang disetor ke bank.',
                'header'              => 'no',
                'can_update_delete'   => '0',
            ],
            [
                'nama_kategori'       => 'Setor Transfer',
                'kategori'            => 'Pemasukan',
                'deskripsi'           => 'Pembayaran customer via transfer, QRIS, debit langsung masuk rekening bank.',
                'header'              => 'yes',
                'can_update_delete'   => '0',
            ],
            [
                'nama_kategori'       => 'Pelunasan Kasbon',
                'kategori'            => 'Pemasukan',
                'deskripsi'           => 'Pelunasan kasbon dari karyawan.',
                'header'              => 'yes',
                'can_update_delete'   => '0',
            ],
            [
                'nama_kategori'       => 'Pemasukan Lain',
                'kategori'            => 'Pemasukan',
                'deskripsi'           => 'Pendapatan di luar penjualan (refund supplier, bunga bank, tambahan modal).',
                'header'              => 'no',
                'can_update_delete'   => '0',
            ],

            [
                'nama_kategori'       => 'Biaya Operasional',
                'kategori'            => 'Pengeluaran',
                'deskripsi'           => 'Biaya operasional harian seperti listrik, wifi, air, dan sewa.',
                'header'              => 'no',
                'can_update_delete'   => '0',
            ],
            [
                'nama_kategori'       => 'Hutang',
                'kategori'            => 'Pengeluaran',
                'deskripsi'           => 'Pembayaran hutang kepada supplier atau vendor.',
                'header'              => 'yes',
                'can_update_delete'   => '0',
            ],
            [
                'nama_kategori'       => 'Pengajuan Kasbon',
                'kategori'            => 'Pengeluaran',
                'deskripsi'           => 'Pinjaman ke karyawan lewat rekening.',
                'header'              => 'yes',
                'can_update_delete'   => '0',
            ],
            [
                'nama_kategori'       => 'Slip Gaji',
                'kategori'            => 'Pengeluaran',
                'deskripsi'           => 'Gaji karyawan dibayar lewat rekening.',
                'header'              => 'yes',
                'can_update_delete'   => '0',
            ],
            [
                'nama_kategori'       => 'Pengeluaran Lainnya',
                'kategori'            => 'Pengeluaran',
                'deskripsi'           => 'Pengeluaran di luar biaya operasional dan hutang (misal: donasi, biaya tak terduga, denda, kerugian selisih kas, atau pengeluaran insidental lainnya).',
                'header'              => 'no',
                'can_update_delete'   => '0',
            ],
        ];

        KategoriKeuangan::insert($data);
    }
}
