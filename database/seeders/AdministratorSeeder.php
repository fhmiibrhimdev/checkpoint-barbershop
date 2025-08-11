<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AdministratorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = [
            [
                'id_cabang' => '1',
                'name' => 'Direktur',
                'email' => 'fahmi@direktur.com',
                'email_verified_at' => NULL,
                'password' => Hash::make('qweqweasd'),
                'active' => '1',
                'remember_token' => '',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // [
            //     'name' => 'Administrator',
            //     'email' => 'fahmi@admin.com',
            //     'email_verified_at' => NULL,
            //     'password' => Hash::make('qweqweasd'),
            //     'active' => '1',
            //     'remember_token' => '',
            //     'created_at' => now(),
            //     'updated_at' => now(),
            // ],
            // [
            //     'name' => 'Kasir',
            //     'email' => 'fahmi@kasir.com',
            //     'email_verified_at' => NULL,
            //     'password' => Hash::make('qweqweasd'),
            //     'active' => '1',
            //     'remember_token' => '',
            //     'created_at' => now(),
            //     'updated_at' => now(),
            // ],
            // [
            //     'name' => 'Capster',
            //     'email' => 'fahmi@capster.com',
            //     'email_verified_at' => NULL,
            //     'password' => Hash::make('qweqweasd'),
            //     'active' => '1',
            //     'remember_token' => '',
            //     'created_at' => now(),
            //     'updated_at' => now(),
            // ]
        ];

        $role = [
            [
                'role_id' => '1',
                'user_id' => '1',
                'user_type' => 'App\Models\User',
            ],
            // [
            //     'role_id' => '2',
            //     'user_id' => '2',
            //     'user_type' => 'App\Models\User',
            // ],
            // [
            //     'role_id' => '3',
            //     'user_id' => '3',
            //     'user_type' => 'App\Models\User',
            // ],
            // [
            //     'role_id' => '4',
            //     'user_id' => '4',
            //     'user_type' => 'App\Models\User',
            // ],
        ];

        DB::table('users')->insert($user);
        DB::table('role_user')->insert($role);
    }
}
