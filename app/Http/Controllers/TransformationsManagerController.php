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

    protected const EXCLUDED_MODULES = ['users', 'userinvites'];

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

        $allowedModuleSlugs = Module::where('is_active', true)
            ->whereNotIn('slug', self::EXCLUDED_MODULES)
            ->pluck('slug');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'source_module' => ['required', 'string', \Illuminate\Validation\Rule::in($allowedModuleSlugs)],
            'target_module' => ['required', 'string', 'different:source_module', \Illuminate\Validation\Rule::in($allowedModuleSlugs)],
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
            $targetLabels = $this->fieldLabelMap($validated['target_module'] ?? null);

            throw \Illuminate\Validation\ValidationException::withMessages([
                'field_mappings' => __('globals.transformations.messages.duplicate_mapped_field', [
                    'fields' => $duplicates->unique()
                        ->map(fn ($name) => $targetLabels->get($name, $name))
                        ->implode(', '),
                ]),
            ]);
        }

        if (($validated['conditions_match'] ?? 'all') === 'all') {
            $conditionDuplicates = collect($validated['conditions'] ?? [])
                ->pluck('field')
                ->filter()
                ->duplicates();

            if ($conditionDuplicates->isNotEmpty()) {
                $sourceLabels = $this->fieldLabelMap($validated['source_module'] ?? null);

                throw \Illuminate\Validation\ValidationException::withMessages([
                    'conditions' => __('globals.transformations.messages.duplicate_condition_field_all', [
                        'fields' => $conditionDuplicates->unique()
                            ->map(fn ($name) => $sourceLabels->get($name, $name))
                            ->implode(', '),
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

        $this->assertConditionFieldsAreKnown($validated);
        $this->assertRelationshipsAreKnown($validated);
        $this->assertRelationshipExistsForLinking($validated);
        $this->assertMappingsAreTypeCompatible($validated);
        $this->assertRequiredTargetFieldsAreMapped($validated);

        return $validated;
    }

    /**
     * link_records_enabled requires a Relationship already defined between the two
     * modules, transformations never create one themselves. Relationships are only
     * ever created explicitly through Module Manager.
     */
    protected function assertRelationshipExistsForLinking(array $validated): void
    {
        if (! ($validated['link_records_enabled'] ?? true)) {
            return;
        }

        if (! isset($validated['source_module'], $validated['target_module'])) {
            return;
        }

        $exists = RelationshipService::getRelationshipBetween(
            $validated['source_module'],
            $validated['target_module'],
        )->isNotEmpty();

        if (! $exists) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'link_records_enabled' => __('globals.transformations.messages.no_relationship_to_link'),
            ]);
        }
    }


    protected function assertConditionFieldsAreKnown(array $validated): void
    {
        $sourceModule = Module::where('slug', $validated['source_module'] ?? null)->first();

        if (! $sourceModule) {
            return;
        }

        $knownFields = $sourceModule->allFields()->pluck('name');

        $unknown = collect($validated['conditions'] ?? [])
            ->pluck('field')
            ->filter()
            ->reject(fn ($name) => $knownFields->contains($name));

        if ($unknown->isNotEmpty()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'conditions' => __('globals.transformations.messages.unknown_condition_field'),
            ]);
        }
    }

 
    protected function assertRelationshipsAreKnown(array $validated): void
    {
        $sourceModule = Module::where('slug', $validated['source_module'] ?? null)->first();

        if (! $sourceModule) {
            return;
        }

        $allowed = RelationshipService::getRelationshipForModule($sourceModule->slug)
            ->pluck('related_slug');

        if ($sourceModule->has_line_items) {
            $allowed->push('line_items');
        }

        $unknown = collect($validated['relationships'] ?? [])
            ->reject(fn ($key) => $allowed->contains($key));

        if ($unknown->isNotEmpty()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'relationships' => __('globals.transformations.messages.unknown_relationship'),
            ]);
        }
    }

    protected function assertMappingsAreTypeCompatible(array $validated): void
    {
        $sourceModule = Module::where('slug', $validated['source_module'] ?? null)->first();
        $targetModule = Module::where('slug', $validated['target_module'] ?? null)->first();

        if (! $sourceModule || ! $targetModule) {
            return;
        }

        $sourceFieldsByName = $sourceModule->allFields()->keyBy('name');
        $targetFieldsByName = $targetModule->allFields()->keyBy('name');
        $textLikeTypes = ['text', 'longtext', 'email', 'phone', 'url'];

        foreach ($validated['field_mappings'] ?? [] as $mapping) {
            $targetField = $targetFieldsByName->get($mapping['target_field'] ?? null);

            if (! $targetField) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'field_mappings' => __('globals.transformations.messages.unknown_mapped_field'),
                ]);
            }

            $incompatible = match ($mapping['mode'] ?? 'field') {
                'field' => $this->fieldModeIsIncompatible($mapping, $targetField, $sourceFieldsByName),
                'expression' => ! in_array($targetField->type, $textLikeTypes, true),
                'static' => $targetField->type === 'record'
                    && ! $this->staticRecordValueIsValid($mapping['value'] ?? null, $targetField),
                default => false,
            };

            if ($incompatible) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'field_mappings' => __('globals.transformations.messages.mapping_incompatible', [
                        'field' => __($targetField->label),
                    ]),
                ]);
            }
        }
    }

    protected function fieldModeIsIncompatible(array $mapping, $targetField, \Illuminate\Support\Collection $sourceFieldsByName): bool
    {
        $sourceField = $sourceFieldsByName->get($mapping['source_field'] ?? null);

        if (! $sourceField || $sourceField->type !== $targetField->type) {
            return true;
        }

        if ($targetField->type === 'record') {
            return $sourceField->related_module !== $targetField->related_module;
        }

        return false;
    }

    protected function staticRecordValueIsValid(?string $value, $targetField): bool
    {
        if (! $value || ! $targetField->related_module) {
            return false;
        }
        if ($value === '@current_user') {
            return $targetField->related_module === 'users';
        }

        $relatedModule = Module::where('slug', $targetField->related_module)->first();

        if (! $relatedModule || ! $relatedModule->model_class || ! class_exists($relatedModule->model_class)) {
            return false;
        }

        return $relatedModule->model_class::where('id', $value)->exists();
    }

    protected function fieldLabelMap(?string $moduleSlug): \Illuminate\Support\Collection
    {
        $module = $moduleSlug ? Module::where('slug', $moduleSlug)->first() : null;

        if (! $module) {
            return collect();
        }

        return $module->allFields()->mapWithKeys(fn ($field) => [$field->name => __($field->label)]);
    }

    protected function assertRequiredTargetFieldsAreMapped(array $validated): void
    {
        $targetModule = Module::where('slug', $validated['target_module'] ?? null)->first();

        if (! $targetModule) {
            return;
        }

        $requiredFieldNames = $targetModule->allFields()
            ->filter(fn ($field) => $field->required && ! $field->readonly && ! $field->is_calculated)
            ->pluck('name');

        $mappingsByTarget = collect($validated['field_mappings'] ?? [])->keyBy('target_field');

        $isMapped = function (string $name) use ($mappingsByTarget): bool {
            $mapping = $mappingsByTarget->get($name);

            if (! $mapping) {
                return false;
            }

            return match ($mapping['mode'] ?? 'field') {
                'static' => ($mapping['value'] ?? '') !== '' && $mapping['value'] !== null,
                'expression' => ! empty($mapping['expression']),
                default => ! empty($mapping['source_field'] ?? null),
            };
        };

        $unmapped = $requiredFieldNames->filter(fn ($name) => ! $isMapped($name));

        if ($unmapped->isNotEmpty()) {
            $targetFieldsByName = $targetModule->allFields()->keyBy('name');

            throw \Illuminate\Validation\ValidationException::withMessages([
                'field_mappings' => __('globals.transformations.messages.required_fields_must_be_mapped', [
                    'fields' => $unmapped
                        ->map(fn ($name) => __($targetFieldsByName->get($name)->label))
                        ->implode(', '),
                    'module' => __($targetModule->label),
                ]),
            ]);
        }
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
            ->whereNotIn('slug', self::EXCLUDED_MODULES)
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
