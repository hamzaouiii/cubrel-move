<?php

namespace App\Notifications;

use App\Support\NotificationPresenter;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Tells the person whose edit triggered an automatic conversion rule
 * that it fired, since that happens silently inside a model observer
 * with no request/response cycle to show them a toast. Manual runs
 * don't need this, the person who clicked "Convert" already got one.
 */
class TransformationTriggeredNotification extends BaseAppNotification
{
    public function __construct(
        protected string $sourceModuleSlug,
        protected string $sourceRecordId,
        protected ?string $sourceRecordLabel,
        protected string $targetModuleSlug,
        protected string $targetRecordId,
        protected ?string $targetRecordLabel,
        protected string $transformationName,
    ) {}

    public function typeKey(): string
    {
        return 'transformation_triggered';
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
            'transformation_name' => $this->transformationName,
            'url' => "/{$this->targetModuleSlug}/{$this->targetRecordId}",
            'icon' => 'fa-solid fa-bolt',
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        $sourceModule = NotificationPresenter::moduleLabel($this->sourceModuleSlug) ?? $this->sourceModuleSlug;
        $targetModule = NotificationPresenter::moduleLabel($this->targetModuleSlug) ?? $this->targetModuleSlug;

        return (new MailMessage)
            ->subject(__('emails.notifications.transformation_triggered.subject'))
            ->line(__('emails.notifications.transformation_triggered.body', [
                'source_module' => $sourceModule,
                'source_record' => $this->sourceRecordLabel ?? $this->sourceRecordId,
                'transformation' => $this->transformationName,
                'target_module' => $targetModule,
                'target_record' => $this->targetRecordLabel ?? $this->targetRecordId,
            ]))
            ->action(__('emails.notifications.view_action'), url("/{$this->targetModuleSlug}/{$this->targetRecordId}"));
    }
}
