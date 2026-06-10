<?php

namespace Database\Factories\Modules;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Faker\Factory as FakerFactory;

class LeadFactory extends Factory
{
  public function definition()
  {
    $locale = 'de_DE';
    $faker = \Faker\Factory::create($locale);
    $firstName = $this->faker->firstName();
    $lastName  = $this->faker->lastName();
    $companyTypes = ['LLC', 'Inc.', 'Corp.', 'Ltd.', 'Group', 'Partners', 'Solutions'];

    $companyName = $faker->company() . ' ' . $faker->randomElement($companyTypes);

    return [
      'id' => Str::orderedUuid(), // Generates sequential UUIDs
      'name' => $firstName . ' ' . $lastName,
      'first_name' => $firstName,
      'last_name' => $lastName,
      'email' => $faker->optional(0.9)->safeEmail(),
      'phone' => $faker->optional(0.9)->phoneNumber(),
      'company' => $companyName,
      'address' => $faker->boolean(90) ? [
        'street'      => $faker->streetAddress(),
        'postal_code' => $faker->postcode(),
        'city'        => $faker->city(),
        'state'       => null,
        'country'     => $faker->country(),
      ] : null,
      'description' => $faker->realText(150),
      'created_at' => \Carbon\Carbon::instance($faker->dateTimeBetween('-2 years', 'now'))->utc(),
      'updated_at' => \Carbon\Carbon::instance($faker->dateTimeBetween('-1 year', 'now'))->utc(),
    ];
  }
}
