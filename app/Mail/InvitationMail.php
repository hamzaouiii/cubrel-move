<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\UserInvite;

// app/Mail/InvitationMail.php
class InvitationMail extends Mailable
{
  use Queueable, SerializesModels;

  public function __construct(public UserInvite $invite) {}

  public function envelope(): Envelope
  {
    return new Envelope(subject: 'You\'ve been invited to Cubrel');
  }

  public function content(): Content
  {
    return new Content(
      markdown: 'emails.invitation',
      with: [
        'inviteUrl' => route('invites.show', $this->invite->token),
        'expiresAt' => $this->invite->expires_at->format('M j, Y'),
      ]
    );
  }
}
