<?php

namespace App\Console\Commands;

use App\Mail\SetupInstanceMail;
use App\Models\User;
use App\Services\Users\SetupTokenService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class BootstrapInstance extends Command
{
  protected $signature = 'cubrel:bootstrap
    {email? : Address to send the setup link to. Prints the link instead if omitted}
    {--locale= : Locale for the setup email (en or de). Defaults to the instance locale}';
  protected $description = 'Generate a one-time setup link to create the first (root) user';

  public function handle(SetupTokenService $tokens): int
  {
    if (User::count() > 0) {
      $this->error('This instance already has users — setup has already run.');
      return self::FAILURE;
    }

    $email = $this->argument('email');

    if ($email && ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $this->error("'{$email}' is not a valid email address.");
      return self::FAILURE;
    }

    $locale = $this->option('locale');

    if ($locale && ! in_array($locale, ['en', 'de'], true)) {
      $this->error("'{$locale}' is not a supported locale. Use 'en' or 'de'.");
      return self::FAILURE;
    }

    $expiresAt = now()->addHours(SetupTokenService::TTL_HOURS);
    $token = $tokens->generate();
    $url = route('setup.show', array_filter(['token' => $token, 'locale' => $locale]));

    if ($email) {
      Mail::to($email)->send(new SetupInstanceMail($url, $expiresAt, $locale));
      $this->info("Setup link sent to {$email} (valid once, expires in 24 hours).");
      return self::SUCCESS;
    }

    $this->info('Setup link (valid once, expires in 24 hours):');
    $this->line($url);

    return self::SUCCESS;
  }
}
