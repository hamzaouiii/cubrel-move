<?php

namespace Database\Factories\Modules;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class QuoteFactory extends Factory
{
  /**
   * Define the model's default state.
   *
   * @return array<string, mixed>
   */
  public function definition(): array
  {
    $subtotal = $this->faker->randomFloat(2, 100, 8000);
    $tax      = round($subtotal * 0.19, 2);
    $total    = $subtotal + $tax;

    return [

      'name' => 'Quote ' . $this->faker->unique()->numerify('####'),

      'description' => $this->faker->optional()->realText(150),

      'number' => $this->faker->unique()->numerify('Q-#####'),

      'status' => $this->faker->randomElement([
        'draft',
        'sent',
        'accepted',
        'rejected',
        'expired',
      ]),

      'valid_until' => ($d = $this->faker->optional()->dateTimeBetween('now', '+30 days')) ? \Carbon\Carbon::instance($d)->utc() : null,
      'discount' => 0,
      'subtotal' => $subtotal,
      'tax'      => $tax,
      'total'    => $total,

      'notes' => $this->faker->optional()->paragraph(),
            'created_at' => \Carbon\Carbon::instance($this->faker->dateTimeBetween('-2 years', 'now'))->utc(),
      'updated_at' => \Carbon\Carbon::instance($this->faker->dateTimeBetween('-1 year', 'now'))->utc(),
    ];
  }
}
