<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition()
    {
        return [
            'site_id' => Site::factory(),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph(3),
            'short_description' => $this->faker->sentence(10),
            'sku' => $this->faker->unique()->ean8,
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'compare_price' => $this->faker->randomFloat(2, 15, 1200),
            'cost_price' => $this->faker->randomFloat(2, 5, 800),
            'weight' => $this->faker->randomFloat(3, 0.1, 10),
            'dimensions' => json_encode([
                'length' => $this->faker->randomFloat(2, 1, 100),
                'width' => $this->faker->randomFloat(2, 1, 100),
                'height' => $this->faker->randomFloat(2, 1, 100),
                'unit' => 'cm'
            ]),
            'stock_quantity' => $this->faker->numberBetween(0, 100),
            'rating_average' => $this->faker->randomFloat(2, 1, 5),
            'rating_count' => $this->faker->numberBetween(0, 100),
            'tags' => json_encode($this->faker->words(5)),
            'attributes' => json_encode([
                'color' => $this->faker->colorName(),
                'size' => $this->faker->randomElement(['S', 'M', 'L', 'XL']),
                'material' => $this->faker->randomElement(['Cotton', 'Polyester', 'Leather', 'Metal', 'Plastic'])
            ]),
            'images' => json_encode([
                'main_image' => $this->faker->imageUrl(400, 400, 'product'),
                'gallery_images' => [
                    $this->faker->imageUrl(400, 400, 'product'),
                    $this->faker->imageUrl(400, 400, 'product')
                ]
            ])
        ];
    }
}
