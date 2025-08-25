<?php

namespace Database\Factories;

use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

class SiteFactory extends Factory
{
    protected $model = Site::class;

    public function definition()
    {
        return [
            'name' => $this->faker->company(),
            'domain' => $this->faker->unique()->domainName(),
            'description' => $this->faker->paragraph(2),
            'is_active' => true,
            'settings' => json_encode([
                'type' => $this->faker->randomElement(['ecommerce', 'blog', 'corporate']),
                'language' => $this->faker->randomElement(['en', 'tr', 'de', 'fr']),
                'currency' => $this->faker->randomElement(['USD', 'EUR', 'TRY'])
            ])
        ];
    }
}
