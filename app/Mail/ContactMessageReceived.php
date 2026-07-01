<?php

namespace App\Mail;

use App\Models\Modules\ContactMessage;
use App\Models\Modules\Email;
use App\Support\Settings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactMessageReceived extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected ?int $sentEmailId = null;

    public function __construct(public ContactMessage $msg)
    {
        $this->locale = Settings::locale();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject:  __('emails.contact_admin.subject', ['app' => config('app.name', 'Cubrel')]),
            replyTo: [new \Illuminate\Mail\Mailables\Address($this->msg->email, $this->msg->name)],
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.contact.adminNotification',
            with: ['msg' => $this->msg],
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
