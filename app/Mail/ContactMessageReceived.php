<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\ContactMessage;

class ContactMessageReceived extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public ContactMessage $msg;

    public function __construct(ContactMessage $msg)
    {
        $this->msg = $msg;
    }

    public function build()
    {
        return $this->subject('Neue Kontaktanfrage')
            ->replyTo($this->msg->email, $this->msg->name) 
            ->markdown('emails.contact_received');
    }
}
