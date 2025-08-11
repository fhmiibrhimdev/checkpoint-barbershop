<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            LaratrustSeeder::class,
            AdministratorSeeder::class,
            CabangLokasiSeeder::class,
            KategoriProdukSeeder::class,
            // KategoriPengeluaranSeeder::class,
            KategoriPembayaranSeeder::class,
            KategoriSatuanSeeder::class,
            KategoriKeuanganSeeder::class,
            DaftarKaryawanSeeder::class,
            DaftarPelangganSeeder::class,
        ]);
    }
}
