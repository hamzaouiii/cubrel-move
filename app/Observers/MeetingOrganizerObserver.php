<?php

namespace App\Observers;

use App\Models\MeetingAttendee;
use App\Models\Modules\Meeting;
use App\Models\User;
use Illuminate\Support\Str;

class MeetingOrganizerObserver
{
  // every meeting will have at least one attendant - the meeting creator is that person by default - can be changed via the UI
    public function created(Meeting $meeting): void
    {
        if (empty($meeting->owner_id)) {
            return;
        }

        $owner = User::find($meeting->owner_id);

        if (! $owner) {
            return;
        }

        $attendee = new MeetingAttendee([
            'meeting_id' => $meeting->id,
            'source_type' => 'user',
            'source_id' => $owner->id,
            'name' => $owner->name,
            'email' => $owner->email,
            'role' => 'organizer',
            'rsvp_status' => 'accepted',
            'owner_id' => $meeting->owner_id,
        ]);
        $attendee->id = Str::uuid();
        $attendee->save();
    }
}
