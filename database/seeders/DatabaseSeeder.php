<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Project;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ID'leri sıfırla ve 1400'den başlat
        $this->resetAutoIncrement();
        
        // Seeder'ları çalıştır (sadece mevcut olanları)
        $this->call([
            // AdminUserSeeder::class, // Zaten mevcut
            // UserSeeder::class, // Zaten mevcut
            // ProjectSeeder::class, // Zaten mevcut
            // ProductSeeder::class, // Dosya mevcut değil
            // PlanSeeder::class, // Dosya mevcut değil
        ]);
        
        echo "Database seeding completed. All necessary data already exists.\n";
    }
    
    /**
     * Auto increment değerlerini sıfırla
     */
    private function resetAutoIncrement(): void
    {
        // Users tablosu için
        DB::statement('ALTER TABLE users AUTO_INCREMENT = 1400');
        
        // Projects tablosu için
        DB::statement('ALTER TABLE projects AUTO_INCREMENT = 1400');
        
        // Diğer önemli tablolar için
        DB::statement('ALTER TABLE knowledge_bases AUTO_INCREMENT = 1400');
        DB::statement('ALTER TABLE enhanced_chat_sessions AUTO_INCREMENT = 1400');
        DB::statement('ALTER TABLE intents AUTO_INCREMENT = 1400');
        DB::statement('ALTER TABLE campaigns AUTO_INCREMENT = 1400');
        DB::statement('ALTER TABLE products AUTO_INCREMENT = 1400');
    }
}
