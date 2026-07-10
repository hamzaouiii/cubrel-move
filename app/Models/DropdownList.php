<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class DropdownList extends Model
{
  use HasFactory, HasUuids;
  protected  $table = 'dropdown_lists';
  protected $keyType = 'string';
  public $incrementing = false;


  protected $fillable = [
    'id',
    'key',
    'field_key',
    'values',
    'is_draft',
    'is_status'
  ];

  protected $casts = [
    'values' => 'array',
    'is_status' => 'boolean',
  ];

  public static function get($key): DropdownList
  {
    return DropdownList::query()
      ->where('key', $key)
      ->first();
  }

}
