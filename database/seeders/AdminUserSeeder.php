<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'id' => 1399, // Admin user ID'si 1399 olarak ayarlandı
            'name' => 'Kadir Durmazlar',
            'email' => 'kadirdurmazlar@gmail.com',
            'password' => Hash::make('Copperage.26'),
            'is_admin' => true,
            'email_verified_at' => now(),
        ]);
    }
}
