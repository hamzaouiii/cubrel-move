<?php

namespace App\Models\Modules;

use App\Models\BaseModule;
use App\Models\MeetingAttendee;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

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
        'owner_id',
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
            if ($meeting->start_at && $meeting->end_at && $meeting->start_at->gt($meeting->end_at)) {
                throw ValidationException::withMessages([
                    'end_at' => 'End time must be after start time.',
                ]);
            }

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
