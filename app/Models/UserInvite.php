<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// UserInvite.php
class UserInvite extends Model
{
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
