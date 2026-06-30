<?php

/*
|--------------------------------------------------------------------------
| Dashboard Preset Layouts
|--------------------------------------------------------------------------
|
| One entry per user.type value.  Items are widget definitions WITHOUT
| instanceIds — those are generated at runtime by DashboardPresets::layout().
| String items (e.g. 'my-records') are legacy widgets and pass through as-is.
|
| Org-wide vs. per-user scoping is controlled at query time:
|   - is_admin = true  → no implicit owner filter ever
|   - type in config('dashboard.org_wide_types') → no implicit owner filter
|   - all others → owner_id = auth()->id() injected automatically
|
| To add a new preset: add the key here and seed with DashboardPresetSeeder.
|
*/

$metric = fn (string $module, string $aggregate, string $label, string $icon, string $iconBg, string $iconColor, array $filters = []) => [
    'type' => 'metric',
    'cols' => 1,
    'config' => compact('module', 'aggregate', 'label', 'icon', 'iconBg', 'iconColor', 'filters'),
];

$metricField = fn (string $module, string $aggregate, string $field, string $label, string $icon, string $iconBg, string $iconColor, array $filters = []) => [
    'type' => 'metric',
    'cols' => 1,
    'config' => compact('module', 'aggregate', 'field', 'label', 'icon', 'iconBg', 'iconColor', 'filters'),
];

$timeSeries = fn (string $module, string $chartType, string $dateRange, string $interval, string $label, array $filters = []) => [
    'type' => 'time-series',
    'cols' => 4,
    'config' => [
        'module'    => $module,
        'dateField' => 'created_at',
        'metric'    => ['type' => 'count'],
        'interval'  => $interval,
        'chartType' => $chartType,
        'dateRange' => $dateRange,
        'label'     => $label,
        'filters'   => $filters,
    ],
];

$breakdown = fn (string $module, string $groupBy, string $chartType, string $label, array $filters = []) => [
    'type' => 'breakdown',
    'cols' => 2,
    'config' => [
        'module'    => $module,
        'groupBy'   => $groupBy,
        'metric'    => ['type' => 'count'],
        'chartType' => $chartType,
        'label'     => $label,
        'filters'   => [],
    ],
];

$recordList = fn (string $module, array $columns, string $sortField, string $label, int $limit = 10) => [
    'type' => 'record-list',
    'cols' => 1,
    'config' => [
        'module'  => $module,
        'columns' => $columns,
        'sort'    => ['field' => $sortField, 'direction' => 'desc'],
        'limit'   => $limit,
        'label'   => $label,
        'filters' => [],
    ],
];

// Shared filter sets
$wonFilter      = [['field' => 'sales_stage', 'operator' => 'equals',     'value' => 'closed_won']];
$lostFilter     = [['field' => 'sales_stage', 'operator' => 'equals',     'value' => 'closed_lost']];
$openFilters    = [
    ['field' => 'sales_stage', 'operator' => 'not_equals', 'value' => 'closed_won'],
    ['field' => 'sales_stage', 'operator' => 'not_equals', 'value' => 'closed_lost'],
];

return [

    // ── Admin — org-wide across all modules ──────────────────────────────────
    'admin' => [
        $metric('leads',  'count', 'Total Leads',  'fa-solid fa-users',              '#e8f5e9', '#2e7d32'),
        $metric('deals',  'count', 'Won Deals',    'fa-regular fa-circle-check',      '#e3f2fd', '#1565c0', $wonFilter),
        $metric('deals',  'count', 'Open Deals',   'fa-regular fa-clock',             '#fff3e0', '#e65100', $openFilters),
        $metric('deals',  'count', 'Lost Deals',   'fa-regular fa-circle-xmark',      '#fce4ec', '#c62828', $lostFilter),
        'my-records',
        $recordList('leads',  [],                                    'created_at', 'Recent Leads',  10),
        $timeSeries('deals',  'bar',  'last_6_months', 'month', 'Deals over time'),
        $breakdown( 'deals',  'sales_stage', 'donut',               'Deal Stages'),
        $recordList('orders', ['order_number', 'status', 'order_date', 'total'], 'order_date', 'Recent Orders', 5),
    ],

    // ── Sales Rep — scoped to current user automatically ─────────────────────
    'sales_rep' => [
        $metric('leads',  'count', 'My Leads',       'fa-solid fa-users',              '#e8f5e9', '#2e7d32'),
        $metric('deals',  'count', 'My Won Deals',   'fa-regular fa-circle-check',     '#e3f2fd', '#1565c0', $wonFilter),
        $metric('deals',  'count', 'My Open Deals',  'fa-regular fa-clock',            '#fff3e0', '#e65100', $openFilters),
        $metricField('deals', 'sum', 'amount', 'Pipeline Value', 'fa-solid fa-euro-sign', '#f3e8ff', '#7c3aed', $openFilters),
        'my-records',
        $recordList('leads', [],            'created_at', 'My Recent Leads', 10),
        $timeSeries('deals', 'bar', 'last_6_months', 'month', 'My Deals over time'),
        $breakdown( 'deals', 'sales_stage', 'donut', 'My Deal Stages'),
    ],

    // ── Sales Manager — org-wide, sales-team visibility ──────────────────────
    // Note: config/dashboard.php org_wide_types exempts this type from the implicit owner filter.
    'sales_manager' => [
        $metric('leads', 'count', 'Total Leads',    'fa-solid fa-users',              '#e8f5e9', '#2e7d32'),
        $metric('deals', 'count', 'Won Deals',      'fa-regular fa-circle-check',     '#e3f2fd', '#1565c0', $wonFilter),
        $metricField('deals', 'sum', 'amount', 'Won Revenue',      'fa-solid fa-trophy',         '#ecfdf5', '#065f46', $wonFilter),
        $metricField('deals', 'sum', 'amount', 'Pipeline Value',   'fa-solid fa-euro-sign',    '#f3e8ff', '#7c3aed', $openFilters),
        $recordList('leads',  [],                                    'created_at', 'Recent Leads',  10),
        $timeSeries('deals',  'bar',  'last_12_months', 'month', 'Deals over time'),
        $breakdown( 'deals',  'sales_stage', 'donut', 'Deal Stages'),
        $recordList('orders', ['order_number', 'status', 'order_date', 'total'], 'order_date', 'Recent Orders', 10),
    ],

    // ── Support Agent — scoped to current user, support-module focused ────────
    'support_agent' => [
        $metric('cases', 'count', 'My Cases',        'fa-solid fa-life-ring',  '#fff7ed', '#c2410c'),
        $metric('cases', 'count', 'My Open Cases',   'fa-solid fa-ticket',     '#fef3c7', '#d97706'),
        'my-records',
        $recordList('cases', [], 'created_at', 'My Recent Cases', 10),
        $timeSeries('cases', 'line', 'last_6_months', 'month', 'Cases over time'),
    ],

    // ── Marketing User — lead-generation and contact growth focus ─────────────
    'marketing_user' => [
        $metric('leads',    'count', 'Total Leads',    'fa-solid fa-bullseye', '#e8f5e9', '#2e7d32'),
        $metric('contacts', 'count', 'Total Contacts', 'fa-solid fa-address-book', '#e0f2fe', '#0369a1'),
        $metric('accounts', 'count', 'Accounts',       'fa-solid fa-building', '#ede9fe', '#5b21b6'),
        'my-records',
        $timeSeries('leads',    'line', 'last_6_months', 'month', 'New Leads over time'),
        $timeSeries('contacts', 'line', 'last_6_months', 'month', 'New Contacts over time'),
        $recordList('leads',    [], 'created_at', 'Recent Leads',    10),
        $recordList('contacts', [], 'created_at', 'Recent Contacts', 10),
    ],

    // ── Read Only / Default — minimal, safe for any role ─────────────────────
    'read_only' => [
        $metric('leads', 'count', 'Leads',      'fa-solid fa-users',          '#e8f5e9', '#2e7d32'),
        $metric('deals', 'count', 'Won Deals',  'fa-regular fa-circle-check', '#e3f2fd', '#1565c0', $wonFilter),
        $metric('deals', 'count', 'Open Deals', 'fa-regular fa-clock',        '#fff3e0', '#e65100', $openFilters),
        'my-records',
        $recordList('leads', [], 'created_at', 'Recent Leads', 10),
    ],

];
