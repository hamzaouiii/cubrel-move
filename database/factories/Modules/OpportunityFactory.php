<?php

namespace Database\Factories\Modules;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OpportunityFactory extends Factory
{
  public function definition(): array
  {
    $stages = [
      'prospecting',
      'qualification',
      'proposal',
      'negotiation',
      'closed_won',
      'closed_lost',
    ];

    $types = [
      'new_business',
      'existing_business',
      'renewal',
      'upsell',

    ];

    return [
      'id' => (string) Str::uuid(),

      'name' => $this->faker->company() . ' Deal',
      'amount' => $this->faker->randomFloat(2, 1000, 50000),
      'currency' => 'EUR',
      'description' => $this->faker->sentence(),

      'sales_stage' => $this->faker->randomElement($stages),
      'probability' => $this->faker->numberBetween(10, 90),
      'expected_close_date' => $this->faker->dateTimeBetween('+1 week', '+6 months'),

      'type' => $this->faker->randomElement($types),

      'created_at' => now(),
      'updated_at' => now(),
    ];
  }
}
