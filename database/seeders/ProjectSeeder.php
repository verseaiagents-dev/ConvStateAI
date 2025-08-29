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
        // Örnek projeler oluştur (ID 1400'den başlayacak)
        $projects = [
            [
                'id' => 1400,
                'name' => 'E-ticaret Projesi',
                'description' => 'Online satış platformu geliştirme projesi',
                'status' => 'active',
                'is_featured' => true,
                'created_by' => 1, // Admin user ID
            ],
            [
                'id' => 1401,
                'name' => 'Mobil Uygulama',
                'description' => 'iOS ve Android mobil uygulama geliştirme',
                'status' => 'active',
                'is_featured' => false,
                'created_by' => 1,
            ],
            [
                'id' => 1402,
                'name' => 'Web Sitesi Yenileme',
                'description' => 'Kurumsal web sitesi modernizasyon projesi',
                'status' => 'planning',
                'is_featured' => false,
                'created_by' => 1,
            ],
        ];
        
        foreach ($projects as $projectData) {
            Project::create($projectData);
        }
    }
}
