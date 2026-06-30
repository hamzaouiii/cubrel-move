<?php

namespace App\Services\Aggregation;

use App\Models\Module;
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
