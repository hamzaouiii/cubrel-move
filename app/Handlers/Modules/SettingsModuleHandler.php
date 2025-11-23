<?php

namespace App\Handlers\Modules;

use App\Models\Modules\Settings;
use Illuminate\Database\Eloquent\Builder;

class SettingsModuleHandler extends BasePaginatedModuleHandler
{
    /**
     * The Eloquent model class associated with this module.
     *
     * @var class-string
     */
    protected string $modelClass = Settings::class;

    protected function query(array $params = []): Builder
    {
        $query = Settings::query();

        // apply filters here if needed

        return $query;
    }
    // Optionally override baseQuery(), filters, etc.
}

