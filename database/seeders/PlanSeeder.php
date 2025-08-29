<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Plan;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Freemium',
                'price' => 0.00,
                'billing_cycle' => 'monthly',
                'features' => [
                    'max_projects' => 1,
                    'max_knowledge_bases' => 1,
                    'max_chat_sessions' => 50,
                    'ai_analysis' => false,
                    'support' => 'Community',
                    'duration_days' => 7
                ],
                'is_active' => true
            ],
            [
                'name' => 'Ücretsiz',
                'price' => 0.00,
                'billing_cycle' => 'monthly',
                'features' => [
                    'max_projects' => 1,
                    'max_knowledge_bases' => 3,
                    'max_chat_sessions' => 100,
                    'ai_analysis' => false,
                    'support' => 'Email'
                ],
                'is_active' => true
            ],
            [
                'name' => 'Pro',
                'price' => 29.99,
                'billing_cycle' => 'monthly',
                'features' => [
                    'max_projects' => 10,
                    'max_knowledge_bases' => 50,
                    'max_chat_sessions' => 1000,
                    'ai_analysis' => true,
                    'support' => 'Priority Email'
                ],
                'is_active' => true
            ],
            [
                'name' => 'Enterprise',
                'price' => 99.99,
                'billing_cycle' => 'monthly',
                'features' => [
                    'max_projects' => -1, // Sınırsız
                    'max_knowledge_bases' => -1, // Sınırsız
                    'max_chat_sessions' => -1, // Sınırsız
                    'ai_analysis' => true,
                    'support' => '24/7 Phone + Email'
                ],
                'is_active' => true
            ]
        ];

        foreach ($plans as $planData) {
            Plan::create($planData);
        }
    }
}
