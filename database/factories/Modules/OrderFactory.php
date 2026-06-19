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
      'pending',
      'procecessing',
      'completed',
      'cancelled',
    ];

    $orderTypes = [
      'Software License', 'Hardware Bundle', 'Annual Subscription',
      'Professional Services', 'Support Package', 'Enterprise Suite',
      'Cloud Storage', 'API Access', 'Training Package', 'Consulting Services',
    ];

    return [
      'id' => (string) Str::uuid(),
      'name' => $this->faker->company() . ' – ' . $this->faker->randomElement($orderTypes),
      'order_number' => 'ORD-' . strtoupper(Str::random(6)),
      'description' => $this->faker->realText(120),

      'status' => $this->faker->randomElement($statuses),

      'order_date' => \Carbon\Carbon::instance($this->faker->dateTimeBetween('-1 month', 'now'))->utc(),
      'due_date' => \Carbon\Carbon::instance($this->faker->dateTimeBetween('now', '+2 months'))->utc(),


      'created_at' => \Carbon\Carbon::instance($this->faker->dateTimeBetween('-2 years', 'now'))->utc(),
      'updated_at' => \Carbon\Carbon::instance($this->faker->dateTimeBetween('-1 year', 'now'))->utc(),
    ];
  }
}
