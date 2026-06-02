<?php

namespace Database\Factories\Modules;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OrderFactory extends Factory
{
  public function definition(): array
  {
    $statuses = [
      'draft',
      'confirmed',
      'shipped',
      'completed',
      'cancelled',
    ];

    return [
      'id' => (string) Str::uuid(),
      'name' => $this->faker->words(3, true),
      'order_number' => 'ORD-' . strtoupper(Str::random(6)),
      'description' => $this->faker->sentence(),

      'total_amount' => $this->faker->randomFloat(2, 500, 20000),
      'currency' => $this->faker->randomElement(['EUR', 'USD', 'GBP']),
      'status' => $this->faker->randomElement($statuses),

      'order_date' => \Carbon\Carbon::instance($this->faker->dateTimeBetween('-1 month', 'now'))->utc(),
      'due_date' => \Carbon\Carbon::instance($this->faker->dateTimeBetween('now', '+2 months'))->utc(),


      'created_at' => \Carbon\Carbon::instance($this->faker->dateTimeBetween('-2 years', 'now'))->utc(),
      'updated_at' => \Carbon\Carbon::instance($this->faker->dateTimeBetween('-1 year', 'now'))->utc(),
    ];
  }
}
