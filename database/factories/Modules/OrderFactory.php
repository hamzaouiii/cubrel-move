<?php

namespace Database\Factories\Modules;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OrderFactory extends Factory
{
  public function definition(): array
  {
    $statuses = [
      'Draft',
      'Confirmed',
      'Shipped',
      'Completed',
      'Cancelled',
    ];

    return [
      'id' => (string) Str::uuid(),

      'order_number' => 'ORD-' . strtoupper(Str::random(6)),
      'description' => $this->faker->sentence(),

      'total_amount' => $this->faker->randomFloat(2, 500, 20000),
      'currency' => 'EUR',

      'status' => $this->faker->randomElement($statuses),

      'order_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
      'due_date' => $this->faker->dateTimeBetween('now', '+2 months'),


      'created_at' => now(),
      'updated_at' => now(),
    ];
  }
}
