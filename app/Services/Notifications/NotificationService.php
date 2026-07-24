<?php

namespace App\Services\Notifications;

use App\Models\User;
use App\Models\BaseModule;
use App\Models\Module;
use App\Models\MeetingAttendee;
use App\Models\Modules\Meeting;
use App\Models\Modules\Task;
use App\Models\UserInvite;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Notifications\RecordActivityNotification;
use App\Notifications\RecordAssignedNotification;
use App\Notifications\ImpersonationNotification;
use App\Notifications\MeetingInviteNotification;
use App\Notifications\TaskDueSoonNotification;
use App\Notifications\UserInviteAcceptedNotification;
use App\Notifications\UserInviteExpiredNotification;

class NotificationService {

    /**
     * Shared "who gets this notification" guard all method below use.
     */
    private static function resolveRecipient(?string $userId, bool $skipSelf = true): ?User
    {
        if (! $userId || ($skipSelf && $userId === Auth::id())) {
            return null;
        }

        return User::find($userId);
    }

    /**
     * Notifies the new owner that a record was assigned to them, unless they
     * assigned it to themselves.
     */
    public static function notifyIfAssigned(BaseModule $model, Module $module, ?string $newOwnerId): void
    {
        $owner = self::resolveRecipient($newOwnerId);

        $owner?->notify(new RecordAssignedNotification(
            $module->slug,
            $model->id,
            $model->name ?? $model->number ?? null,
            Auth::user()?->name,
        ));
    }

    /**
     * Notifies the record's owner about activity on their record, unless
     * they're the one who caused it.
     */
    public static function notifyRecordActivity(BaseModule $model, Module $module, string $action): void
    {
        // exclude userinvites from RecordActivity since we have two notification types for invites -- accepted + expired
        if ($module->slug === 'userinvites') {
            return;
        }

        $owner = self::resolveRecipient($model->getAttribute('owner_id'));

        $owner?->notify(new RecordActivityNotification(
            $module->slug,
            $model->id,
            $model->name ?? $model->number ?? null,
            Auth::user()?->name,
            $action,
        ));
    }

    /**
     * Notifies the invited user about a meeting invite, unless they invited
     * themselves or the source isn't an internal user.
     */
    public static function notifyMeetingInvite(MeetingAttendee $attendee): void
    {
        if ($attendee->source_type !== 'user') {
            return;
        }

        $invitedUser = self::resolveRecipient($attendee->source_id);
        $meeting = Meeting::find($attendee->meeting_id);

        if (! $invitedUser || ! $meeting) {
            return;
        }

        $invitedUser->notify(new MeetingInviteNotification(
            $meeting->id,
            $meeting->name,
            Auth::user()?->name,
            $attendee->role,
        ));
    }

    /**
     * Notifies the inviting admin that their invite was accepted.
     */
    public static function notifyInviteAccepted(UserInvite $invite, ?string $acceptedUserName): void
    {
        $inviter = self::resolveRecipient($invite->invited_by, skipSelf: false);

        $inviter?->notify(new UserInviteAcceptedNotification($invite->id, $invite->email, $acceptedUserName));
    }

    /**
     * Notifies the inviting admin that their invite expired.
     */
    public static function notifyInviteExpired(UserInvite $invite): void
    {
        $inviter = self::resolveRecipient($invite->invited_by, skipSelf: false);

        $inviter?->notify(new UserInviteExpiredNotification($invite->id, $invite->email));
    }

    /**
     * Notifies a task's owner that it's due soon.
     */
    public static function notifyTaskDueSoon(Task $task): void
    {
        $owner = self::resolveRecipient($task->owner_id, skipSelf: false);

        $owner?->notify(new TaskDueSoonNotification($task->id, $task->name, $task->due_at));
    }

    /**
     * Notifies a user that their account was accessed via impersonation.
     */
    public static function notifyImpersonated(User $target, string $impersonatorName, Carbon $startedAt): void
    {
        $target->notify(new ImpersonationNotification($impersonatorName, $startedAt));
    }
}
