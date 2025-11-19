<?php

namespace App\Models\Modules;

use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    protected $fillable = ['name','message','email','phone','status','ip','user_agent'];
}
