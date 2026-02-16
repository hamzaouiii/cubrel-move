<?php

namespace Database\Factories\Modules;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AccountFactory extends Factory
{
  public function definition()
  {
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

    $companyName = $this->faker->company() . ' ' . $this->faker->randomElement($companyTypes);

    return [
      'id' => Str::orderedUuid(), // Generates sequential UUIDs
      'name' => $companyName,
      'website' => $this->faker->optional(0.8)->url(), // 80% chance of having a website
      'email' => $this->faker->optional(0.9)->safeEmail(), // 90% chance of having email
      'phone' => $this->faker->optional(0.7)->phoneNumber(), // 70% chance of having phone
      'billing_address' => $this->faker->optional(0.9)->streetAddress(), // 90% chance
      'shipping_address' => $this->faker->optional(0.5)->streetAddress(),
      'city' => $this->faker->optional(0.9)->city(),
      'country' => $this->faker->optional(0.9)->country(),
      'description' => $this->faker->paragraph(),
      'created_at' => $this->faker->dateTimeBetween('-2 years', 'now'),
      'updated_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
    ];
  }

  /**
   * Factory state for premium accounts
   */
  public function premium()
  {
    return $this->state(function (array $attributes) {
      $premiumDomains = ['example.com', 'corporate.com', 'enterprise.com'];

      return [
        'website' => 'https://' . Str::slug($attributes['name']) . '.' . $this->faker->randomElement($premiumDomains),
        'email' => 'contact@' . Str::slug($attributes['name']) . '.com',
      ];
    });
  }

  /**
   * Factory state for international accounts
   */
  public function international()
  {
    return $this->state(function (array $attributes) {
      $countries = ['Germany', 'France', 'United Kingdom', 'Japan', 'Australia', 'Canada'];

      return [
        'country' => $this->faker->randomElement($countries),
        'city' => $this->faker->city(),
      ];
    });
  }

  /**
   * Factory state for accounts with complete information
   */
  public function complete()
  {
    return $this->state(function (array $attributes) {
      return [
        'website' => 'https://' . Str::slug($attributes['name']) . '.com',
        'email' => 'info@' . Str::slug($attributes['name']) . '.com',
        'phone' => $this->faker->phoneNumber(),
        'billing_address' => $this->faker->streetAddress(),
        'shipping_address' => $this->faker->streetAddress(),
        'city' => $this->faker->city(),
        'country' => $this->faker->country(),
      ];
    });
  }
}
