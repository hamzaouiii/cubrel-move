<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SetupToken extends Model
{
    protected $fillable = [
        'token_hash',
        'expires_at',
        'used_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at'    => 'datetime',
    ];
}
