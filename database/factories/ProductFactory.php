<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $title = $this->faker->sentence(3);

        return [
            'producer_id' => User::factory(),
            'title' => $title,
            'slug' => Str::slug($title) . '-' . Str::random(6),
            'description' => $this->faker->paragraph(),
            'price' => 297,
            'currency' => 'MZN',
            'type' => 'ebook',
            'commission_rate' => 10,
            'status' => 'draft',
        ];
    }
}
