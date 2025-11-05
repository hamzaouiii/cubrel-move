<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Email;

class ContactMessageConfirmation extends Mailable implements ShouldQueue
{
    use Queueable;

    public $contactMessage;
    protected ?int $sentEmailId = null;

    public function __construct($contactMessage)
    {
        $this->contactMessage = $contactMessage;
    }

    public function withSentEmailId(int $id): static
    {
        $this->sentEmailId = $id;
        return $this;
    }

    public function build()
    {
        return $this->subject('Bestätigung: Ihre Nachricht wurde erhalten')
                    ->markdown('emails.contact.confirmation', [
                        'contactMessage' => $this->contactMessage,
                    ]);
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
