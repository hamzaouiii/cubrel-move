<?php

namespace App\Models\Modules;

use App\Models\BaseModule;

class Call extends BaseModule
{
    protected $table = 'calls';

    protected $fillable = [
        'name',
        'description',
        'direction',
        'call_at',
        'duration_minutes',
        'status',
        'outcome',
        'owner_id',
    ];

    protected $moduleCasts = [
        'call_at' => 'datetime',
        'duration_minutes' => 'integer',
    ];
}
