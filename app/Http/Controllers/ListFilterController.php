<?php

namespace App\Http\Controllers;

use App\Models\ListFilter;
use App\Models\Module;
use App\Support\Filters\FilterOperators;
use App\Support\Filters\FilterQueryBuilder;
use Illuminate\Http\Request;

class ListFilterController extends Controller
{
    public function store(Request $request, string $module)
    {
        $moduleModel = Module::query()->where('slug', $module)->where('is_active', true)->firstOrFail();
        $validated = $this->validateFilter($request, $moduleModel);

        ListFilter::create([
            'module_slug' => $moduleModel->slug,
            'name' => $validated['name'],
            'label' => $validated['name'],
            'user_id' => $request->user()->id,
            'is_shared' => $validated['is_shared'] ?? false,
            'is_system' => false,
            'conditions' => $validated['conditions'],
            'match_type' => $validated['match_type'],
        ]);

        return back();
    }

    public function update(Request $request, string $module, ListFilter $filter)
    {
        $moduleModel = Module::query()->where('slug', $module)->where('is_active', true)->firstOrFail();
        abort_if($filter->module_slug !== $moduleModel->slug, 404);
        abort_unless($filter->canManage($request->user()), 403);

        $validated = $this->validateFilter($request, $moduleModel);

        $filter->update([
            'name' => $validated['name'],
            'is_shared' => $validated['is_shared'] ?? $filter->is_shared,
            'conditions' => $validated['conditions'],
            'match_type' => $validated['match_type'],
        ]);

        return back();
    }

    public function destroy(Request $request, string $module, ListFilter $filter)
    {
        abort_if($filter->module_slug !== $module, 404);
        abort_unless($filter->canManage($request->user()), 403);

        $filter->delete();

        return back();
    }

    protected function validateFilter(Request $request, Module $moduleModel): array
    {
        $allowlist = FilterQueryBuilder::allowedFieldsMap($moduleModel);

        return $request->validate([
            'name' => 'required|string|max:255',
            'is_shared' => 'sometimes|boolean',
            'match_type' => 'required|in:all,any',
            'conditions' => 'required|array|min:1',
            'conditions.*.field' => ['required', 'string', function ($attribute, $value, $fail) use ($allowlist) {
                if (! isset($allowlist[$value])) {
                    $fail(__('modules.filters.invalid_field'));
                }
            }],
            'conditions.*.operator' => ['required', 'string', function ($attribute, $value, $fail) use ($request, $allowlist) {
                $index = explode('.', $attribute)[1];
                $fieldName = $request->input("conditions.{$index}.field");
                $fieldType = $allowlist[$fieldName]->type ?? null;

                if (! $fieldType || ! FilterOperators::isAllowed($fieldType, $value)) {
                    $fail(__('modules.filters.invalid_operator'));
                }
            }],
            'conditions.*.value' => 'present',
            'conditions.*.valueLabel' => 'sometimes',
        ]);
    }
}
