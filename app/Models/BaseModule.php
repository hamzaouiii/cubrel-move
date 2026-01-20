<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Concerns\HasTranslatableLabel;
use App\Concerns\HasCustomFields;

abstract class BaseModule extends Model
{
  use HasUuids;
  use HasTranslatableLabel;
  use HasCustomFields;

  protected $casts = [
    'custom_fields' => 'array',
  ];
  public $incrementing = false;
  protected $keyType = 'string';
  public function uniqueIds()
  {
    return ['id'];
  }
}
