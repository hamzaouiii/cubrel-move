<?php

namespace App\Models;

use App\Support\Settings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;

class SetupToken extends Model
{
    use Prunable;

    protected $fillable = [
        'token_hash',
        'expires_at',
        'used_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at'    => 'datetime',
    ];

    public function prunable()
    {
        return static::where(function ($query) {
            $query->whereNotNull('used_at')->orWhere('expires_at', '<=', now());
        })->where('created_at', '<=', now()->subDays(Settings::get('retention_setup_tokens_days', 90)));
    }
}
