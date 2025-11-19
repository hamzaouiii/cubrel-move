<?php

namespace App\Models\Modules;

use Illuminate\Database\Eloquent\Model;

class Email extends Model
{
    protected $fillable = [
        'to', 'subject', 'mailable_class', 'related_id', 'status'
    ];
}