<?php

namespace Database\Factories\Modules;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class DealFactory extends Factory
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
      'amount' => number_format($this->faker->randomFloat(2, 1000, 50000), 2, '.', ''),
      'description' => $this->faker->realText(120),

      'sales_stage' => $this->faker->randomElement($stages),
      'probability' => $this->faker->numberBetween(10, 90),
      'expected_close_date' => \Carbon\Carbon::instance($this->faker->dateTimeBetween('+1 week', '+6 months'))->utc(),

      'type' => $this->faker->randomElement($types),

      'created_at' => \Carbon\Carbon::instance($this->faker->dateTimeBetween('-2 years', 'now'))->utc(),
      'updated_at' => \Carbon\Carbon::instance($this->faker->dateTimeBetween('-1 year', 'now'))->utc(),
    ];
  }
}
