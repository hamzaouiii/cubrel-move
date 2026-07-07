<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImpersonationSession extends Model
{
    public $timestamps = false;

    protected $guarded = ['id'];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function impersonator()
    {
        return $this->belongsTo(User::class, 'impersonator_id');
    }

    public function targetUser()
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    /**
     * Seconds elapsed so far — works for both ended and still-ongoing sessions.
     */
    public function durationInSeconds(): int
    {
        return ($this->ended_at ?? now())->getTimestamp() - $this->started_at->getTimestamp();
    }

    public function toDisplayArray(): array
    {
        return [
            'id' => $this->id,
            'impersonator' => $this->impersonator?->only('id', 'name'),
            'target_user' => $this->targetUser?->only('id', 'name'),
            'ip_address' => $this->ip_address,
            'started_at' => $this->started_at,
            'ended_at' => $this->ended_at,
            'duration_seconds' => $this->durationInSeconds(),
            'ongoing' => is_null($this->ended_at),
        ];
    }
}
