<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class SetupInstanceMail extends Mailable
{
  use Queueable, SerializesModels;

  public function __construct(public string $setupUrl, public Carbon $expiresAt, ?string $locale = null)
  {
    $this->locale = $locale ?? \App\Support\Settings::locale();
  }

  public function envelope(): Envelope
  {
    return new Envelope(subject: __('emails.setup.subject', ['app' => config('app.name', 'Cubrel')]));
  }

  public function content(): Content
  {
    return new Content(
      view: 'emails.setup',
      with: [
        'setupUrl'     => $this->setupUrl,
        'expiresAt'    => $this->expiresAt->format('M j, Y g:i A'),
        'primaryColor' => \App\Support\Settings::get('primary_color', '#3498db'),
        'appName'      => config('app.name', 'Cubrel'),
        'appUrl'       => config('app.url'),
      ]
    );
  }
}
