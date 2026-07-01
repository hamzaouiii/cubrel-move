<?php

namespace App\Services\Users;

use App\Models\UserInvite;
use App\Models\User;
use Illuminate\Support\Str;
use App\Mail\InvitationMail;
use Illuminate\Support\Facades\Mail;


class InviteService
{
  /**
   * The token itself is the credential, so only its hash is ever
   * persisted (same pattern as SetupTokenService): a leaked DB row can't
   * be turned back into a usable invite link.
   */
  public function create(string $email, string $invitedBy, bool $is_admin = false): UserInvite
  {
    // Revoke any existing pending invite for this email
    UserInvite::where('email', $email)->whereNull('accepted_at')->delete();

    $rawToken = Str::random(64);

    $invite = UserInvite::create([
      'email'      => $email,
      'token_hash' => $this->hash($rawToken),
      'invited_by' => $invitedBy,
      'is_admin'       => $is_admin,
      'expires_at' => now()->addDays(7),
    ]);
    $invite->plainToken = $rawToken;

    Mail::to($invite->email)->send(new InvitationMail($invite, $rawToken));

    return $invite;
  }

  public function findByToken(string $rawToken): ?UserInvite
  {
    return UserInvite::where('token_hash', $this->hash($rawToken))->first();
  }

  public function accept(string $token, array $userData): User
  {
    $invite = $this->findByToken($token);

    abort_if($invite === null, 404);
    abort_if($invite->isExpired(), 410, 'Invite link has expired.');
    abort_if(!$invite->isPending(), 409, 'Invite has already been used.');

    $user = User::createFromAccountForm(
      array_merge($userData, ['email' => $invite->email]),
      ['is_admin' => $invite->is_admin]
    );

    $invite->update(
      [
        'accepted_at' => now(),
        'status' => 'accepted'
      ]
    );

    return $user;
  }

  protected function hash(string $rawToken): string
  {
    return hash('sha256', $rawToken);
  }
}
