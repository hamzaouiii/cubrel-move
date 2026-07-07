<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $guarded = ['id'];

    protected $casts = [
        'diff' => 'array',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function impersonator()
    {
        return $this->belongsTo(User::class, 'impersonator_id');
    }

    public function isImpersonated(): bool
    {
        return ! is_null($this->impersonator_id);
    }

    /**
     * Shared shape for both the global Audit Trail endpoint and the
     * per-record history endpoint. Impersonator identity is always
     * included when present.
     *
     * Exposed to the frontend as 'changes' (the natural name there); stored
     * in the 'diff' column/attribute to avoid colliding with Eloquent's own
     * protected $changes property.
     */
    public function toDisplayArray(): array
    {
        return [
            'id' => $this->id,
            'module_slug' => $this->module_slug,
            'record_id' => $this->record_id,
            'action' => $this->action,
            'changes' => $this->getAttribute('diff'),
            'created_at' => $this->created_at,
            'user' => $this->user?->only('id', 'name'),
            'impersonator' => $this->impersonator?->only('id', 'name'),
        ];
    }
}
