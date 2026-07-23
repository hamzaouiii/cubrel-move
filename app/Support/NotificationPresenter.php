<?php

namespace App\Support;

use App\Models\Module;
use Illuminate\Support\Carbon;

/**
 * Renders a notification's title/body fresh, from the raw data stored in
 * Notification::toArray()
 * */
class NotificationPresenter
{
    public static function present(string $type, array $data): array
    {
        return match ($type) {
            'record_assigned' => [
                'title' => __('globals.notifications.record_assigned.title', [
                    'module' => self::moduleLabel($data['module_slug'] ?? null) ?? ($data['module_slug'] ?? ''),
                ]),
                'body' => __('globals.notifications.record_assigned.body', [
                    'record' => $data['record_label'] ?? $data['record_id'] ?? '',
                    'user' => $data['actor_name'] ?? __('globals.notifications.someone'),
                ]),
            ],
            'meeting_invite' => [
                'title' => __('globals.notifications.meeting_invite.title'),
                'body' => __('globals.notifications.meeting_invite.body', [
                    'meeting' => $data['meeting_name'] ?? __('globals.notifications.a_meeting'),
                    'user' => $data['actor_name'] ?? __('globals.notifications.someone'),
                ]),
            ],
            'task_due_soon' => [
                'title' => __('globals.notifications.task_due_soon.title'),
                'body' => __('globals.notifications.task_due_soon.body', [
                    'task' => $data['task_name'] ?? $data['task_id'] ?? '',
                    'due' => self::relativeTime($data['due_at'] ?? null),
                ]),
            ],
            'invite_accepted' => [
                'title' => __('globals.notifications.invite_accepted.title'),
                'body' => __('globals.notifications.invite_accepted.body', [
                    'user' => $data['accepted_user_name'] ?? $data['invite_email'] ?? '',
                ]),
            ],
            'invite_expired' => [
                'title' => __('globals.notifications.invite_expired.title'),
                'body' => __('globals.notifications.invite_expired.body', [
                    'email' => $data['invite_email'] ?? '',
                ]),
            ],
            'record_activity' => [
                'title' => __('globals.notifications.record_activity.title', [
                    'module' => self::moduleLabel($data['module_slug'] ?? null) ?? ($data['module_slug'] ?? ''),
                ]),
                'body' => __("globals.notifications.record_activity.body.{$data['action']}", [
                    'record' => $data['record_label'] ?? $data['record_id'] ?? '',
                    'user' => $data['actor_name'] ?? __('globals.notifications.someone'),
                ]),
            ],
            'impersonated' => [
                'title' => __('globals.notifications.impersonated.title'),
                'body' => __('globals.notifications.impersonated.body', [
                    'user' => $data['actor_name'] ?? __('globals.notifications.someone'),
                    'time' => self::formattedTime($data['started_at'] ?? null),
                ]),
            ],
            default => ['title' => '', 'body' => ''],
        };
    }

    private static function moduleLabel(?string $slug): ?string
    {
        if (! $slug) {
            return null;
        }

        return Module::where('slug', $slug)->first()?->single_label;
    }

    private static function relativeTime(?string $iso): string
    {
        if (! $iso) {
            return '';
        }

        return Carbon::parse($iso)->locale(app()->getLocale())->diffForHumans();
    }

    private static function formattedTime(?string $iso): string
    {
        if (! $iso) {
            return '';
        }

        return Carbon::parse($iso)->locale(app()->getLocale())->translatedFormat('l, d.m.Y H:i');
    }
}
