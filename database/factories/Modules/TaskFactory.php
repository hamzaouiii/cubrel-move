<?php

namespace Database\Factories\Modules;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TaskFactory extends Factory
{
  protected $model = \App\Models\Modules\Task::class;

  public function definition(): array
  {
    $statuses = ['not_started', 'in_progress', 'completed', 'deferred'];
    $priorities = ['low', 'medium', 'high'];

    $status = $this->faker->randomElement($statuses);

    return [
      'id' => (string) Str::uuid(),

      'name' => ucfirst($this->faker->words(4, true)),
      'description' => $this->faker->realText(120),

      'due_at' => \Carbon\Carbon::instance($this->faker->dateTimeBetween('-2 months', '+2 months'))->utc(),
      'status' => $status,
      'priority' => $this->faker->randomElement($priorities),
      'completed_at' => $status === 'completed'
        ? \Carbon\Carbon::instance($this->faker->dateTimeBetween('-2 months', 'now'))->utc()
        : null,

      'created_at' => \Carbon\Carbon::instance($this->faker->dateTimeBetween('-2 years', 'now'))->utc(),
      'updated_at' => \Carbon\Carbon::instance($this->faker->dateTimeBetween('-1 year', 'now'))->utc(),
    ];
  }
}
