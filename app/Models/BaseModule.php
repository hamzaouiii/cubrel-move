<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Concerns\HasTranslatableLabel;

abstract class BaseModule extends Model
{
    use HasUuids;
    use HasTranslatableLabel;
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
