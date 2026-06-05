<?php

namespace Database\Factories\Modules;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class ContactFactory extends Factory
{
  /**
   * Define the model's default state.
   *
   * @return array<string, mixed>
   */
  public function definition(): array
  {
    $firstName = $this->faker->firstName();
    $lastName  = $this->faker->lastName();

    return [

      'name' => $firstName . ' ' . $lastName,

      'description' => $this->faker->optional()->realText(150),


      'first_name' => $firstName,
      'last_name'  => $lastName,

      'email' => $this->faker->safeEmail(),
      'phone' => $this->faker->phoneNumber(),

      'position' => $this->faker->jobTitle(),

      'notes' => $this->faker->optional()->paragraph(),
      'created_at' => \Carbon\Carbon::instance($this->faker->dateTimeBetween('-2 years', 'now'))->utc(),
      'updated_at' => \Carbon\Carbon::instance($this->faker->dateTimeBetween('-1 year', 'now'))->utc(),
    ];
  }
}
