<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserRoleSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Akun Admin
        User::create([
            'name' => 'Administrator',
            'username' => 'admin',
            'email' => 'admin@toko.com',
            'password' => Hash::make('rahasia123'),
            'role' => 'admin',
        ]);

        // 2. Akun Owner
        User::create([
            'name' => 'Pemilik',
            'username' => 'owner',
            'email' => 'owner@toko.com',
            'password' => Hash::make('rahasia123'),
            'role' => 'owner',
        ]);

        // 3. Akun Kasir
        User::create([
            'name' => 'Kasir',
            'username' => 'kasir',
            'email' => 'kasir@toko.com',
            'password' => Hash::make('rahasia123'),
            'role' => 'kasir',
        ]);

        $this->command->info('Akun Admin, Owner, dan Kasir berhasil dibuat!');
    }
}
