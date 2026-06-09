<?php

namespace Database\Factories\Modules;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Faker\Factory as FakerFactory;

class AccountFactory extends factory
{
  public function definition()
  {
    $locale = 'de_DE';
    $faker = \Faker\Factory::create($locale);

    $companyTypes = ['LLC', 'Inc.', 'Corp.', 'Ltd.', 'Group', 'Partners', 'Solutions'];
    $industries = [
      'Technology',
      'Finance',
      'Healthcare',
      'Retail',
      'Manufacturing',
      'Construction',
      'Education',
      'Real Estate',
      'Transportation',
      'Hospitality'
    ];

    $companyName = $faker->company() . ' ' . $faker->randomElement($companyTypes);

    return [
      'id' => Str::orderedUuid(), // Generates sequential UUIDs
      'name' => $companyName,
      'website' => $faker->optional(0.8)->url(), // 80% chance of having a website
      'email' => $faker->optional(0.9)->safeEmail(), // 90% chance of having email
      'phone' => $faker->optional(0.7)->phoneNumber(), // 70% chance of having phone
      'billing_address' => $faker->boolean(90) ? [
        'street'      => $faker->streetAddress(),
        'postal_code' => $faker->postcode(),
        'city'        => $faker->city(),
        'state'       => null,
        'country'     => $faker->country(),
      ] : null,
      'shipping_address' => $faker->boolean(50) ? [
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
