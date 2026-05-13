<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;


/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
  /**
   * The current password being used by the factory.
   */
  protected static ?string $password;

  /**
   * Define the model's default state.
   *
   * @return array<string, mixed>
   */
  public function definition(): array
  {
    $first_name = fake()->firstName();
    $last_name = fake()->lastName();
    $name = $first_name." ".$last_name;
    return [
      'id' => Str::uuid(), // Generate UUID since model uses HasUuids and $incrementing = false
      'first_name' =>  $first_name,
      'last_name' =>  $last_name,
      'name' => $name,
      'title' => fake()->title(),
      'username' => fake()->unique()->userName(),
      'email' => fake()->unique()->safeEmail(),
      'email_verified_at' => now(),
      'password' => static::$password ??= Hash::make('password'),
      'remember_token' => Str::random(10),
      'is_admin' => false,
      'is_root' => false,
    ];
  }

  /**
   * Indicate that the model's email address should be unverified.
   */
  public function unverified(): static
  {
    return $this->state(fn(array $attributes) => [
      'email_verified_at' => null,
    ]);
  }

  /**
   * Indicate that the user is an admin.
   */
  public function admin(): static
  {
    return $this->state(fn(array $attributes) => [
      'is_admin' => true,
    ]);
  }
}
