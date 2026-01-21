<?php

namespace Database\Factories\Modules;

use Illuminate\Database\Eloquent\Factories\Factory;
use Faker\Factory as FakerFactory;

class SupportCaseFactory extends Factory
{
  protected $model = \App\Models\Modules\SupportCase::class;

  public function definition(): array
  {
    // Randomly choose German or English
    $locale = rand(0, 1) ? 'de_DE' : 'en_US';
    $faker = \Faker\Factory::create($locale);

    $openedAt = $faker->dateTimeBetween('-3 months', 'now');
    $closed = $faker->boolean(40);

    return [
      'name'        => $faker->name(),
      'description' => $faker->realText(150),
      'status'      => $closed ? 'closed' : 'open',
      'priority'    => $faker->randomElement(['low', 'medium', 'high']),
      'opened_at'   => $openedAt,
      'closed_at'   => $closed
        ? $faker->dateTimeBetween($openedAt, 'now')
        : null,
    ];
  }
}
