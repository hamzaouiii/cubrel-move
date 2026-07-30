<?php

namespace App\Support;

use App\Models\Module;
use Illuminate\Support\Carbon;

/**
 * Renders a notification's title and body fresh, from the raw data stored in Notification::toArray()
 * */
class NotificationPresenter
{
    public static function present(string $type, array $data): array
    {
        return match ($type) {
            'record_assigned' => [
                'title' => __('globals.notifications.record_assigned.title', [
                    'module' => self::highlight(self::moduleLabel($data['module_slug'] ?? null) ?? ($data['module_slug'] ?? '')),
                ]),
                'body' => __('globals.notifications.record_assigned.body', [
                    'record' => self::highlight($data['record_label'] ?? $data['record_id'] ?? ''),
                    'user' => self::highlight($data['actor_name'] ?? __('globals.notifications.someone')),
                ]),
            ],
            'meeting_invite' => [
                'title' => __('globals.notifications.meeting_invite.title'),
                'body' => __('globals.notifications.meeting_invite.body', [
                    'meeting' => self::highlight($data['meeting_name'] ?? __('globals.notifications.a_meeting')),
                    'user' => self::highlight($data['actor_name'] ?? __('globals.notifications.someone')),
                ]),
            ],
            'task_due_soon' => [
                'title' => __('globals.notifications.task_due_soon.title'),
                'body' => __('globals.notifications.task_due_soon.body', [
                    'task' => self::highlight($data['task_name'] ?? $data['task_id'] ?? ''),
                    'due' => self::highlight(self::relativeTime($data['due_at'] ?? null)),
                ]),
            ],
            'invite_accepted' => [
                'title' => __('globals.notifications.invite_accepted.title'),
                'body' => __('globals.notifications.invite_accepted.body', [
                    'user' => self::highlight($data['accepted_user_name'] ?? $data['invite_email'] ?? ''),
                ]),
            ],
            'invite_expired' => [
                'title' => __('globals.notifications.invite_expired.title'),
                'body' => __('globals.notifications.invite_expired.body', [
                    'email' => self::highlight($data['invite_email'] ?? ''),
                ]),
            ],
            'record_activity' => [
                'title' => __('globals.notifications.record_activity.title', [
                    'module' => self::highlight(self::moduleLabel($data['module_slug'] ?? null) ?? ($data['module_slug'] ?? '')),
                ]),
                'body' => __("globals.notifications.record_activity.body.{$data['action']}", [
                    'record' => self::highlight($data['record_label'] ?? $data['record_id'] ?? ''),
                    'user' => self::highlight($data['actor_name'] ?? __('globals.notifications.someone')),
                ]),
            ],
            'impersonated' => [
                'title' => __('globals.notifications.impersonated.title'),
                'body' => __('globals.notifications.impersonated.body', [
                    'user' => self::highlight($data['actor_name'] ?? __('globals.notifications.someone')),
                    'time' => self::highlight(self::formattedTime($data['started_at'] ?? null)),
                ]),
            ],
            'record_converted' => [
                'title' => __('globals.notifications.record_converted.title', [
                    'module' => self::highlight(self::moduleLabel($data['source_module_slug'] ?? null) ?? ($data['source_module_slug'] ?? '')),
                ]),
                'body' => __('globals.notifications.record_converted.body', [
                    'user' => self::highlight($data['actor_name'] ?? __('globals.notifications.someone')),
                    'source_record' => self::highlight($data['source_record_label'] ?? $data['source_record_id'] ?? ''),
                    'target_module' => self::highlight(self::moduleLabel($data['target_module_slug'] ?? null) ?? ($data['target_module_slug'] ?? '')),
                    'target_record' => self::highlight($data['target_record_label'] ?? $data['target_record_id'] ?? ''),
                ]),
            ],
            'transformation_triggered' => [
                'title' => __('globals.notifications.transformation_triggered.title'),
                'body' => __('globals.notifications.transformation_triggered.body', [
                    'source_record' => self::highlight($data['source_record_label'] ?? $data['source_record_id'] ?? ''),
                    'target_module' => self::highlight(self::moduleLabel($data['target_module_slug'] ?? null) ?? ($data['target_module_slug'] ?? '')),
                    'target_record' => self::highlight($data['target_record_label'] ?? $data['target_record_id'] ?? ''),
                ]),
            ],
            'transformation_automation_failed' => [
                'title' => __('globals.notifications.transformation_automation_failed.title'),
                'body' => __('globals.notifications.transformation_automation_failed.body', [
                    'transformation' => self::highlight($data['transformation_name'] ?? ''),
                    'source_record' => self::highlight($data['source_record_label'] ?? $data['source_record_id'] ?? ''),
                    'reason' => self::highlight($data['reason'] ?? ''),
                ]),
            ],
            default => ['title' => '', 'body' => ''],
        };
    }

    
    private static function highlight(string $value): string
    {
        return '<span class="notification-highlight">'.e($value).'</span>';
    }

    // n+1 solution - public so toMail() implementations can reuse the same  memoized lookup for the module label shown in emails
    private static array $moduleLabelCache = [];

    public static function moduleLabel(?string $slug): ?string
    {
        if (! $slug) {
            return null;
        }

        if (! array_key_exists($slug, self::$moduleLabelCache)) {
            self::$moduleLabelCache[$slug] = Module::where('slug', $slug)->first()?->single_label;
        }

        return self::$moduleLabelCache[$slug];
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
