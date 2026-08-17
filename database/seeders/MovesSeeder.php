<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Modules\Contact;
use App\Models\Modules\Moves;
use App\Models\Modules\Moverequests;
use App\Services\Relationships\RelationshipService;

class MovesSeeder extends Seeder
{
  public function run(): void
  {
    $moves = Moves::factory(25)->create();
    $moveRequests = Moverequests::factory(20)->create();

    $contactIds = Contact::pluck('id');

    if ($contactIds->isEmpty()) {
      return;
    }

    foreach ($moves as $move) {
      RelationshipService::link('contacts_moves', 'moves', $move->id, $contactIds->random());
    }

    foreach ($moveRequests as $moveRequest) {
      RelationshipService::link('contacts_moverequests', 'moverequests', $moveRequest->id, $contactIds->random());
    }

    // A subset of converted requests graduate into a scheduled move.
    $convertedRequests = $moveRequests->where('status', 'konvertiert')->values();
    $availableMoves = $moves->shuffle()->values();

    foreach ($convertedRequests as $index => $moveRequest) {
      $move = $availableMoves->get($index);

      if (!$move) {
        break;
      }

      RelationshipService::link('moverequests_moves', 'moverequests', $moveRequest->id, $move->id);
    }
  }
}
