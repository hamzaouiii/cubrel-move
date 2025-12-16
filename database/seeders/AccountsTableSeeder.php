<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class AccountsTableSeeder extends Seeder
{
  public function run()
  {
    $faker = Faker::create();

    $accounts = [];

    for ($i = 0; $i < 50; $i++) {
      $companyName = $faker->company() . ' ' .
        $faker->randomElement(['LLC', 'Inc.', 'Corp.', 'Ltd.']);

      $hasShipping = $faker->boolean(60); // 60% chance of different shipping

      $accounts[] = [
        'id' => Str::orderedUuid(),
        'name' => $companyName,
        'website' => $faker->boolean(80) ? 'https://' . Str::slug($companyName) . '.com' : null,
        'email' => $faker->boolean(90) ? $faker->companyEmail() : null,
        'phone' => $faker->boolean(70) ? $faker->phoneNumber() : null,
        'billing_address' => $faker->boolean(90) ? $faker->streetAddress() : null,
        'shipping_address' => $hasShipping ? $faker->streetAddress() : null,
        'city' => $faker->boolean(90) ? $faker->city() : null,
        'country' => $faker->boolean(90) ? $faker->country() : null,
        'created_at' => $faker->dateTimeBetween('-2 years', 'now'),
        'updated_at' => $faker->dateTimeBetween('-1 year', 'now'),
      ];
    }

    // Insert in batches for better performance
    foreach (array_chunk($accounts, 100) as $chunk) {
      DB::table('accounts')->insert($chunk);
    }
  }
}
