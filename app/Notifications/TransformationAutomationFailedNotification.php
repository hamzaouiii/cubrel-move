<?php

namespace App\Notifications;

use App\Support\NotificationPresenter;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Tells admins an automatic conversion rule failed to run. The rule has no
 * owner to notify (Transformations are Studio config, not user-owned
 * records), so this always goes to every admin instead. Links to the rule's
 * edit page so they can fix the mapping.
 */
class TransformationAutomationFailedNotification extends BaseAppNotification
{
    public function __construct(
        protected string $transformationId,
        protected string $transformationName,
        protected string $sourceModuleSlug,
        protected string $sourceRecordId,
        protected ?string $sourceRecordLabel,
        protected string $reason,
    ) {}

    public function typeKey(): string
    {
        return 'transformation_automation_failed';
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => $this->typeKey(),
            'transformation_id' => $this->transformationId,
            'transformation_name' => $this->transformationName,
            'source_module_slug' => $this->sourceModuleSlug,
            'source_record_id' => $this->sourceRecordId,
            'source_record_label' => $this->sourceRecordLabel,
            'reason' => $this->reason,
            'url' => "/settings/transformations/{$this->transformationId}",
            'icon' => 'fa-solid fa-triangle-exclamation',
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        $sourceModule = NotificationPresenter::moduleLabel($this->sourceModuleSlug) ?? $this->sourceModuleSlug;

        return (new MailMessage)
            ->subject(__('emails.notifications.transformation_automation_failed.subject', [
                'transformation' => $this->transformationName,
            ]))
            ->line(__('emails.notifications.transformation_automation_failed.body', [
                'transformation' => $this->transformationName,
                'source_module' => $sourceModule,
                'source_record' => $this->sourceRecordLabel ?? $this->sourceRecordId,
                'reason' => $this->reason,
            ]))
            ->action(__('emails.notifications.view_action'), url("/settings/transformations/{$this->transformationId}"));
    }
}
