<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Module;
use App\Support\Settings;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;

class RelatedFieldController extends Controller
{
public function __invoke(Request $request, string $related_module): \Illuminate\Http\JsonResponse
{
    $moduleModel = Module::query()
        ->select('id', 'model_class', 'slug')
        ->where('slug', $related_module)
        ->where('is_active', true)
        ->first();

    if (!$moduleModel || !$moduleModel->model_class) {
        return response()->json(['error' => 'Module not found'], 404);
    }

    $perPage    = Settings::get('linking_panel_limit', 15);
    $search     = $request->string('q')->trim()->toString();
    $selectedId = $request->input('selected');

    $moduleModelClass = $moduleModel->model_class;

    // Uses DB layout -> config fallback -> global fallback
    $fields = $this->getFieldsForModuleFromLayout($moduleModel);

    $selectFields = array_unique([
        'id',
        'name',
        ...$fields
    ]);

    $query = $moduleModelClass::query();

    if ($search) {
        $query->where(function ($q) use ($search, $fields) {

            $q->where('name', 'like', "%{$search}%");

            foreach ($fields as $field) {
                if ($field !== 'name') {
                    $q->orWhere($field, 'like', "%{$search}%");
                }
            }
        });
    }

    if (empty($search) && $selectedId) {
        $query->orderByRaw(
            'CASE WHEN id = ? THEN 0 ELSE 1 END ASC',
            [$selectedId]
        );
    }

    $paginator = $query
        ->orderBy('name')
        ->paginate($perPage, $selectFields);

    return response()->json([
        'data'         => $paginator->items(),
        'current_page' => $paginator->currentPage(),
        'last_page'    => $paginator->lastPage(),
        'total'        => $paginator->total(),
    ]);
}
protected function getFieldsForModuleFromLayout(Module $module): array
{
    $layout = $module->linkingPanelLayout();

    if (!isset($layout['columns']) || !is_array($layout['columns'])) {
        return [];
    }

    return collect($layout['columns'])
        ->pluck('name')
        ->filter(fn ($field) => $field !== 'id')
        ->values()
        ->toArray();
}

}