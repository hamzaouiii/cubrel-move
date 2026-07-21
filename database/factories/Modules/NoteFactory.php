<?php

namespace Database\Factories\Modules;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class NoteFactory extends Factory
{
  protected $model = \App\Models\Modules\Note::class;

  // Realistic call/meeting takeaways, the kind of thing a rep jots down
  // right after talking to a customer. `description` is what actually shows
  // in the record's timeline title, so it carries the realism here.
  protected array $notes = [
    'Client confirmed budget approval, ready to proceed.',
    'Mentioned interest in upgrading to the premium tier.',
    'Requested more time to review the proposal internally.',
    'Pricing was a concern, offered to revisit in Q2.',
    'Very happy with the onboarding experience so far.',
    'Escalated a technical issue to the support team.',
    'Decision maker is now the CFO, not the original contact.',
    'Asked for case studies from similarly-sized companies.',
    'Confirmed renewal for another 12 months.',
    'Wants delivery before the end of the month.',
    'Competitor is also being evaluated, they are price-sensitive.',
    'Requested a custom integration with their existing tools.',
    'Team is still deciding internally, follow up next week.',
    'Very responsive over email, prefers that over calls.',
    'Flagged a bug during testing, passed along to engineering.',
    'Happy with support response time on the last ticket.',
    'Asked about our data retention and security policies.',
    'Introduced us to another team within their organization.',
  ];

  public function definition(): array
  {
    $note = $this->faker->randomElement($this->notes);

    return [
      'id' => (string) Str::uuid(),

      'name' => Str::limit($note, 40),
      'description' => $note,

      'created_at' => \Carbon\Carbon::instance($this->faker->dateTimeBetween('-2 years', 'now'))->utc(),
      'updated_at' => \Carbon\Carbon::instance($this->faker->dateTimeBetween('-1 year', 'now'))->utc(),
    ];
  }
}
