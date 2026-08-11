<?php

namespace Database\Factories\Modules;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class InvoiceFactory extends Factory
{
  /**
   * Define the model's default state.
   *
   * @return array<string, mixed>
   */
  public function definition(): array
  {

    return [

      'name' => 'Invoice ' . $this->faker->unique()->numerify('####'),

      'description' => $this->faker->optional()->realText(150),

      'number' => $this->faker->unique()->numerify('INV-#####'),

      'status' => $this->faker->randomElement([
        'draft',
        'sent',
        'paid',
        'overdue',
        'viewed',
      ]),

      'issue_date' => $issueDate = $this->faker->optional()->date(),
      'due_date'   => $issueDate
        ? $this->faker->optional()->dateTimeBetween($issueDate, (new \DateTime($issueDate))->modify('+90 days'))?->format('Y-m-d')
        : $this->faker->optional()->date(),

      'notes' => $this->faker->optional()->paragraph(),
      'created_at' => \Carbon\Carbon::instance($this->faker->dateTimeBetween('-2 years', 'now'))->utc(),
      'updated_at' => \Carbon\Carbon::instance($this->faker->dateTimeBetween('-1 year', 'now'))->utc(),
    ];
  }
}
