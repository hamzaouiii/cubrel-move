<?php

namespace App\Support\Filters;

use App\Models\Module;
use Illuminate\Database\Eloquent\Builder;

class FilterQueryBuilder
{
    /**
     * Fields a filter is allowed to reference, keyed by column name.
     * Default fields (name, description, owner_id, created_at, updated_at) are always
     * eligible; custom DB fields must be explicitly marked filterable.
     *
     * @return array<string, \App\Models\Field>
     */
    public static function allowedFieldsMap(Module $module): array
    {
        return $module->builderFields()
            ->filter(fn ($field) => str_starts_with($field->key ?? '', 'default.') || $field->filterable === true)
            ->reject(fn ($field) => $field->name === 'owner_id' && ! $module->has_owner)
            ->keyBy('name')
            ->all();
    }

    /**
     * Whether every condition's field is filterable on the given module - used to hide
     * global/shared filters (e.g. 'my_records') from modules they don't make sense for
     * (e.g. a module with no owner_id field).
     *
     * @param array<int, array{field: string, operator: string, value: mixed}> $conditions
     */
    public static function isApplicable(Module $module, array $conditions): bool
    {
        $allowlist = static::allowedFieldsMap($module);

        foreach ($conditions as $condition) {
            if (! isset($allowlist[$condition['field'] ?? null])) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<int, array{field: string, operator: string, value: mixed}> $conditions
     */
    public static function apply(Builder $query, Module $module, array $conditions, string $matchType): void
    {
        $allowlist = static::allowedFieldsMap($module);

        $validConditions = collect($conditions)
            ->filter(function ($condition) use ($allowlist) {
                $field = $allowlist[$condition['field'] ?? null] ?? null;

                return $field && FilterOperators::isAllowed($field->type, $condition['operator'] ?? '');
            })
            ->values();

        if ($validConditions->isEmpty()) {
            return;
        }

        $boolean = $matchType === 'any' ? 'or' : 'and';

        $query->where(function (Builder $q) use ($validConditions, $allowlist, $boolean) {
            foreach ($validConditions as $condition) {
                $field = $allowlist[$condition['field']];
                $value = static::resolveValue($condition['value'] ?? null);
                static::applyCondition($q, $field->name, $field->type, $condition['operator'], $value, $boolean);
            }
        });
    }

    protected static function applyCondition(Builder $q, string $column, string $fieldType, string $operator, mixed $value, string $boolean): void
    {
        $isOr = $boolean === 'or';
        $method = $isOr ? 'orWhere' : 'where';

        match ($operator) {
            'equals' => $q->{$method}($column, '=', $value),
            'not_equals' => $q->{$method}($column, '!=', $value),
            'contains' => $q->{$method}($column, 'LIKE', "%{$value}%"),
            'not_contains' => $q->{$method}($column, 'NOT LIKE', "%{$value}%"),
            'starts_with' => $q->{$method}($column, 'LIKE', "{$value}%"),
            'greater_than' => $q->{$method}($column, '>', $value),
            'less_than' => $q->{$method}($column, '<', $value),
            'before' => $q->{$method}($column, '<', $value),
            'after' => $q->{$method}($column, '>', $value),
            'between' => $isOr
                ? $q->orWhere(fn (Builder $qq) => $qq->whereBetween($column, (array) $value))
                : $q->whereBetween($column, (array) $value),
            'in' => $isOr
                ? $q->orWhereIn($column, (array) $value)
                : $q->whereIn($column, (array) $value),
            'is_empty' => $isOr
                ? $q->orWhere(fn (Builder $qq) => $qq->whereNull($column)->orWhere($column, ''))
                : $q->where(fn (Builder $qq) => $qq->whereNull($column)->orWhere($column, ''))
            ,
            'is_not_empty' => $isOr
                ? $q->orWhere(fn (Builder $qq) => $qq->whereNotNull($column)->where($column, '!=', ''))
                : $q->where(fn (Builder $qq) => $qq->whereNotNull($column)->where($column, '!=', ''))
            ,
            default => null,
        };
    }

    protected static function resolveValue(mixed $value): mixed
    {
        if ($value === '@current_user') {
            return auth()->id();
        }

        if (is_array($value)) {
            return array_map(fn ($v) => $v === '@current_user' ? auth()->id() : $v, $value);
        }

        return $value;
    }
}
