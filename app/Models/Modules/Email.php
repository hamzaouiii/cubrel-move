<?php

namespace App\Models\Modules;

use Illuminate\Database\Eloquent\Model;
use App\Models\BaseModule;

class Email extends BaseModule
{
    protected $fillable = [
        'to', 'subject', 'mailable_class', 'related_id', 'status'
    ];
}