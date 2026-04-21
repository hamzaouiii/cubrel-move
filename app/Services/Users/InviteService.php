<?php

namespace App\Services\Users;

use App\Models\UserInvite;
use App\Models\User;
use Illuminate\Support\Str;
use App\Mail\InvitationMail;
use Illuminate\Support\Facades\Mail;


class InviteService
{
  public function create(string $email, string $invitedBy, bool $is_admin = false): UserInvite
  {
    // Revoke any existing pending invite for this email
    UserInvite::where('email', $email)->whereNull('accepted_at')->delete();

    $invite = UserInvite::create([
      'email'      => $email,
      'token'      => Str::random(64),
      'invited_by' => $invitedBy,
      'is_admin'       => $is_admin,
      'expires_at' => now()->addDays(7),
    ]);
    Mail::to($invite->email)->send(new InvitationMail($invite));

    return $invite;
  }


  public function accept(string $token, array $userData): User
  {
    $invite = UserInvite::where('token', $token)->firstOrFail();

    abort_if($invite->isExpired(), 410, 'Invite link has expired.');
    abort_if(!$invite->isPending(), 409, 'Invite has already been used.');

    $user = User::create([
      'username' => $userData['username'],
      'name'     => $userData['name'],
      'email'    => $invite->email,
      'password' => bcrypt($userData['password']),
      'is_admin'     => $invite->is_admin,
    ]);

    $invite->update(
      [
        'accepted_at' => now(),
        'status' => 'accepted'
      ]
    );

    return $user;
  }
}
