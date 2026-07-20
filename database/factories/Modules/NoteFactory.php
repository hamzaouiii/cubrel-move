<?php

namespace Database\Factories\Modules;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class NoteFactory extends Factory
{
  protected $model = \App\Models\Modules\Note::class;

  public function definition(): array
  {
    return [
      'id' => (string) Str::uuid(),

      'name' => ucfirst($this->faker->words(3, true)) . ' Note',
      'description' => $this->faker->realText(150),

      'created_at' => \Carbon\Carbon::instance($this->faker->dateTimeBetween('-2 years', 'now'))->utc(),
      'updated_at' => \Carbon\Carbon::instance($this->faker->dateTimeBetween('-1 year', 'now'))->utc(),
    ];
  }
}
