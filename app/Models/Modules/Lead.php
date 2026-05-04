<?php

namespace App\Models\Modules;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\BaseModule;

class Lead extends BaseModule
{
  use HasUuids, HasFactory;

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
    'owner_id'
  ];

  protected $keyType = 'string';
  public $incrementing = false;

  protected static function booted(): void
  {
    parent::booted();

    static::saving(function ($lead) {
      if ($lead->isDirty('first_name') || $lead->isDirty('last_name')) {
        $lead->name = trim($lead->first_name . ' ' . $lead->last_name);
      }
    });
  }
}
