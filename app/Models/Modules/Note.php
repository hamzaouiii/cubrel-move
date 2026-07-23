<?php

namespace App\Models\Modules;

use App\Models\BaseModule;

class Note extends BaseModule
{
    protected $table = 'notes';

    protected $fillable = [
        'name',
        'description',
        'owner_id',
    ];
}
