<?php

namespace App\Handlers\Modules;

use App\Models\Modules\Lead;
use Illuminate\Database\Eloquent\Builder;

class LeadModuleHandler extends BasePaginatedModuleHandler
{
    /**
     * Build the base query for leads.
     * Here you can apply filters based on $params later (search, status, etc.).
     */
    protected function query(array $params = []): Builder
    {
        $query = Lead::query();

        // example: if you later want filters:
        // if (!empty($params['status'])) {
        //     $query->where('status', $params['status']);
        // }

        return $query;
    }

    // If you want a different default per-page for leads, you can do:
    // protected function getPerPage(array $params): int
    // {
    //     return $params['perPage'] ?? 25;
    // }
}
