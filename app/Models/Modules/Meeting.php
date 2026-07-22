<?php

namespace App\Models\Modules;

use App\Models\BaseModule;
use App\Models\MeetingAttendee;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Meeting extends BaseModule
{
    protected $table = 'meetings';

    protected $fillable = [
        'name',
        'description',
        'location',
        'start_at',
        'end_at',
        'duration',
        'status',
    ];

    protected $moduleCasts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'duration' => 'integer',
    ];

    protected static function booted(): void
    {
        parent::booted();

        static::saving(function (self $meeting) {
            $meeting->duration = $meeting->start_at && $meeting->end_at
                ? $meeting->start_at->diffInMinutes($meeting->end_at, absolute: true)
                : null;
        });
    }

    public function getCasts(): array
    {
        return array_merge(parent::getCasts(), [
            'location' => 'array',
        ]);
    }

    public function attendees(): HasMany
    {
        return $this->hasMany(MeetingAttendee::class);
    }
}
