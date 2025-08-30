<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\User;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin kullanıcının ID'sini al
        $adminUser = User::where('is_admin', true)->first();
        
        if (!$adminUser) {
            throw new \Exception('Admin user not found. Please run AdminUserSeeder first.');
        }
        
        // Örnek projeler oluştur (ID 1400'den başlayacak)
        $projects = [
            [
                'id' => 1400,
                'name' => 'E-ticaret Projesi',
                'description' => 'Online satış platformu geliştirme projesi',
                'status' => 'active',
                'is_featured' => true,
                'created_by' => $adminUser->id, // Dinamik admin user ID
            ],
            [
                'id' => 1401,
                'name' => 'Mobil Uygulama',
                'description' => 'iOS ve Android mobil uygulama geliştirme',
                'status' => 'active',
                'is_featured' => false,
                'created_by' => $adminUser->id, // Dinamik admin user ID
            ],
            [
                'id' => 1402,
                'name' => 'Web Sitesi Yenileme',
                'description' => 'Kurumsal web sitesi modernizasyon projesi',
                'status' => 'inactive', // 'planning' yerine 'inactive' kullan
                'is_featured' => false,
                'created_by' => $adminUser->id, // Dinamik admin user ID
            ],
        ];
        
        foreach ($projects as $projectData) {
            Project::create($projectData);
        }
    }
}
