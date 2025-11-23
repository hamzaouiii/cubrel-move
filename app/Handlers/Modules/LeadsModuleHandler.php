<?php

namespace App\Handlers\Modules;

use App\Models\Modules\Lead;
use Illuminate\Database\Eloquent\Builder;

class LeadsModuleHandler extends BasePaginatedModuleHandler
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
    protected array $searchable = [
        'name',
        'email',
        'phone',
        'company',
    ];
    protected string $model = Lead::class;
    
}
