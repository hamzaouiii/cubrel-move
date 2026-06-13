<?php

namespace App\Handlers\Modules;

use App\Models\PdfTemplate;
use Illuminate\Database\Eloquent\Builder;

class PdfTemplateModuleHandler extends BaseModuleHandler
{
    protected string $model = PdfTemplate::class;

    protected array $searchable = ['name', 'module_slug', 'description'];

    protected function query(array $params = []): Builder
    {
        return PdfTemplate::query();
    }
}
