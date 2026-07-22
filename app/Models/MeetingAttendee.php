<?php

namespace App\Models;

use App\Models\Modules\Meeting;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeetingAttendee extends Model
{
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'meeting_id',
        'source_type',
        'source_id',
        'name',
        'email',
        'role',
        'rsvp_status',
        'attendance_status',
        'responded_at',
        'owner_id',
    ];

    protected $casts = [
        'responded_at' => 'datetime',
    ];

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }
}
