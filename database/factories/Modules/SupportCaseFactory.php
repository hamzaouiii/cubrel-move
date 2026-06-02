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
      'subject' => $faker->sentence(),
      'description' => $faker->realText(150),
      'status'      => $closed ? 'closed' : 'open',
      'priority'    => $faker->randomElement(['low', 'medium', 'high']),
      'opened_at'   => \Carbon\Carbon::instance($openedAt)->utc(),
      'closed_at'   => $closed
        ? \Carbon\Carbon::instance($faker->dateTimeBetween($openedAt, 'now'))->utc()
        : null,
              'created_at' => \Carbon\Carbon::instance($faker->dateTimeBetween('-2 years', 'now'))->utc(),
      'updated_at' => \Carbon\Carbon::instance($faker->dateTimeBetween('-1 year', 'now'))->utc(),
    ];
  }
}
