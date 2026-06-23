<?php

namespace App\Support\Filters;

/**
 * Reads from config/filter_operators.php, the single source of truth for
 * which operators are valid per field type (also shared to the frontend
 * as the `filterOperators` page prop - see ListController and FilterZone.vue).
 */
class FilterOperators
{
    public static function allowedFor(string $fieldType): array
    {
        return config("filter_operators.by_type.{$fieldType}") ?? config('filter_operators.default');
    }

    public static function isAllowed(string $fieldType, string $operator): bool
    {
        return in_array($operator, self::allowedFor($fieldType), true);
    }
}
