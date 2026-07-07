<?php

/**
 * Default fields for modules that have the flag 'has_line_items' flag set to true
 */
return [
    'subtotal' => [
        'name' => 'subtotal',
        'label' => 'modules.defaults.subtotal',
        'type' => 'currency',
        'readonly' => true,
        'is_calculated' => true,
    ],
    'discount_amount' => [
        'name' => 'discount_amount',
        'label' => 'modules.defaults.discount_amount',
        'type' => 'currency',
        'readonly' => true,
        'is_calculated' => true,
    ],
    'tax_amount' => [
        'name' => 'tax_amount',
        'label' => 'modules.defaults.tax_amount',
        'type' => 'currency',
        'readonly' => true,
        'is_calculated' => true,
    ],
    'total' => [
        'name' => 'total',
        'label' => 'modules.defaults.total',
        'type' => 'currency',
        'readonly' => true,
        'is_calculated' => true,
    ],

];
