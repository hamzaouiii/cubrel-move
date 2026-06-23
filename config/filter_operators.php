<?php

/**
 * Single source of truth for which filter operators are valid per field type.
 * Consumed on the backend by App\Support\Filters\FilterOperators and shared
 * to the frontend (see ListController) for resources/js/Registries/operatorRegistry.js.
 */
return [
    'by_type' => [
        'text' => ['equals', 'not_equals', 'contains', 'not_contains', 'starts_with', 'is_empty', 'is_not_empty'],
        'longtext' => ['contains', 'not_contains', 'is_empty', 'is_not_empty'],
        'email' => ['equals', 'not_equals', 'contains', 'is_empty', 'is_not_empty'],
        'phone' => ['equals', 'contains', 'is_empty', 'is_not_empty'],
        'url' => ['equals', 'contains', 'is_empty', 'is_not_empty'],
        'integer' => ['equals', 'not_equals', 'greater_than', 'less_than', 'between', 'is_empty', 'is_not_empty'],
        'decimal' => ['equals', 'not_equals', 'greater_than', 'less_than', 'between', 'is_empty', 'is_not_empty'],
        'currency' => ['equals', 'not_equals', 'greater_than', 'less_than', 'between'],
        'percentage' => ['equals', 'not_equals', 'greater_than', 'less_than', 'between'],
        'boolean' => ['equals'],
        'checkbox' => ['equals'],
        'date' => ['equals', 'before', 'after', 'between', 'is_empty', 'is_not_empty'],
        'datetime' => ['equals', 'before', 'after', 'between', 'is_empty', 'is_not_empty'],
        'select' => ['equals', 'not_equals',  'is_empty', 'is_not_empty'],
        'status' => ['equals', 'not_equals'],
        'dropdown' => ['equals', 'not_equals'],
        'record' => ['equals', 'not_equals', 'is_empty', 'is_not_empty'],
        'address' => ['contains'],
    ],

    'default' => ['equals', 'not_equals', 'is_empty', 'is_not_empty'],
];
