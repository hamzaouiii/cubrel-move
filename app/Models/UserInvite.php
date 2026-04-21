<?php

namespace App\Models;

use App\Models\BaseModule;

class UserInvite extends BaseModule
{
  protected $table = 'user_invites';

  protected $fillable = [
    'email',
    'invited_by',
    'token',
    'is_admin',
    'expires_at',
    'accepted_at'
  ];
  protected $casts = ['accepted_at' => 'datetime', 'expires_at' => 'datetime'];

  public function isExpired(): bool
  {
    return $this->expires_at->isPast();
  }

  public function isPending(): bool
  {
    return is_null($this->accepted_at) && !$this->isExpired();
  }
}
