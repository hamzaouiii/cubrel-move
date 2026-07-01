<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\UserInvite;

class InvitationMail extends Mailable
{
  use Queueable, SerializesModels;

  public function __construct(public UserInvite $invite, public string $token)
  {
    $this->locale = \App\Support\Settings::locale();
  }

  public function envelope(): Envelope
  {
    return new Envelope(subject: __('emails.invitation.subject', ['app' => config('app.name', 'Cubrel')]));
  }

  public function content(): Content
  {
    return new Content(
      view: 'emails.invitation',
      with: [
        'inviteUrl'    => route('invites.show', $this->token),
        'expiresAt'    => $this->invite->expires_at->format('M j, Y'),
        'primaryColor' => \App\Support\Settings::get('primary_color', '#3498db'),
        'appName'      => config('app.name', 'Cubrel'),
        'appUrl'       => config('app.url'),
      ]
    );
  }
}
