<?php

namespace App\Models;

use App\Models\BaseModule;
use App\Support\Settings;
use Illuminate\Database\Eloquent\Prunable;

class UserInvite extends BaseModule
{
  use Prunable;

  protected $table = 'userinvites';

  protected $fillable = [
    'email',
    'invited_by',
    'token_hash',
    'is_admin',
    'expires_at',
    'accepted_at',
    'status',
    'owner_id'
  ];
  protected $casts = ['accepted_at' => 'datetime', 'expires_at' => 'datetime'];

  /**
   * The raw, unhashed token — only ever populated in memory right after
   * InviteService::create() issues a new invite, never persisted. Only
   * token_hash is stored in the DB, mirroring SetupToken.
   */
  public ?string $plainToken = null;

  public function isExpired(): bool
  {
    return $this->expires_at->isPast();
  }
  public function isPending(): bool
  {
    return is_null($this->accepted_at) && !$this->isExpired();
  }

  public function getStatusAttribute(): string
  {
    $dbStatus = $this->attributes['status'] ?? 'pending';

    if ($dbStatus === 'pending' && $this->isExpired()) {
      $this->timestamps = false;
      $this->forceFill(['status' => 'expired'])->save();
      return 'expired';
    }

    return $dbStatus;
  }

  /**
   * Only prunes resolved invites (accepted, or expired past the notification
   * job's expired_notified_at stamp) — pending invites are never touched.
   */
  public function prunable()
  {
    $cutoff = now()->subDays(Settings::get('retention_userinvites_days', 365));

    return static::where(function ($query) use ($cutoff) {
      $query->where('accepted_at', '<=', $cutoff)
        ->orWhere(function ($query) use ($cutoff) {
          $query->where('status', 'expired')
            ->where('expired_notified_at', '<=', $cutoff);
        });
    });
  }
}
