<?php

namespace Database\Factories\Modules;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class MeetingFactory extends Factory
{
  protected $model = \App\Models\Modules\Meeting::class;

  // Realistic meeting titles paired with a short agenda note.
  protected array $templates = [
    ['title' => 'Kickoff meeting', 'description' => 'Introduce the team and align on project scope and timeline.'],
    ['title' => 'Quarterly business review', 'description' => 'Review results from the last quarter and plan ahead.'],
    ['title' => 'Product demo', 'description' => 'Walk the evaluation team through the platform live.'],
    ['title' => 'Contract negotiation', 'description' => 'Work through open items in the contract before signing.'],
    ['title' => 'Onboarding session', 'description' => 'Get the team set up and comfortable with the basics.'],
    ['title' => 'Strategy planning session', 'description' => 'Map out priorities and next steps for the coming months.'],
    ['title' => 'Stakeholder alignment meeting', 'description' => 'Make sure everyone involved is on the same page before moving forward.'],
    ['title' => 'Renewal discussion', 'description' => 'Go over renewal terms and any changes needed for next year.'],
    ['title' => 'Technical requirements review', 'description' => "Confirm integration requirements with their engineering team."],
    ['title' => 'Budget planning meeting', 'description' => 'Discuss budget scope for the upcoming project phase.'],
  ];

  public function definition(): array
  {
    $statuses = ['planned', 'held', 'not_held', 'cancelled'];
    $template = $this->faker->randomElement($this->templates);

    $startAt = $this->faker->dateTimeBetween('-2 months', '+2 months');
    $endAt = (clone $startAt)->modify('+' . $this->faker->numberBetween(30, 120) . ' minutes');

    return [
      'id' => (string) Str::uuid(),

      'name' => $template['title'],
      'description' => $template['description'],

      'location' => $this->faker->boolean(70) ? [
        'street'      => $this->faker->streetAddress(),
        'postal_code' => $this->faker->postcode(),
        'city'        => $this->faker->city(),
        'state'       => null,
        'country'     => $this->faker->country(),
      ] : null,

      'start_at' => \Carbon\Carbon::instance($startAt)->utc(),
      'end_at' => \Carbon\Carbon::instance($endAt)->utc(),
      'status' => $this->faker->randomElement($statuses),

      'created_at' => \Carbon\Carbon::instance($this->faker->dateTimeBetween('-2 years', 'now'))->utc(),
      'updated_at' => \Carbon\Carbon::instance($this->faker->dateTimeBetween('-1 year', 'now'))->utc(),
    ];
  }
}
