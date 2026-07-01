<?php

namespace App\Mail;

use App\Models\Modules\Email;
use App\Support\Settings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactMessageConfirmation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected ?int $sentEmailId = null;

    public function __construct(public $contactMessage)
    {
        $this->locale = Settings::locale();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('emails.contact_confirmation.subject'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.contact.confirmation',
            with: ['contactMessage' => $this->contactMessage],
        );
    }

    public function withSentEmailId(int $id): static
    {
        $this->sentEmailId = $id;
        return $this;
    }

    public function __destruct()
    {
        if ($this->sentEmailId) {
            Email::where('id', $this->sentEmailId)->update(['status' => 'sent']);
        }
    }

    public function failed(\Throwable $e): void
    {
        if ($this->sentEmailId) {
            Email::where('id', $this->sentEmailId)->update(['status' => 'failed']);
        }
    }
}
