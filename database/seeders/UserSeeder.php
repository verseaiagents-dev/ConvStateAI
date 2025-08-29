<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Örnek kullanıcılar oluştur (ID 1400'den başlayacak)
        $users = [
            [
                'id' => 1400,
                'name' => 'Test User 1',
                'email' => 'test1@example.com',
                'password' => Hash::make('password123'),
                'is_admin' => false,
                'email_verified_at' => now(),
            ],
            [
                'id' => 1401,
                'name' => 'Test User 2',
                'email' => 'test2@example.com',
                'password' => Hash::make('password123'),
                'is_admin' => false,
                'email_verified_at' => now(),
            ],
            [
                'id' => 1402,
                'name' => 'Test User 3',
                'email' => 'test3@example.com',
                'password' => Hash::make('password123'),
                'is_admin' => false,
                'email_verified_at' => now(),
            ],
        ];
        
        foreach ($users as $userData) {
            User::create($userData);
        }
    }
}
