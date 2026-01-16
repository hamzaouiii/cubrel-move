<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;


class Label extends Model
{
  use HasUuids;

  protected $fillable = [
    'key',
    'value',
    'module_id',
    'is_custom'
  ];
  public function uniqueIds()
  {
    return ['id'];
  }
}
