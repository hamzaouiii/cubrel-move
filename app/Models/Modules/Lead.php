<?php

namespace App\Models\Modules;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lead extends Model
{
    use HasUuids,HasFactory;

    protected $fillable = [
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
}
