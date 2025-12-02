<?php

namespace App\Models\Modules;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Models\BaseModule;

class ContactMessage extends BaseModule
{
      use HasUuids;
    protected $fillable = ['name','message','email','phone','status','ip','user_agent'];
}
