<?php

namespace Database\Factories\Modules;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CallFactory extends Factory
{
  protected $model = \App\Models\Modules\Call::class;

  // Realistic call purposes paired with a short note on what it was about.
  protected array $templates = [
    ['title' => 'Discovery call', 'description' => 'Understand their current process and where the pain points are.'],
    ['title' => 'Intro call', 'description' => 'First conversation — get a feel for what they need and who is involved.'],
    ['title' => 'Pricing negotiation', 'description' => 'Walk through tiered pricing options and discuss possible discounts.'],
    ['title' => 'Contract review call', 'description' => 'Go over the contract terms line by line before signing.'],
    ['title' => 'Follow-up call', 'description' => 'Check back in after the last conversation to see where things stand.'],
    ['title' => 'Check-in call', 'description' => 'Routine touchpoint to see how things are going on their end.'],
    ['title' => 'Support call', 'description' => 'Customer ran into an issue and needed help troubleshooting.'],
    ['title' => 'Renewal discussion', 'description' => 'Talk through renewal terms ahead of the contract end date.'],
    ['title' => 'Demo walkthrough', 'description' => 'Show the product live and answer questions as they come up.'],
    ['title' => 'Onboarding call', 'description' => 'Get them set up and walk through the first steps together.'],
    ['title' => 'Escalation call', 'description' => 'Address a recurring complaint directly with the account owner.'],
    ['title' => 'Quarterly business review', 'description' => 'Review usage, results, and plans for the next quarter.'],
    ['title' => 'Referral follow-up', 'description' => 'They were referred by an existing customer, gauge interest.'],
    ['title' => 'Budget check-in', 'description' => 'Confirm budget is approved before moving to next steps.'],
  ];

  public function definition(): array
  {
    $directions = ['inbound', 'outbound'];
    $statuses = ['planned', 'held', 'not_held'];
    $outcomes = ['connected', 'no_answer', 'voicemail', 'busy'];

    $status = $this->faker->randomElement($statuses);
    $template = $this->faker->randomElement($this->templates);

    return [
      'id' => (string) Str::uuid(),

      'name' => $template['title'],
      'description' => $template['description'],

      'direction' => $this->faker->randomElement($directions),
      'call_at' => \Carbon\Carbon::instance($this->faker->dateTimeBetween('-2 months', '+1 month'))->utc(),
      'duration_minutes' => $status === 'held' ? $this->faker->numberBetween(1, 60) : null,
      'status' => $status,
      'outcome' => $status === 'held' ? $this->faker->randomElement($outcomes) : null,

      'created_at' => \Carbon\Carbon::instance($this->faker->dateTimeBetween('-2 years', 'now'))->utc(),
      'updated_at' => \Carbon\Carbon::instance($this->faker->dateTimeBetween('-1 year', 'now'))->utc(),
    ];
  }
}
