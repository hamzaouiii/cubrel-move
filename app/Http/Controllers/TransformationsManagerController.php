<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\Transformation;
use App\Services\Relationships\RelationshipService;
use App\Services\Transformations\ExpressionEvaluator;
use App\Services\Transformations\InvalidExpressionException;
use App\Services\Transformations\TransformationContext;
use Illuminate\Http\Request;
use Inertia\Inertia;


class TransformationsManagerController extends Controller
{

    protected const STEP_ORDER = ['create_record', 'copy_fields', 'copy_relationships'];

    public function index(Request $request)
    {
        $moduleIcons = Module::pluck('icon', 'slug');


        $transformations = Transformation::with('steps')
            ->orderBy('name')
            ->get()
            ->map(function (Transformation $t) use ($moduleIcons) {
                $t->target_icon = $moduleIcons[$t->target_module] ?? null;

                return $t;
            });

        return Inertia::render('Settings/Transformations/List', [
            'transformations' => $transformations,
        ]);
    }

    public function create()
    {
        return Inertia::render('Settings/Transformations/Edit', [
            'transformation' => null,
            'transform_modules' => $this->moduleOptions(),
            'filterOperators' => config('filter_operators'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateRequest($request);

        $transformation = Transformation::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'source_module' => $validated['source_module'],
            'target_module' => $validated['target_module'],
            'enabled' => $validated['enabled'] ?? true,
            'automation_enabled' => $validated['automation_enabled'] ?? false,
            'conditions' => $validated['conditions'] ?? [],
            'conditions_match' => $validated['conditions_match'] ?? 'all',
            'link_records_enabled' => $validated['link_records_enabled'] ?? true,
        ]);

        $transformation->ensureRelationship();

        $this->syncSteps($transformation, $validated);

        return redirect()
            ->route('settings.transformations.edit', $transformation)
            ->with('success', 'Transformation created successfully.');
    }

    public function edit(Transformation $transformation)
    {
        $transformation->load('steps');

        return Inertia::render('Settings/Transformations/Edit', [
            'transformation' => $this->withResolvedConditionLabels($transformation),
            'transform_modules' => $this->moduleOptions(),
            'hasLinkedRecords' => $this->hasLinkedRecords($transformation),
            'filterOperators' => config('filter_operators'),
        ]);
    }


    
    protected function withResolvedConditionLabels(Transformation $transformation): array
    {
        $data = $transformation->toArray();

        $sourceModule = Module::where('slug', $transformation->source_module)->first();
        $fieldsByName = $sourceModule?->allFields()->keyBy('name') ?? collect();

        $data['conditions'] = collect($data['conditions'] ?? [])
            ->map(function (array $condition) use ($fieldsByName) {
                $field = $fieldsByName->get($condition['field'] ?? null);

                if (! $field || $field->type !== 'record' || empty($condition['value'])) {
                    return $condition;
                }

                $condition['valueLabel'] = $this->resolveRecordLabel($field->related_module, $condition['value']);

                return $condition;
            })
            ->all();

        return $data;
    }

    protected function resolveRecordLabel(?string $relatedModuleSlug, string $id): ?string
    {
        if (! $relatedModuleSlug) {
            return null;
        }

        $module = Module::where('slug', $relatedModuleSlug)->first();

        if (! $module || ! $module->model_class || ! class_exists($module->model_class)) {
            return null;
        }

        $record = $module->model_class::find($id);

        return $record ? ($record->name ?? $record->number ?? $id) : null;
    }

    /**
     * Whether this transformation's relationship already has record links
     */
    protected function hasLinkedRecords(Transformation $transformation): bool
    {
        if (! $transformation->relationship_id) {
            return false;
        }

        return \Illuminate\Support\Facades\DB::table('relationship_links')
            ->where('relationship_id', $transformation->relationship_id)
            ->exists();
    }

    public function update(Request $request, Transformation $transformation)
    {
        $validated = $this->validateRequest($request);

        $transformation->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'source_module' => $validated['source_module'],
            'target_module' => $validated['target_module'],
            'enabled' => $validated['enabled'] ?? true,
            'automation_enabled' => $validated['automation_enabled'] ?? false,
            'conditions' => $validated['conditions'] ?? [],
            'conditions_match' => $validated['conditions_match'] ?? 'all',
            'link_records_enabled' => $validated['link_records_enabled'] ?? true,
        ]);

        $transformation->ensureRelationship();

        $this->syncSteps($transformation, $validated);

        return redirect()
            ->route('settings.transformations.edit', $transformation)
            ->with('success', 'Transformation updated successfully.');
    }

    public function destroy(Transformation $transformation)
    {
        $transformation->delete();

        return redirect()
            ->route('settings.transformations.index')
            ->with('success', 'Transformation deleted.');
    }

    public function toggle(Transformation $transformation)
    {
        $transformation->update(['enabled' => ! $transformation->enabled]);

        return redirect()
            ->route('settings.transformations.index')
            ->with('success', $transformation->enabled
                ? 'Transformation enabled.'
                : 'Transformation disabled.');
    }

  
    public function validateExpression(Request $request)
    {
        $request->validate([
            'expression' => 'required|array',
            'expression.*.type' => 'required|in:text,field,helper',
            'expression.*.value' => 'nullable|string',
            'source_module' => 'required|string',
        ]);

        $module = Module::where('slug', $request->input('source_module'))->first();

        if (! $module || empty($module->model_class)) {
            return response()->json(['valid' => false, 'error' => 'Unknown source module.'], 422);
        }

        $dummySource = new $module->model_class();
        $context = new TransformationContext(
            transformation: new Transformation(['source_module' => $request->input('source_module'), 'target_module' => 'preview']),
            sourceRecord: $dummySource,
            sourceModuleSlug: $request->input('source_module'),
            targetModuleSlug: 'preview',
            actor: $request->user(),
        );

        try {
            app(ExpressionEvaluator::class)->evaluate($request->input('expression'), $context);

            return response()->json(['valid' => true]);
        } catch (InvalidExpressionException $e) {
            return response()->json(['valid' => false, 'error' => $e->getMessage()], 422);
        }
    }

    protected function validateRequest(Request $request): array
    {

        $allOperators = collect(config('filter_operators.by_type'))
            ->flatten()
            ->merge(config('filter_operators.default'))
            ->unique()
            ->values()
            ->all();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'source_module' => 'required|string',
            'target_module' => 'required|string|different:source_module',
            'enabled' => 'boolean',
            'automation_enabled' => 'boolean',
            'conditions' => 'array',
            'conditions.*.field' => 'required|string',
            'conditions.*.operator' => ['required', \Illuminate\Validation\Rule::in($allOperators)],
            'conditions.*.value' => 'nullable',
            'conditions_match' => 'nullable|in:all,any',
            'link_records_enabled' => 'boolean',
            'field_mappings' => 'array',
            'field_mappings.*.target_field' => 'required|string',
            'field_mappings.*.mode' => 'required|in:field,static,expression',
            'field_mappings.*.source_field' => 'nullable|string',
            'field_mappings.*.value' => 'nullable',
            'field_mappings.*.expression' => 'nullable|array',
            'field_mappings.*.expression.*.type' => 'required|in:text,field,helper',
            'field_mappings.*.expression.*.value' => 'nullable|string',
            'relationships' => 'array',
            'relationships.*' => 'string',
        ]);

        $duplicates = collect($validated['field_mappings'] ?? [])
            ->pluck('target_field')
            ->filter()
            ->duplicates();

        if ($duplicates->isNotEmpty()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'field_mappings' => __('globals.transformations.messages.duplicate_mapped_field', [
                    'fields' => $duplicates->unique()->implode(', '),
                ]),
            ]);
        }

        if (($validated['conditions_match'] ?? 'all') === 'all') {
            $conditionDuplicates = collect($validated['conditions'] ?? [])
                ->pluck('field')
                ->filter()
                ->duplicates();

            if ($conditionDuplicates->isNotEmpty()) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'conditions' => __('globals.transformations.messages.duplicate_condition_field_all', [
                        'fields' => $conditionDuplicates->unique()->implode(', '),
                    ]),
                ]);
            }
        }

    
        if ($validated['automation_enabled'] ?? false) {
            $hasCondition = collect($validated['conditions'] ?? [])
                ->contains(fn (array $c) => ! empty($c['field'] ?? null));

            if (! $hasCondition) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'conditions' => __('globals.transformations.messages.automation_requires_condition'),
                ]);
            }
        }

        return $validated;
    }

    protected function syncSteps(Transformation $transformation, array $validated): void
    {
        $transformation->steps()->delete();

        $configurations = [
            'create_record' => [],
            'copy_fields' => ['mappings' => $validated['field_mappings'] ?? []],
            'copy_relationships' => ['relationships' => $validated['relationships'] ?? []],
        ];

        $stepOrder = self::STEP_ORDER;

        if ($validated['link_records_enabled'] ?? true) {
            $stepOrder[] = 'link_records';
            $configurations['link_records'] = [];
        }

        foreach (array_values($stepOrder) as $order => $type) {
            $transformation->steps()->create([
                'order' => $order,
                'type' => $type,
                'configuration' => $configurations[$type],
            ]);
        }
    }

    protected function moduleOptions()
    {
        return Module::where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'slug', 'name', 'label', 'icon', 'color', 'has_line_items'])
            ->map(function (Module $module) {
                return [
                    'slug' => $module->slug,
                    'name' => $module->name,
                    'label' => $module->label,
                    'icon' => $module->icon,
                    'color' => $module->color,
                    'has_line_items' => $module->has_line_items,
                    'fields' => $module->allFields()->values(),
                    'relationships' => RelationshipService::getRelationshipForModule($module->slug)
                        ->map(fn ($r) => ['name' => $r->related_slug, 'label' => $r->label])
                        ->values(),
                ];
            });
    }
}
