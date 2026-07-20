<?php

namespace Database\Factories\Modules;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class MeetingFactory extends Factory
{
  protected $model = \App\Models\Modules\Meeting::class;

  public function definition(): array
  {
    $statuses = ['planned', 'held', 'not_held', 'cancelled'];

    $startAt = $this->faker->dateTimeBetween('-2 months', '+2 months');
    $endAt = (clone $startAt)->modify('+' . $this->faker->numberBetween(30, 120) . ' minutes');

    return [
      'id' => (string) Str::uuid(),

      'name' => ucfirst($this->faker->words(4, true)) . ' Meeting',
      'description' => $this->faker->realText(120),

      'location' => $this->faker->boolean(70) ? [
        'street'      => $this->faker->streetAddress(),
        'postal_code' => $this->faker->postcode(),
        'city'        => $this->faker->city(),
        'state'       => null,
        'country'     => $this->faker->country(),
      ] : null,

      'start_at' => \Carbon\Carbon::instance($startAt)->utc(),
      'end_at' => \Carbon\Carbon::instance($endAt)->utc(),
      'status' => $this->faker->randomElement($statuses),

      'created_at' => \Carbon\Carbon::instance($this->faker->dateTimeBetween('-2 years', 'now'))->utc(),
      'updated_at' => \Carbon\Carbon::instance($this->faker->dateTimeBetween('-1 year', 'now'))->utc(),
    ];
  }
}
