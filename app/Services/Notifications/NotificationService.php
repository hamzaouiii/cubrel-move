<?php

namespace App\Services\Notifications;

use App\Models\User;
use App\Models\BaseModule;
use App\Models\Module;
use App\Models\MeetingAttendee;
use App\Models\Modules\Meeting;
use App\Models\Modules\Task;
use App\Models\Transformation;
use App\Models\UserInvite;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Notifications\RecordActivityNotification;
use App\Notifications\RecordAssignedNotification;
use App\Notifications\ImpersonationNotification;
use App\Notifications\MeetingInviteNotification;
use App\Notifications\RecordConvertedNotification;
use App\Notifications\TaskDueSoonNotification;
use App\Notifications\TransformationAutomationFailedNotification;
use App\Notifications\TransformationTriggeredNotification;
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

    /**
     * Notifies about a transformation run, manual or automatic. Two
     * different people can care about this and for different reasons:
     *
     * - The source record's owner is told their record was converted,
     *   this matters regardless of how the run was triggered, UNLESS
     *   they're the one who caused it (a manual run already shows them
     *   a success toast; an automatic run gets the more specific
     *   "your change triggered this" notification below instead).
     * - The actor is told their edit triggered an automatic conversion,
     *   only relevant for automatic runs, a manual run means they
     *   already know, they clicked the button themselves.
     */
    public static function notifyTransformationRun(
        Transformation $transformation,
        BaseModule $sourceRecord,
        BaseModule $targetRecord,
        ?User $actor,
        bool $automatic,
    ): void {
        $ownerId = $sourceRecord->getAttribute('owner_id');
        $actorIsOwner = $actor && $ownerId && $actor->id === $ownerId;

        if ($automatic && $actor) {
            $actor->notify(new TransformationTriggeredNotification(
                $transformation->source_module,
                $sourceRecord->id,
                $sourceRecord->name ?? $sourceRecord->number ?? null,
                $transformation->target_module,
                $targetRecord->id,
                $targetRecord->name ?? $targetRecord->number ?? null,
                $transformation->name,
            ));
        }

        if ($actorIsOwner) {
            return;
        }

        $owner = self::resolveRecipient($ownerId, skipSelf: false);

        $owner?->notify(new RecordConvertedNotification(
            $transformation->source_module,
            $sourceRecord->id,
            $sourceRecord->name ?? $sourceRecord->number ?? null,
            $transformation->target_module,
            $targetRecord->id,
            $targetRecord->name ?? $targetRecord->number ?? null,
            $actor?->name,
        ));
    }

    /**
     * Notifies every admin that an automatic conversion rule failed to run.
     * Transformations are Studio config with no "owner" field, so unlike the
     * other transformation notifications above there's no single record
     * owner to tell; admins are the only people who can go fix the rule.
     */
    public static function notifyTransformationAutomationFailed(
        Transformation $transformation,
        BaseModule $sourceRecord,
        string $reason,
    ): void {
        User::where('is_admin', true)->get()->each(fn (User $admin) => $admin->notify(
            new TransformationAutomationFailedNotification(
                $transformation->id,
                $transformation->name,
                $transformation->source_module,
                $sourceRecord->id,
                $sourceRecord->name ?? $sourceRecord->number ?? null,
                $reason,
            )
        ));
    }
}
