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

$timeSeries = fn (string $module, string $chartType, string $dateRange, string $interval, string $label, array $filters = [], string $dateField = 'created_at') => [
    'type' => 'time-series',
    'cols' => 2,
    'config' => [
        'module' => $module,
        'dateField' => $dateField,
        'metric' => ['type' => 'count'],
        'interval' => $interval,
        'chartType' => $chartType,
        'dateRange' => $dateRange,
        'label' => $label,
        'filters' => $filters,
    ],
];

$breakdown = fn (string $module, string $groupBy, string $chartType, string $label, array $filters = []) => [
    'type' => 'breakdown',
    'cols' => 2,
    'config' => [
        'module' => $module,
        'groupBy' => $groupBy,
        'metric' => ['type' => 'count'],
        'chartType' => $chartType,
        'label' => $label,
        'filters' => [],
    ],
];

$recordList = fn (string $module, array $columns, string $sortField, string $label, int $limit = 10) => [
    'type' => 'record-list',
    'cols' => 1,
    'config' => [
        'module' => $module,
        'columns' => $columns,
        'sort' => ['field' => $sortField, 'direction' => 'desc'],
        'limit' => $limit,
        'label' => $label,
        'filters' => [],
    ],
];

// Moving-company filter sets (moves / moverequests modules — deals is deactivated,
// moverequests is its replacement pipeline)
$requestNewFilter = [['field' => 'status', 'operator' => 'equals', 'value' => 'neu']];
$requestConvertedFilter = [['field' => 'status', 'operator' => 'equals', 'value' => 'konvertiert']];
$requestOpenFilters = [
    ['field' => 'status', 'operator' => 'not_equals', 'value' => 'konvertiert'],
    ['field' => 'status', 'operator' => 'not_equals', 'value' => 'verloren'],
];
$moveUpcomingFilter = [['field' => 'status', 'operator' => 'equals', 'value' => 'geplant']];
$moveCompletedFilter = [['field' => 'status', 'operator' => 'equals', 'value' => 'abgeschlossen']];
$movePaidFilter = [['field' => 'zahlungsstatus', 'operator' => 'equals', 'value' => 'bezahlt']];

return [

    // ── Admin — org-wide across moves & move requests ────────────────────────
    'admin' => [
        $metric('moverequests', 'count', 'Neue Umzugsanfragen', 'fa-solid fa-inbox', '#e0f2fe', '#0369a1', $requestNewFilter),
        // $metric('moverequests', 'count', 'Konvertierte Anfragen', 'fa-regular fa-circle-check', '#ecfde3', '#026305', $requestConvertedFilter),
        $metric('moves', 'count', 'Anstehende Umzüge', 'fa-solid fa-truck-fast', '#f5efe8', '#7d6d2e', $moveUpcomingFilter),
        $metric('moves', 'count', 'Abgeschlossene Umzüge', 'fa-regular fa-circle-check', '#ecfdf5', '#065f46', $moveCompletedFilter),
        $metricField('moves', 'sum', 'endpreis', 'Umsatz (bezahlt)', 'fa-solid fa-euro-sign', '#ecfdf5', '#065f46', $movePaidFilter),
        // $recordList('moverequests', ['objekttyp', 'wunschtermin', 'geschaetzter_preis_von', 'status'], 'wunschtermin', 'Neue Umzugsanfragen', 10),
        // $recordList('moves', ['umzugstermin', 'status', 'anzahl_umzugshelfer', 'endpreis'], 'umzugstermin', 'Anstehende Umzüge', 10),
        // $metricField('moverequests', 'sum', 'angebotener_preis', 'Angebotssumme', 'fa-solid fa-euro-sign', '#f3e8ff', '#7c3aed', $requestOpenFilters),
        $breakdown('moverequests', 'quelle', 'donut', 'Anfragen nach Quelle'),
        $breakdown('moves', 'status', 'bar', 'Umzüge nach Status'),
        $timeSeries('moverequests', 'line', 'last_6_months', 'month', 'Umzugsanfragen im Zeitverlauf'),
        $timeSeries('moves', 'bar', 'last_6_months', 'month', 'Geplante Umzüge im Zeitverlauf', [], 'umzugstermin'),

    ],

    // ── Sales Rep — scoped to current user automatically ─────────────────────
    'sales_rep' => [
        $metric('moverequests', 'count', 'Meine neuen Anfragen', 'fa-solid fa-inbox', '#e0f2fe', '#0369a1', $requestNewFilter),
        $metric('moverequests', 'count', 'Meine konvertierten Anfragen', 'fa-regular fa-circle-check', '#e3f2fd', '#1565c0', $requestConvertedFilter),
        $metric('moves', 'count', 'Meine anstehenden Umzüge', 'fa-solid fa-truck-fast', '#e8f5e9', '#2e7d32', $moveUpcomingFilter),
        $metricField('moverequests', 'sum', 'angebotener_preis', 'Meine Angebotssumme', 'fa-solid fa-euro-sign', '#f3e8ff', '#7c3aed', $requestOpenFilters),
        'my-records',
        $recordList('moverequests', ['objekttyp', 'wunschtermin', 'geschaetzter_preis_von', 'status'], 'wunschtermin', 'Meine Umzugsanfragen', 10),
        $timeSeries('moverequests', 'bar', 'last_6_months', 'month', 'Meine Anfragen im Zeitverlauf'),
        $breakdown('moverequests', 'quelle', 'donut', 'Meine Anfragen nach Quelle'),
    ],

    // ── Sales Manager — org-wide, sales-team visibility ──────────────────────
    // Note: config/dashboard.php org_wide_types exempts this type from the implicit owner filter.
    'sales_manager' => [
        $metric('moverequests', 'count', 'Anfragen gesamt', 'fa-solid fa-inbox', '#e0f2fe', '#0369a1'),
        $metric('moverequests', 'count', 'Konvertierte Anfragen', 'fa-regular fa-circle-check', '#e3f2fd', '#1565c0', $requestConvertedFilter),
        $metricField('moverequests', 'sum', 'angebotener_preis', 'Angebotssumme', 'fa-solid fa-euro-sign', '#f3e8ff', '#7c3aed', $requestOpenFilters),
        $metricField('moves', 'sum', 'endpreis', 'Umsatz (bezahlt)', 'fa-solid fa-sack-dollar', '#ecfdf5', '#065f46', $movePaidFilter),
        $recordList('moverequests', ['objekttyp', 'wunschtermin', 'geschaetzter_preis_von', 'status'], 'wunschtermin', 'Aktuelle Umzugsanfragen', 10),
        $timeSeries('moverequests', 'bar', 'last_12_months', 'month', 'Umzugsanfragen im Zeitverlauf'),
        $breakdown('moverequests', 'quelle', 'donut', 'Anfragen nach Quelle'),
        $metric('moves', 'count', 'Anstehende Umzüge', 'fa-solid fa-truck-fast', '#e8f5e9', '#2e7d32', $moveUpcomingFilter),
        $breakdown('moves', 'status', 'donut', 'Umzüge nach Status'),
        $recordList('moves', ['umzugstermin', 'status', 'anzahl_umzugshelfer', 'endpreis'], 'umzugstermin', 'Anstehende Umzüge', 10),
    ],

    // ── Support Agent — scoped to current user, support-module focused ────────
    'support_agent' => [
        $metric('cases', 'count', 'My Cases', 'fa-solid fa-life-ring', '#fff7ed', '#c2410c'),
        $metric('cases', 'count', 'My Open Cases', 'fa-solid fa-ticket', '#fef3c7', '#d97706'),
        'my-records',
        $recordList('cases', [], 'created_at', 'My Recent Cases', 10),
        $timeSeries('cases', 'line', 'last_6_months', 'month', 'Cases over time'),
    ],

    // ── Marketing User — lead-generation and contact growth focus ─────────────
    'marketing_user' => [
        $metric('leads', 'count', 'Total Leads', 'fa-solid fa-bullseye', '#e8f5e9', '#2e7d32'),
        $metric('contacts', 'count', 'Total Contacts', 'fa-solid fa-address-book', '#e0f2fe', '#0369a1'),
        $metric('accounts', 'count', 'Accounts', 'fa-solid fa-building', '#ede9fe', '#5b21b6'),
        'my-records',
        $timeSeries('leads', 'line', 'last_6_months', 'month', 'New Leads over time'),
        $timeSeries('contacts', 'line', 'last_6_months', 'month', 'New Contacts over time'),
        $recordList('leads', [], 'created_at', 'Recent Leads', 10),
        $recordList('contacts', [], 'created_at', 'Recent Contacts', 10),
    ],

    // ── Read Only / Default — minimal, safe for any role ─────────────────────
    'read_only' => [
        $metric('moverequests', 'count', 'Umzugsanfragen', 'fa-solid fa-inbox', '#e0f2fe', '#0369a1'),
        $metric('moves', 'count', 'Anstehende Umzüge', 'fa-solid fa-truck-fast', '#e8f5e9', '#2e7d32', $moveUpcomingFilter),
        $metric('moves', 'count', 'Abgeschlossene Umzüge', 'fa-regular fa-circle-check', '#ecfdf5', '#065f46', $moveCompletedFilter),
        'my-records',
        $recordList('moves', ['umzugstermin', 'status', 'anzahl_umzugshelfer', 'endpreis'], 'umzugstermin', 'Anstehende Umzüge', 10),
    ],

];
