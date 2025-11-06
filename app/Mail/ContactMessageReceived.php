<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\ContactMessage;
use App\Models\Email;

class ContactMessageReceived extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;
    protected ?int $sentEmailId = null;
    public ContactMessage $msg;

    public function __construct(ContactMessage $msg)
    {
        $this->msg = $msg;
    }

    public function build()
    {
        return $this->subject('Automatisierung Regensburg: New Lead from Contact form')
            ->replyTo($this->msg->email, $this->msg->name) 
            ->markdown('emails.contact.adminNotification');
    }
    public function withSentEmailId(int $id): static
    {
        $this->sentEmailId = $id;
        return $this;
    }
    public function __destruct()
    {
        if ($this->sentEmailId) {
            Email::where('id', $this->sentEmailId)
                ->update(['status' => 'sent']);
        }
    }
    public function failed(\Throwable $e): void
    {
        if ($this->sentEmailId) {
            Email::where('id', $this->sentEmailId)
                ->update(['status' => 'failed']);
        }
    }

}
