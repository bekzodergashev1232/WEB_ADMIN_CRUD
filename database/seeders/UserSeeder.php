<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('12345678'),
            'phone' => '+(998) 99 999 99 99',
            'nick_name' => 'super_admin',
            'address' => 'Tashkent, Uzbekistan yangi darxon',
            'viloyat_id' => 1726,
            'tuman_id' => 1726262,
            //'role_id' => User::SUPER_ADMIN,
        ]);
    }
}
