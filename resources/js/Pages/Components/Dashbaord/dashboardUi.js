/**
 * Frontend-only UI helpers for dashboard widgets.
 * Value lists that the backend also validates (allowed metrics, intervals,
 * chart types, field-type groups, filter operators) are NOT duplicated here —
 * they come from the `dashboardConfig` / `filterOperators` Inertia page props,
 * shared from config/dashboard.php and config/filter_operators.php
 * (see DashboardController::index()).
 */

export const CHART_PALETTE = [
  '#3b8bff',
  '#10b981',
  '#f59e0b',
  '#ef4444',
  '#8b5cf6',
  '#06b6d4',
  '#ec4899',
  '#84cc16',
]

// Cross-cutting operator behaviour — mirrors the same local constants used in
// FilterZone.vue. Not config: this just decides which value input to render.
export const EMPTY_OPERATORS   = ['is_empty', 'is_not_empty']
export const BETWEEN_OPERATORS = ['between']

// Builds { value, label } pairs from a raw value list (e.g. dashboardConfig.allowed_metrics)
// using the `globals.dashboard.opt_<value>` translation key convention.
export function buildOptions(t, values) {
  return (values ?? []).map((v) => ({ value: v, label: t(`globals.dashboard.opt_${v}`) }))
}

export function booleanOptions(t) {
  return [
    { value: '1', label: t('globals.dashboard.filter_yes') },
    { value: '0', label: t('globals.dashboard.filter_no') },
  ]
}
