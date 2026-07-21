<?php

namespace Database\Factories\Modules;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TaskFactory extends Factory
{
  protected $model = \App\Models\Modules\Task::class;

  // Realistic CRM to-dos, title paired with a short elaborating note —
  // reads like something a rep would actually write, not lorem ipsum.
  protected array $templates = [
    ['title' => 'Follow up on proposal', 'description' => "Check in to see if they've had a chance to review the proposal we sent."],
    ['title' => 'Send updated contract', 'description' => 'Legal requested a few changes — resend the updated version for signature.'],
    ['title' => 'Schedule product demo', 'description' => 'Set up a live walkthrough of the platform for the evaluation team.'],
    ['title' => 'Confirm delivery date', 'description' => 'Double check the warehouse can hit the date we promised.'],
    ['title' => 'Review pricing with client', 'description' => 'They asked about volume discounts — put together an updated quote.'],
    ['title' => 'Prepare renewal quote', 'description' => 'Contract renews next month, get the numbers ready before the call.'],
    ['title' => 'Chase outstanding invoice', 'description' => 'Payment is a few days overdue, send a friendly reminder.'],
    ['title' => 'Send onboarding materials', 'description' => 'Share the getting-started guide and setup checklist.'],
    ['title' => 'Check in after installation', 'description' => 'Make sure everything is running smoothly a week after go-live.'],
    ['title' => 'Draft follow-up email', 'description' => 'Summarize what was discussed on the call and next steps.'],
    ['title' => 'Confirm meeting attendees', 'description' => 'Verify who from their side is joining before we send the invite.'],
    ['title' => 'Escalate to support team', 'description' => 'Customer reported a recurring issue, needs technical follow-up.'],
    ['title' => 'Verify shipping address', 'description' => 'Address on file looks outdated, confirm before we ship.'],
    ['title' => 'Request customer feedback', 'description' => 'It has been a month since delivery, ask how things are going.'],
    ['title' => 'Prepare quarterly report', 'description' => 'Pull together usage and satisfaction numbers for the QBR.'],
    ['title' => 'Send thank-you note', 'description' => 'They just signed, send a quick note welcoming them aboard.'],
    ['title' => 'Review contract terms', 'description' => 'Legal flagged a clause that needs a second look before signing.'],
    ['title' => 'Schedule kickoff call', 'description' => 'Get everyone lined up to start the project next week.'],
    ['title' => 'Follow up on demo feedback', 'description' => 'They mentioned some concerns during the demo, address them directly.'],
    ['title' => 'Update CRM with call notes', 'description' => "Log what was discussed before it's forgotten."],
  ];

  public function definition(): array
  {
    $statuses = ['not_started', 'in_progress', 'completed', 'deferred'];
    $priorities = ['low', 'medium', 'high'];

    $status = $this->faker->randomElement($statuses);
    $template = $this->faker->randomElement($this->templates);

    return [
      'id' => (string) Str::uuid(),

      'name' => $template['title'],
      'description' => $template['description'],

      'due_at' => \Carbon\Carbon::instance($this->faker->dateTimeBetween('-2 months', '+2 months'))->utc(),
      'status' => $status,
      'priority' => $this->faker->randomElement($priorities),
      'completed_at' => $status === 'completed'
        ? \Carbon\Carbon::instance($this->faker->dateTimeBetween('-2 months', 'now'))->utc()
        : null,

      'created_at' => \Carbon\Carbon::instance($this->faker->dateTimeBetween('-2 years', 'now'))->utc(),
      'updated_at' => \Carbon\Carbon::instance($this->faker->dateTimeBetween('-1 year', 'now'))->utc(),
    ];
  }
}
