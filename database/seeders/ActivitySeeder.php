<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\Modules\Call;
use App\Models\Modules\Meeting;
use App\Models\Modules\Note;
use App\Models\Modules\Task;
use App\Services\Relationships\RelationshipService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

/**
 * Seeds Tasks/Calls/Meetings/Notes and links each one to a random existing
 * record from a has_activity module (Leads, Accounts, Deals, Orders, etc.),
 * so the Activities timeline sidebar has real linked data to show — rather
 * than a pool of activities that don't belong to anything.
 *
 * Must run after RelationshipSeeder (needs the has_activity x is_activity
 * relationships to already exist) and after DevSeeder (needs has_activity
 * module records to link to).
 */
class ActivitySeeder extends Seeder
{
  public function run(): void
  {
    $parentModules = Module::where('has_activity', true)->get();

    if ($parentModules->isEmpty()) {
      return;
    }

    $this->seedLinkedActivities(Task::class, 'tasks', $parentModules, 40);
    $this->seedLinkedActivities(Call::class, 'calls', $parentModules, 50);
    $this->seedLinkedActivities(Note::class, 'notes', $parentModules, 30);

    // Meetings realistically involve more than one record — attendees'
    // accounts/contacts alongside the deal or lead being discussed — so
    // link each one to a small handful of records instead of just one.
    $this->seedMeetings($parentModules, 25);
  }

  private function seedLinkedActivities(string $modelClass, string $activitySlug, Collection $parentModules, int $count): void
  {
    for ($i = 0; $i < $count; $i++) {
      $record = $modelClass::factory()->create();
      $this->linkToRandomRecord($record, $activitySlug, $parentModules->random());
    }
  }

  private function seedMeetings(Collection $parentModules, int $count): void
  {
    for ($i = 0; $i < $count; $i++) {
      $meeting = Meeting::factory()->create();

      $attendeeParentCount = min(random_int(1, 3), $parentModules->count());
      $chosenParents = $parentModules->random($attendeeParentCount);
      $chosenParents = $chosenParents instanceof Collection ? $chosenParents : collect([$chosenParents]);

      foreach ($chosenParents as $parentModule) {
        $this->linkToRandomRecord($meeting, 'meetings', $parentModule);
      }
    }
  }

  private function linkToRandomRecord(Model $activityRecord, string $activitySlug, Module $parentModule): void
  {
    $parentClass = $parentModule->model_class;

    if (! $parentClass || ! class_exists($parentClass)) {
      return;
    }

    $targetId = $parentClass::query()->inRandomOrder()->value('id');

    if (! $targetId) {
      return;
    }

    try {
      RelationshipService::link(
        "{$parentModule->slug}_{$activitySlug}",
        $parentModule->slug,
        (string) $targetId,
        (string) $activityRecord->id,
      );
    } catch (\Throwable $e) {
      // Relationship not seeded yet, or a random duplicate pick — skip it,
      // the record itself was already created either way.
    }
  }
}
