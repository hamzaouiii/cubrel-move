<?php

namespace App\Http\Controllers;

use App\Models\MeetingAttendee;
use App\Services\Audit\AuditService;
use App\Services\Notifications\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MeetingAttendeeController extends Controller
{
    private function values(string $key, string $field = 'value'): array
    {
        return collect(config("meeting_attendees.{$key}"))
            ->pluck($field)
            ->filter()
            ->values()
            ->all();
    }
// one organiser is allowed at a time - we demote all but one
      private function demoteOtherOrganizers(string $meetingId, string $exceptId): void
    {
        MeetingAttendee::query()
            ->where('meeting_id', $meetingId)
            ->where('role', 'organizer')
            ->where('id', '!=', $exceptId)
            ->update(['role' => 'required']);
    }

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'meeting_id' => ['required', 'uuid'],
        ]);
        
        /**
         * 1- organizer
         * 2 - required
         * 3 - optional
         **/
        $attendees = MeetingAttendee::query()
            ->where('meeting_id', $request->meeting_id)
            ->orderByRaw("CASE role WHEN 'organizer' THEN 0 WHEN 'required' THEN 1 WHEN 'optional' THEN 2 ELSE 3 END")
            ->orderBy('name')
            ->get();

        return response()->json($attendees);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'meeting_id' => ['required', 'uuid', 'exists:meetings,id'],
            'source_type' => ['nullable', 'string', Rule::in($this->values('source_modules', 'source_type'))],
            'source_id' => ['nullable', 'uuid', 'required_with:source_type'],
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                Rule::requiredIf(fn () => empty($request->input('source_type'))),
                'nullable', 'email', 'max:255',
            ],
            'role' => ['nullable', 'string', Rule::in($this->values('roles'))],
        ]);

        if (! empty($data['source_type'])) {
            $exists = MeetingAttendee::query()
                ->where('meeting_id', $data['meeting_id'])
                ->where('source_type', $data['source_type'])
                ->where('source_id', $data['source_id'])
                ->exists();

            if ($exists) {
                return response()->json([
                    'message' => __('modules.meeting_attendees.errors.already_added'),
                    'errors' => ['source_id' => __('modules.meeting_attendees.errors.already_added')],
                ], 422);
            }
        }

        $attendee = new MeetingAttendee($data);
        $attendee->id = Str::uuid();
        $attendee->role = $data['role'] ?? 'optional';
        $attendee->rsvp_status = 'invited';
        $attendee->owner_id = auth()->id();
        $attendee->save();

        if ($attendee->role === 'organizer') {
            $this->demoteOtherOrganizers($attendee->meeting_id, $attendee->id);
        }

        AuditService::log('meeting_attendee.added', 'meetings', $attendee->meeting_id, [
            'name' => $attendee->name,
            'role' => $attendee->role,
        ]);

        NotificationService::notifyMeetingInvite($attendee);

        return response()->json($attendee, 201);
    }

    public function update(Request $request, MeetingAttendee $meetingAttendee): JsonResponse
    {
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'role' => ['sometimes', 'string', Rule::in($this->values('roles'))],
            'rsvp_status' => ['sometimes', 'string', Rule::in($this->values('rsvp_statuses'))],
            'attendance_status' => ['nullable', 'string', Rule::in($this->values('attendance_statuses'))],
        ]);

        if (array_key_exists('rsvp_status', $data) && $data['rsvp_status'] !== $meetingAttendee->rsvp_status) {
            $data['responded_at'] = $data['rsvp_status'] === 'invited' ? null : now();
        }

        $meetingAttendee->fill($data)->save();

        if (($data['role'] ?? null) === 'organizer') {
            $this->demoteOtherOrganizers($meetingAttendee->meeting_id, $meetingAttendee->id);
        }

        return response()->json($meetingAttendee);
    }

    public function destroy(MeetingAttendee $meetingAttendee): JsonResponse
    {
        AuditService::log('meeting_attendee.removed', 'meetings', $meetingAttendee->meeting_id, [
            'name' => $meetingAttendee->name,
        ]);

        $meetingAttendee->delete();

        return response()->json(null, 204);
    }

    public function markAllAttended(Request $request): JsonResponse
    {
        $request->validate([
            'meeting_id' => ['required', 'uuid', 'exists:meetings,id'],
        ]);

        MeetingAttendee::query()
            ->where('meeting_id', $request->meeting_id)
            ->whereNull('attendance_status')
            ->update(['attendance_status' => 'attended']);

        AuditService::log('meeting_attendee.marked_all_attended', 'meetings', $request->meeting_id);

        return response()->json(null, 204);
    }
}
