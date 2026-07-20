<?php

namespace App\Models\Modules;

use App\Models\BaseModule;

class Meeting extends BaseModule
{
    protected $table = 'meetings';

    protected $fillable = [
        'name',
        'description',
        'location',
        'start_at',
        'end_at',
        'status',
    ];

    protected $moduleCasts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    public function getCasts(): array
    {
        return array_merge(parent::getCasts(), [
            'location' => 'array',
        ]);
    }
}
