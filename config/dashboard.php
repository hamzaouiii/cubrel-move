<?php

/*
|--------------------------------------------------------------------------
| Dashboard Configuration
|--------------------------------------------------------------------------
|
| Single source of truth for allowed widget config values. AggregationService
| and DashboardController read these at validation time. The same array is
| shared to the frontend as the `dashboardConfig` Inertia page prop (see
| DashboardController::index()) — the frontend never redeclares these lists.
|
*/

return [

    'widget_types'              => ['time-series', 'metric', 'breakdown', 'record-list'],
    'org_wide_types'            => ['sales_manager'],

    'allowed_intervals'         => ['day', 'week', 'month'],
    'allowed_date_ranges'       => ['last_30_days', 'last_6_months', 'last_12_months', 'ytd'],
    'allowed_chart_types'       => ['bar', 'line'],
    'breakdown_chart_types'     => ['donut', 'bar'],
    'allowed_metrics'           => ['count', 'sum', 'avg'],
    'date_field_types'          => ['date', 'datetime'],
    'numeric_field_types'       => ['integer', 'decimal', 'currency', 'percentage'],
    'boolean_field_types'       => ['boolean', 'checkbox'],
    'dropdown_field_types'      => ['select', 'status', 'dropdown'],

];
