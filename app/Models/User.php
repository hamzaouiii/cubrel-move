<?php

namespace App\Models;

// I needed the User to be an Authenticatable and extend BaseModule at the same time. Since PHP does not allow multiple Inheritance, I needed to improvise.
// By Manually importing Authenticatable's contracts and traits I can extend BaseModule while keeping User as an Authenticatable

// Auth Interfaces (Contracts)
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

// Auth Traits that fulfill the Contracts above
use Illuminate\Auth\Authenticatable;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Auth\Passwords\CanResetPassword;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Concerns\HasFullName;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Models\BaseModule;

//  Extend BaseModule and implement the Auth Contracts
class User extends BaseModule implements
  AuthenticatableContract,
  AuthorizableContract,
  CanResetPasswordContract
{
  /** @use HasFactory<\Database\Factories\UserFactory> */
  use HasUuids, HasFactory, Notifiable;

  // 5. Use the Auth Traits inside the class
  use Authenticatable, Authorizable, CanResetPassword;

  public $incrementing = false;
  protected $keyType = 'string';

  protected $fillable = [
    'email',
    'password',
    'username'
  ];

  /**
   * The attributes that should be hidden for serialization.
   *
   * @var list<string>
   */
  protected $hidden = [
    'password',
    'remember_token',
  ];

  /**
   * Get the attributes that should be cast.
   *
   * @return array<string, string>
   */
  protected function casts(): array
  {
    return [
      'email_verified_at' => 'datetime',
      'password' => 'hashed',
      'is_admin' => 'boolean'
    ];
  }
  public function isAdmin(): bool
  {
    return (bool) $this->is_admin;
  }
}
