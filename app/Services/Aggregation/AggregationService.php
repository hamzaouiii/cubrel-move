<?php

namespace App\Services\Aggregation;

use App\Models\Module;
use App\Models\Relationship;
use App\Scopes\AdminOnlyModuleScope;
use App\Services\Relationships\RelationshipService;
use App\Support\Filters\FilterQueryBuilder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AggregationService
{

    /**
     * Return a short list of record names for a module.
     *
     * Returns { rows: [{ id, name }], moduleSlug, moduleIcon, moduleColor }.
     */
    public static function recordList(Module $module, array $config): array
    {
        $limit   = min((int) ($config['limit'] ?? 10), 50);
        $filters = $config['filters'] ?? [];

        if (!empty($filters)) {
            $allowedFilterFields = FilterQueryBuilder::allowedFieldsMap($module);
            foreach ($filters as $condition) {
                $key = $condition['field'] ?? null;
                if (!isset($allowedFilterFields[$key])) {
                    abort(422, "Field '{$key}' is not allowed as a filter on this module.");
                }
            }
        }

        $modelClass = $module->model_class;
        $query = $modelClass::query()
            ->select(['id', 'name'])
            ->orderBy('created_at', 'desc')
            ->limit($limit);

        if (!empty($filters)) {
            FilterQueryBuilder::apply($query, $module, $filters, self::matchType($config));
        }

        $rows = $query->get()->map(fn ($r) => [
            'id'   => $r->id,
            'name' => $r->name ?? '—',
        ])->all();

        return [
            'rows'        => $rows,
            'moduleSlug'  => $module->slug,
            'moduleIcon'  => $module->icon,
            'moduleColor' => $module->color,
        ];
    }

    /**
     * Return a single aggregate value for a module.
     *
     * Returns { value: number }.
     */
    public static function metric(Module $module, array $config): array
    {
        $fields    = $module->allFields()->keyBy('name');
        $aggregate = $config['aggregate'] ?? 'count';

        if (!in_array($aggregate, config('dashboard.allowed_metrics'))) {
            abort(422, "Invalid aggregate.");
        }

        $aggField = null;
        if (in_array($aggregate, ['sum', 'avg'])) {
            $aggField = $fields->get($config['field'] ?? '');
            if (!$aggField || !in_array($aggField->type, config('dashboard.numeric_field_types'))) {
                abort(422, "Invalid or missing aggregate field.");
            }
        }

        $filters = $config['filters'] ?? [];
        if (!empty($filters)) {
            $allowedFilterFields = FilterQueryBuilder::allowedFieldsMap($module);
            foreach ($filters as $condition) {
                $key = $condition['field'] ?? null;
                if (!isset($allowedFilterFields[$key])) {
                    abort(422, "Field '{$key}' is not allowed as a filter on this module.");
                }
            }
        }

        $modelClass = $module->model_class;

        $selectSql = match ($aggregate) {
            'count' => DB::raw('COUNT(*) as metric_value'),
            'sum'   => DB::raw('SUM(`' . $aggField->name . '`) as metric_value'),
            'avg'   => DB::raw('AVG(`' . $aggField->name . '`) as metric_value'),
        };

        $query = $modelClass::query()->select($selectSql);

        if (!empty($filters)) {
            FilterQueryBuilder::apply($query, $module, $filters, self::matchType($config));
        }

        $value = (float) ($query->value('metric_value') ?? 0);

        return ['value' => $aggregate === 'count' ? (int) $value : $value];
    }

    /**
     * Group a module by a field and return counts/sums/averages per group.
     *
     * Returns { labels: string[], series: [{ data: number[] }] }.
     */
    public static function breakdown(Module $module, array $config): array
    {
        $fields = $module->allFields()->keyBy('name');

        // ── groupBy ───────────────────────────────────────────────────────────
        $groupByName = $config['groupBy'] ?? null;
        $groupByField = $fields->get($groupByName);

        if (!$groupByField) {
            abort(422, "Invalid or missing groupBy field.");
        }

        // ── metric ────────────────────────────────────────────────────────────
        $metricConfig = $config['metric'] ?? ['type' => 'count'];
        $metricType   = $metricConfig['type'] ?? 'count';

        if (!in_array($metricType, config('dashboard.allowed_metrics'))) {
            abort(422, "Invalid metric type.");
        }

        $metricField = null;
        if (in_array($metricType, ['sum', 'avg'])) {
            $metricField = $fields->get($metricConfig['field'] ?? '');
            if (!$metricField || !in_array($metricField->type, config('dashboard.numeric_field_types'))) {
                abort(422, "Invalid or missing metric field.");
            }
        }

        // ── chartType (passthrough) ───────────────────────────────────────────
        $chartType = $config['chartType'] ?? 'donut';
        if (!in_array($chartType, config('dashboard.breakdown_chart_types'))) {
            abort(422, "Invalid chartType.");
        }

        // ── filters ───────────────────────────────────────────────────────────
        $filters = $config['filters'] ?? [];
        if (!empty($filters)) {
            $allowedFilterFields = FilterQueryBuilder::allowedFieldsMap($module);
            foreach ($filters as $condition) {
                $key = $condition['field'] ?? null;
                if (!isset($allowedFilterFields[$key])) {
                    abort(422, "Field '{$key}' is not allowed as a filter on this module.");
                }
            }
        }

        $metricSql = match ($metricType) {
            'count' => DB::raw('COUNT(*) as metric_value'),
            'sum'   => DB::raw('SUM(`' . $metricField->name . '`) as metric_value'),
            'avg'   => DB::raw('AVG(`' . $metricField->name . '`) as metric_value'),
        };

        $modelClass = $module->model_class;
        $limit      = isset($config['limit']) ? (int) $config['limit'] : null;

        $query = $modelClass::query()
            ->select(DB::raw("`{$groupByField->name}` as label"), $metricSql)
            ->groupBy(DB::raw('label'))
            ->orderByDesc(DB::raw('metric_value'));

        if ($limit) {
            $query->limit($limit);
        }

        if (!empty($filters)) {
            FilterQueryBuilder::apply($query, $module, $filters, self::matchType($config));
        }

        $rows = $query->get();

        // Map raw DB values to human-readable labels for dropdown/select fields.
        $valueMap = [];
        if ($groupByField->dropdown_list) {
            foreach ($groupByField->dropdown_list->values ?? [] as $option) {
                $valueMap[$option['value']] = __($option['label']);
            }
        }

        $labels = $rows->pluck('label')->map(function ($v) use ($valueMap) {
            if ($v === null) return '(empty)';
            return $valueMap[$v] ?? $v;
        })->all();

        return [
            'labels' => $labels,
            'series' => [['data' => $rows->pluck('metric_value')->map(fn ($v) => (float) $v)->all()]],
        ];
    }

    /**
     * Build time-series data for a given module and config.
     *
     * Returns { labels: string[], series: [{ data: float[] }] }.
     * Empty buckets are filled with 0 so charts never skip periods.
     */
    public static function timeSeries(Module $module, array $config): array
    {
        $fields = $module->allFields()->keyBy('name');

        // ── dateField ─────────────────────────────────────────────────────────
        $dateFieldName = $config['dateField'] ?? null;
        $dateField     = $fields->get($dateFieldName);

        if (!$dateField || !in_array($dateField->type, config('dashboard.date_field_types'))) {
            abort(422, "Invalid or missing dateField.");
        }

        // ── metric ────────────────────────────────────────────────────────────
        $metricConfig = $config['metric'] ?? ['type' => 'count'];
        $metricType   = $metricConfig['type'] ?? 'count';

        if (!in_array($metricType, config('dashboard.allowed_metrics'))) {
            abort(422, "Invalid metric type.");
        }

        $metricField = null;
        if (in_array($metricType, ['sum', 'avg'])) {
            $metricField = $fields->get($metricConfig['field'] ?? '');
            if (!$metricField || !in_array($metricField->type, config('dashboard.numeric_field_types'))) {
                abort(422, "Invalid or missing metric field.");
            }
        }

        // ── interval ──────────────────────────────────────────────────────────
        $interval = $config['interval'] ?? 'month';
        if (!in_array($interval, config('dashboard.allowed_intervals'))) {
            abort(422, "Invalid interval.");
        }

        // ── dateRange ─────────────────────────────────────────────────────────
        $dateRange = $config['dateRange'] ?? 'last_6_months';
        if (!in_array($dateRange, config('dashboard.allowed_date_ranges'))) {
            abort(422, "Invalid dateRange.");
        }

        // ── chartType (passthrough — no query impact, still must be valid) ────
        $chartType = $config['chartType'] ?? 'bar';
        if (!in_array($chartType, config('dashboard.allowed_chart_types'))) {
            abort(422, "Invalid chartType.");
        }

        // ── filters ───────────────────────────────────────────────────────────
        $filters = $config['filters'] ?? [];
        if (!empty($filters)) {
            $allowedFilterFields = FilterQueryBuilder::allowedFieldsMap($module);
            foreach ($filters as $condition) {
                $fieldKey = $condition['field'] ?? null;
                if (!isset($allowedFilterFields[$fieldKey])) {
                    abort(422, "Field '{$fieldKey}' is not allowed as a filter on this module.");
                }
            }
        }

        // ── Resolve date window ───────────────────────────────────────────────
        [$start, $end] = self::resolveDateRange($dateRange);

        // ── Query ─────────────────────────────────────────────────────────────
        $bucketSql = self::bucketExpression($dateFieldName, $interval);

        $metricSql = match ($metricType) {
            'count' => DB::raw('COUNT(*) as metric_value'),
            'sum'   => DB::raw('SUM(`' . $metricField->name . '`) as metric_value'),
            'avg'   => DB::raw('AVG(`' . $metricField->name . '`) as metric_value'),
        };

        $modelClass = $module->model_class;
        $query = $modelClass::query()
            ->whereBetween($dateFieldName, [$start->toDateTimeString(), $end->toDateTimeString()])
            ->select(DB::raw("{$bucketSql} as bucket"), $metricSql)
            ->groupBy(DB::raw('bucket'))
            ->orderBy(DB::raw('bucket'));

        if (!empty($filters)) {
            FilterQueryBuilder::apply($query, $module, $filters, self::matchType($config));
        }

        $rows = $query->get()->keyBy('bucket');

        // ── Fill empty buckets ────────────────────────────────────────────────
        $labels = self::generateBuckets($interval, $start, $end);
        $data   = array_map(
            fn($label) => (float) ($rows->get($label)?->metric_value ?? 0),
            $labels
        );

        return [
            'labels' => $labels,
            'series' => [['data' => $data]],
        ];
    }

    /**
     * Leaderboard-style aggregate grouped by a person — either a direct
     * `record`-type FK field (e.g. `deals.owner_id -> users`), or a named
     * Relationship (e.g. `contacts_invoices` linking `invoices` to `contacts`
     * via the `relationship_links` table) for modules that only connect to
     * people through that system rather than a plain FK column.
     *
     * Returns { rows: [{ id, name, avatar, value }], peopleModuleSlug, aggregate }.
     */
    public static function people(Module $module, array $config): array
    {
        if (!empty($config['relationshipName'])) {
            $relationship = Relationship::where('name', $config['relationshipName'])->first();

            if (!$relationship) {
                abort(422, "Invalid or missing relationshipName.");
            }

            return self::peopleViaRelationship($module, $relationship, $config);
        }

        return self::peopleViaField($module, $config);
    }

    /**
     * Group by a direct `record`-type FK field on `module` (e.g. `owner_id`).
     */
    private static function peopleViaField(Module $module, array $config): array
    {
        $fields = $module->allFields()->keyBy('name');

        $relationFieldName = $config['relationField'] ?? null;
        $relationField = $fields->get($relationFieldName);

        if (!$relationField || $relationField->type !== 'record' || !$relationField->related_module) {
            abort(422, "Invalid or missing relationField.");
        }

        $peopleModule = self::resolvePeopleModule($relationField->related_module);
        [$aggregate, $aggField] = self::resolveAggregate($fields, $config);
        $filters = self::validatedFilters($module, $config);
        $limit = min((int) ($config['limit'] ?? 10), 50);

        $aggSql = match ($aggregate) {
            'count' => DB::raw('COUNT(*) as metric_value'),
            'sum'   => DB::raw('SUM(`' . $aggField->name . '`) as metric_value'),
            'avg'   => DB::raw('AVG(`' . $aggField->name . '`) as metric_value'),
        };

        $modelClass = $module->model_class;
        $query = $modelClass::query()
            ->select(DB::raw("`{$relationFieldName}` as person_id"), $aggSql)
            ->whereNotNull($relationFieldName)
            ->groupBy(DB::raw('person_id'))
            ->orderByDesc(DB::raw('metric_value'))
            ->limit($limit);

        if (!empty($filters)) {
            FilterQueryBuilder::apply($query, $module, $filters, self::matchType($config));
        }

        $ranked = $query->get()->map(fn ($r) => [
            'person_id' => $r->person_id,
            'value'     => (float) $r->metric_value,
        ]);

        return [
            'rows'             => self::buildPeopleRows($ranked, $peopleModule),
            'peopleModuleSlug' => $peopleModule->slug,
            'aggregate'        => $aggregate,
        ];
    }

    /**
     * Group by a named Relationship (`relationships` + `relationship_links`
     * tables) — for modules whose only link to a "people" module (e.g.
     * contacts, leads) is through that system rather than a plain FK column.
     *
     * The aggregate itself is computed in PHP rather than SQL: joining
     * `relationship_links` onto `module`'s table would make any of its
     * default columns (id/created_at/updated_at exist on both tables)
     * ambiguous for FilterQueryBuilder's plain, unqualified `where($column)`
     * calls. Fetching filtered rows first (no join) then grouping via the
     * separate relationship_links lookup avoids that without forking the
     * shared filter pipeline.
     */
    private static function peopleViaRelationship(Module $module, Relationship $relationship, array $config): array
    {
        if ($relationship->left_module !== $module->slug && $relationship->right_module !== $module->slug) {
            abort(422, "Relationship does not involve this module.");
        }

        $rel = RelationshipService::getWithSide($relationship, $module->slug);
        $peopleModule = self::resolvePeopleModule($rel->related_slug);

        $fields = $module->allFields()->keyBy('name');
        [$aggregate, $aggField] = self::resolveAggregate($fields, $config);
        $filters = self::validatedFilters($module, $config);

        $modelClass = $module->model_class;
        $selectCols = array_values(array_filter(['id', $aggField?->name]));
        $query = $modelClass::query()->select($selectCols);

        if (!empty($filters)) {
            FilterQueryBuilder::apply($query, $module, $filters, self::matchType($config));
        }

        $records = $query->get()->keyBy('id');

        if ($records->isEmpty()) {
            return ['rows' => [], 'peopleModuleSlug' => $peopleModule->slug, 'aggregate' => $aggregate];
        }

        $links = DB::table('relationship_links')
            ->where('relationship_id', $relationship->id)
            ->whereIn($rel->current_id_field, $records->keys())
            ->select($rel->current_id_field . ' as record_id', $rel->other_id_field . ' as person_id')
            ->get();

        $totals = [];
        foreach ($links as $link) {
            $record = $records->get($link->record_id);
            if (!$record) {
                continue;
            }

            $totals[$link->person_id] ??= ['count' => 0, 'sum' => 0.0];
            $totals[$link->person_id]['count']++;
            if ($aggField) {
                $totals[$link->person_id]['sum'] += (float) $record->{$aggField->name};
            }
        }

        $limit = min((int) ($config['limit'] ?? 10), 50);

        $ranked = collect($totals)
            ->map(fn ($t, $personId) => [
                'person_id' => $personId,
                'value'     => match ($aggregate) {
                    'count' => (float) $t['count'],
                    'sum'   => $t['sum'],
                    'avg'   => $t['count'] > 0 ? $t['sum'] / $t['count'] : 0.0,
                },
            ])
            ->sortByDesc('value')
            ->take($limit)
            ->values();

        return [
            'rows'             => self::buildPeopleRows($ranked, $peopleModule),
            'peopleModuleSlug' => $peopleModule->slug,
            'aggregate'        => $aggregate,
        ];
    }

    /**
     * AdminOnlyModuleScope hides the 'users'/'settings' modules from
     * non-admins — but that's about hiding module *navigation*, not about
     * whether a widget can look up a person's name/avatar for display.
     */
    private static function resolvePeopleModule(?string $slug): Module
    {
        $peopleModule = $slug
            ? Module::withoutGlobalScope(AdminOnlyModuleScope::class)
                ->where('slug', $slug)
                ->where('is_active', true)
                ->first()
            : null;

        if (!$peopleModule) {
            abort(422, "Related people module not found or inactive.");
        }

        return $peopleModule;
    }

    /**
     * @return array{0: string, 1: ?\App\Models\Field}
     */
    private static function resolveAggregate(\Illuminate\Support\Collection $fields, array $config): array
    {
        $aggregate = $config['aggregate'] ?? 'count';

        if (!in_array($aggregate, config('dashboard.allowed_metrics'))) {
            abort(422, "Invalid aggregate.");
        }

        $aggField = null;
        if (in_array($aggregate, ['sum', 'avg'])) {
            $aggField = $fields->get($config['field'] ?? '');
            if (!$aggField || !in_array($aggField->type, config('dashboard.numeric_field_types'))) {
                abort(422, "Invalid or missing aggregate field.");
            }
        }

        return [$aggregate, $aggField];
    }

    private static function validatedFilters(Module $module, array $config): array
    {
        $filters = $config['filters'] ?? [];

        if (!empty($filters)) {
            $allowedFilterFields = FilterQueryBuilder::allowedFieldsMap($module);
            foreach ($filters as $condition) {
                $key = $condition['field'] ?? null;
                if (!isset($allowedFilterFields[$key])) {
                    abort(422, "Field '{$key}' is not allowed as a filter on this module.");
                }
            }
        }

        return $filters;
    }

    /**
     * Joins name/avatar from the people module onto ranked { person_id, value } rows.
     */
    private static function buildPeopleRows(\Illuminate\Support\Collection $ranked, Module $peopleModule): array
    {
        $personIds = $ranked->pluck('person_id')->filter()->values()->all();

        $avatarFieldName = $peopleModule->allFields()->first(fn ($f) => $f->type === 'image')?->name;
        $selectCols = array_values(array_filter(['id', 'name', $avatarFieldName]));

        $peopleModelClass = $peopleModule->model_class;
        $people = $peopleModelClass::query()->whereIn('id', $personIds)->get($selectCols)->keyBy('id');

        return $ranked
            ->filter(fn ($r) => $people->has($r['person_id']))
            ->map(function ($r) use ($people, $avatarFieldName) {
                $person = $people->get($r['person_id']);

                return [
                    'id'     => $person->id,
                    'name'   => $person->name ?? '—',
                    'avatar' => $avatarFieldName ? ($person->{$avatarFieldName} ?? null) : null,
                    'value'  => $r['value'],
                ];
            })
            ->values()
            ->all();
    }

    private static function matchType(array $config): string
    {
        $type = $config['filtersMatchType'] ?? 'all';
        return in_array($type, ['all', 'any']) ? $type : 'all';
    }

    private static function resolveDateRange(string $dateRange): array
    {
        $now = now();

        return match ($dateRange) {
            'last_30_days'   => [$now->copy()->subDays(29)->startOfDay(),   $now->copy()->endOfDay()],
            'last_6_months'  => [$now->copy()->subMonths(5)->startOfMonth(), $now->copy()->endOfMonth()],
            'last_12_months' => [$now->copy()->subMonths(11)->startOfMonth(), $now->copy()->endOfMonth()],
            'ytd'            => [$now->copy()->startOfYear(),                $now->copy()->endOfDay()],
        };
    }

    private static function bucketExpression(string $field, string $interval): string
    {
        return match ($interval) {
            'day'   => "DATE(`{$field}`)",
            // Monday of the week — WEEKDAY() returns 0=Mon … 6=Sun
            'week'  => "DATE(DATE_SUB(`{$field}`, INTERVAL WEEKDAY(`{$field}`) DAY))",
            'month' => "DATE_FORMAT(`{$field}`, '%Y-%m')",
        };
    }

    private static function generateBuckets(string $interval, Carbon $start, Carbon $end): array
    {
        $labels  = [];
        $current = match ($interval) {
            'day'   => $start->copy()->startOfDay(),
            'week'  => $start->copy()->startOfWeek(Carbon::MONDAY),
            'month' => $start->copy()->startOfMonth(),
        };

        while ($current->lte($end)) {
            $labels[] = match ($interval) {
                'day', 'week' => $current->format('Y-m-d'),
                'month'       => $current->format('Y-m'),
            };

            match ($interval) {
                'day'   => $current->addDay(),
                'week'  => $current->addWeek(),
                'month' => $current->addMonth(),
            };
        }

        return $labels;
    }
}
