<?php

namespace App\Handlers\Modules;

use App\Models\ContactMessage;
use Illuminate\Database\Eloquent\Builder;

class CustomerInquriesModuleHandler extends BasePaginatedModuleHandler
{
    protected function query(array $params = []): Builder
    {
        $query = ContactMessage::query();

        // apply filters here if needed

        return $query;
    }
}
