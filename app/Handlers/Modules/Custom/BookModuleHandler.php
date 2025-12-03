<?php

namespace App\Handlers\Modules\Custom;

use App\Models\Modules\Custom\Book;
use Illuminate\Database\Eloquent\Builder;
use App\Handlers\Modules\BasePaginatedModuleHandler;

class BookModuleHandler extends BasePaginatedModuleHandler
{
    protected string $model = Book::class;

    protected function query(array $params = []): Builder
    {
        $query = Book::query();

        // apply filters if needed

        return $query;
    }
}
