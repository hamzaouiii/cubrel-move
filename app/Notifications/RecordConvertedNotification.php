<?php

namespace App\Notifications;

use App\Support\NotificationPresenter;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Tells a source record's owner that their record was converted into a
 * new one, manual or automatic run does not matter. Not sent when the
 * owner is also the one who caused it, they already know: a manual run
 * shows them a success toast, an automatic run gets
 * TransformationTriggeredNotification instead.
 */
class RecordConvertedNotification extends BaseAppNotification
{
    public function __construct(
        protected string $sourceModuleSlug,
        protected string $sourceRecordId,
        protected ?string $sourceRecordLabel,
        protected string $targetModuleSlug,
        protected string $targetRecordId,
        protected ?string $targetRecordLabel,
        protected ?string $actorName,
    ) {}

    public function typeKey(): string
    {
        return 'record_converted';
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => $this->typeKey(),
            'source_module_slug' => $this->sourceModuleSlug,
            'source_record_id' => $this->sourceRecordId,
            'source_record_label' => $this->sourceRecordLabel,
            'target_module_slug' => $this->targetModuleSlug,
            'target_record_id' => $this->targetRecordId,
            'target_record_label' => $this->targetRecordLabel,
            'actor_name' => $this->actorName,
            'url' => "/{$this->targetModuleSlug}/{$this->targetRecordId}",
            'icon' => 'fa-solid fa-arrow-right-arrow-left',
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        $sourceModule = NotificationPresenter::moduleLabel($this->sourceModuleSlug) ?? $this->sourceModuleSlug;
        $targetModule = NotificationPresenter::moduleLabel($this->targetModuleSlug) ?? $this->targetModuleSlug;

        return (new MailMessage)
            ->subject(__('emails.notifications.record_converted.subject', ['module' => $sourceModule]))
            ->line(__('emails.notifications.record_converted.body', [
                'user' => $this->actorName ?? __('globals.notifications.someone'),
                'source_module' => $sourceModule,
                'source_record' => $this->sourceRecordLabel ?? $this->sourceRecordId,
                'target_module' => $targetModule,
                'target_record' => $this->targetRecordLabel ?? $this->targetRecordId,
            ]))
            ->action(__('emails.notifications.view_action'), url("/{$this->targetModuleSlug}/{$this->targetRecordId}"));
    }
}
