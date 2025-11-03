<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IpWhitelist extends Model
{
    protected $fillable = ['ip', 'active', 'label'];
    protected $casts = ['active' => 'boolean'];
}