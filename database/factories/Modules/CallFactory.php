<?php

namespace Database\Factories\Modules;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CallFactory extends Factory
{
  protected $model = \App\Models\Modules\Call::class;

  public function definition(): array
  {
    $directions = ['inbound', 'outbound'];
    $statuses = ['planned', 'held', 'not_held'];
    $outcomes = ['connected', 'no_answer', 'voicemail', 'busy'];

    $status = $this->faker->randomElement($statuses);

    return [
      'id' => (string) Str::uuid(),

      'name' => ucfirst($this->faker->words(3, true)) . ' Call',
      'description' => $this->faker->realText(120),

      'direction' => $this->faker->randomElement($directions),
      'call_at' => \Carbon\Carbon::instance($this->faker->dateTimeBetween('-2 months', '+1 month'))->utc(),
      'duration_minutes' => $status === 'held' ? $this->faker->numberBetween(1, 60) : null,
      'status' => $status,
      'outcome' => $status === 'held' ? $this->faker->randomElement($outcomes) : null,

      'created_at' => \Carbon\Carbon::instance($this->faker->dateTimeBetween('-2 years', 'now'))->utc(),
      'updated_at' => \Carbon\Carbon::instance($this->faker->dateTimeBetween('-1 year', 'now'))->utc(),
    ];
  }
}
