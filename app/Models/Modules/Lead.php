<?php

namespace App\Models\Modules;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Lead extends Model
{
    use HasUuids,HasFactory;

    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'email',
        'phone',
        'company',
        'street',
        'city',
        'zip',
        'description',
    ];

    protected $keyType = 'string';
    public $incrementing = false;

    protected static function booted()  
    {
        static::saving(function ($lead) {
            // Only regenerate if first or last name changed
            if ($lead->isDirty('first_name') || $lead->isDirty('last_name')) {
                $lead->name = trim($lead->first_name . ' ' . $lead->last_name);
            }
        });
    }
}
