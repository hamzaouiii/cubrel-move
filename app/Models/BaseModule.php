<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

abstract class BaseModule extends Model
{
    use HasUuids;

    /**
     * IDs are not auto incrementing integers.
     */
    public $incrementing = false;

    /**
     * IDs are stored as strings.
     */
    protected $keyType = 'string';

    /**
     * By default HasUuids will generate a UUID for the primary key.
     */
    public function uniqueIds()
    {
        return ['id'];
    }
}
