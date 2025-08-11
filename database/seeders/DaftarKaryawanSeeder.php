<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\DaftarKaryawan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DaftarKaryawanSeeder extends Seeder
{
    public function run(): void
    {
        $id_cabang = 1;

        $users = [
            [
                'name' => 'Cabang ' . $id_cabang . ' - Admin',
                'email' => 'cabang' . $id_cabang . '@admin.com',
                'role_id' => 2, // admin
            ],
            [
                'name' => 'Cabang ' . $id_cabang . ' - Kasir',
                'email' => 'cabang' . $id_cabang . '@kasir.com',
                'role_id' => 3, // kasir
            ],
            [
                'name' => 'Cabang ' . $id_cabang . ' - Capster',
                'email' => 'cabang' . $id_cabang . '@capster.com',
                'role_id' => 4, // capster
            ],
        ];

        $roleMapping = [
            "1"  => "direktur",
            "2"  => "admin",
            "3"  => "kasir",
            "4"  => "capster",
        ];

        foreach ($users as $userData) {
            $user = User::create([
                'id_cabang' => $id_cabang,
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => Hash::make('1'),
                'active' => 1,
            ]);

            // Insert ke role_user
            DB::table('role_user')->insert([
                'role_id' => $userData['role_id'],
                'user_id' => $user->id,
                'user_type' => 'App\Models\User',
            ]);

            // Insert ke daftar_karyawan
            DaftarKaryawan::create([
                'id_user'    => $user->id,
                'id_cabang'  => $id_cabang,
                'saldo_kasbon'  => '0',
                'role_id'    => $roleMapping[$userData['role_id']],
                'tgl_lahir'  => date('Y-m-d'),
                'jk'         => '-',
                'alamat'     => '-',
                'no_telp'    => '62',
                'deskripsi'  => '-',
                'gambar'     => '-',
            ]);
        }
    }
}
