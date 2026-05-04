<?php

namespace Database\Factories\Modules;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

class BaseModuleFactory extends Factory
{
  public function definition(): array
  {
    return [
      'owner_id' => User::inRandomOrder()->first()?->id ?? User::factory(),
    ];
  }
  
  public function withOwner($userId)
  {
    return $this->state(['owner_id' => $userId]);
  }
}