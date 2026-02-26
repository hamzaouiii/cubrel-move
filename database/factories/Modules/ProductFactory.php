<?php

namespace Database\Factories\Modules;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
  public function definition(): array
  {
    return [
      'id' => (string) Str::uuid(),

      'name' => $this->faker->words(3, true),
      'sku' => strtoupper(Str::random(8)),
      'description' => $this->faker->sentence(),

      'category' => $this->faker->randomElement([
        'Software',
        'Hardware',
        'Service',
      ]),

      'price' => $this->faker->randomFloat(2, 50, 5000),
      'currency' => 'EUR',

      'is_active' => $this->faker->boolean(90),

      'created_at' => now(),
      'updated_at' => now(),
    ];
  }
}
