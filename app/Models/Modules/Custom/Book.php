<?php

namespace App\Models\Modules\Custom;

use App\Models\BaseModule;

class Book extends BaseModule
{
    protected $table = 'books_cstm';

    protected $guarded = [];
}
