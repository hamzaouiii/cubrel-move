<?php

namespace Database\Factories\Modules;

use Illuminate\Database\Eloquent\Factories\Factory;
use Faker\Factory as FakerFactory;

class SupportCaseFactory extends Factory
{
  protected $model = \App\Models\Modules\SupportCase::class;

  public function definition(): array
  {
    // Randomly choose German or English
    $locale = rand(0, 1) ? 'de_DE' : 'en_US';
    $faker = FakerFactory::create($locale);

    $subjects = [
      'Unable to log in after password reset',
      'Billing discrepancy on last invoice',
      'Data export not working for large datasets',
      'Dashboard not loading for some users',
      'Email notifications not being sent',
      'API rate limit exceeded unexpectedly',
      'Two-factor authentication code not accepted',
      'Import fails with CSV files over 10 MB',
      'Permission error when accessing shared reports',
      'Integration stops syncing after OAuth token refresh',
      'Search returns incorrect results for recent records',
      'PDF generation times out on large documents',
    ];

    $subject  = $faker->randomElement($subjects);
    $openedAt = $faker->dateTimeBetween('-3 months', 'now');
    $closed   = $faker->boolean(40);

    return [
      'name'        => $subject,
      'subject'     => $subject,
      'description' => $faker->realText(150),
      'status'      => $closed ? 'closed' : 'open',
      'priority'    => $faker->randomElement(['low', 'medium', 'high']),
      'opened_at'   => \Carbon\Carbon::instance($openedAt)->utc(),
      'closed_at'   => $closed
        ? \Carbon\Carbon::instance($faker->dateTimeBetween($openedAt, 'now'))->utc()
        : null,
              'created_at' => \Carbon\Carbon::instance($faker->dateTimeBetween('-2 years', 'now'))->utc(),
      'updated_at' => \Carbon\Carbon::instance($faker->dateTimeBetween('-1 year', 'now'))->utc(),
    ];
  }
}
