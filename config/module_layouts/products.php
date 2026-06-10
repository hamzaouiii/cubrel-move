<?php

return [
    'list' => [
        'columns' => [
            0 => [
                'name' => 'name',
                'type' => 'text',
                'label' => 'modules.defaults.name',
            ],
            1 => [
                'name' => 'is_active',
                'type' => 'checkbox',
                'label' => 'modules.products.fields.is_active',
            ],
            2 => [
                'name' => 'price',
                'type' => 'currency',
                'label' => 'modules.products.fields.price',
            ],
            3 => [
                'name' => 'sku',
                'type' => 'text',
                'label' => 'modules.products.fields.sku',
            ],
            4 => [
                'name' => 'category',
                'type' => 'select',
                'label' => 'modules.products.fields.category',
            ],
            5 => [
                'name' => 'unit',
                'type' => 'select',
                'label' => 'modules.products.fields.unit',
            ],
            6 => [
                'name' => 'owner_id',
                'type' => 'record',
                'label' => 'modules.defaults.owner_id',
            ],
            7 => [
                'name' => 'created_at',
                'type' => 'datetime',
                'label' => 'modules.defaults.created_at',
            ],
            8 => [
                'name' => 'updated_at',
                'type' => 'datetime',
                'label' => 'modules.defaults.updated_at',
            ],

        ],
    ],
    'record' => [
        'sections' => [
            0 => [
                'name' => 'Card',
                'layout' => [
                    0 => [
                        'name' => 'name',
                        'type' => 'text',
                        'label' => 'modules.defaults.name',
                    ],
                    1 => [
                        'name' => 'price',
                        'type' => 'number',
                        'label' => 'modules.products.fields.price',
                    ],
                    2 => [
                        'name' => 'is_active',
                        'type' => 'checkbox',
                        'label' => 'modules.products.fields.is_active',
                    ],
                    3 => [
                        'name' => 'unit',
                        'type' => 'select',
                        'label' => 'modules.products.fields.unit',
                    ],
                    4 => [
                        'name' => 'tax_rate',
                        'type' => 'percentage',
                        'label' => 'modules.products.fields.tax_rate',
                    ],
                    5 => [
                        'name' => 'category',
                        'type' => 'select',
                        'label' => 'modules.products.fields.category',
                    ],
                    6 => [
                        'name' => 'sku',
                        'type' => 'text',
                        'label' => 'modules.products.fields.sku',
                    ],
                    7 => [
                        'name' => 'description',
                        'type' => 'longText',
                        'label' => 'modules.defaults.description',
                    ],
                    8 => [
                        'name' => 'owner_id',
                        'type' => 'record',
                        'label' => 'modules.defaults.owner_id',
                    ],
                    9 => [
                        'name' => 'created_at',
                        'type' => 'datetime',
                        'label' => 'modules.defaults.created_at',
                    ],
                    10 => [
                        'name' => 'updated_at',
                        'type' => 'datetime',
                        'label' => 'modules.defaults.updated_at',
                    ],
                ],
            ],
        ],
    ],
    'related' => [
        'columns' => [
            0 => [
                'layout' => [
                    0 => [
                        'name' => 'quotes_products',
                        'type' => 'many-to-many',
                        'label' => 'relationships.quotes_products',
                        'fields' => [
                            0 => [
                                'name' => 'name',
                                'type' => 'text',
                                'label' => 'modules.quotes.fields.name',
                            ],
                            1 => [
                                'name' => 'valid_until',
                                'type' => 'date',
                                'label' => 'modules.quotes.fields.valid_until',
                            ],
                            2 => [
                                'name' => 'total',
                                'type' => 'number',
                                'label' => 'modules.quotes.fields.total',
                            ],
                            3 => [
                                'name' => 'status',
                                'type' => 'select',
                                'label' => 'modules.quotes.fields.status',
                            ],
                        ],
                    ],

                ],
            ],
            1 => [
                'layout' => [
                    0 => [
                        'name' => 'deals_products',
                        'type' => 'many-to-many',
                        'label' => 'relationships.deals_products',
                        'fields' => [
                            0 => [
                                'name' => 'name',
                                'type' => 'text',
                                'label' => 'modules.deals.fields.name',
                            ],
                            1 => [
                                'name' => 'sales_stage',
                                'type' => 'select',
                                'label' => 'modules.deals.fields.sales_stage',
                            ],
                            2 => [
                                'name' => 'type',
                                'type' => 'select',
                                'label' => 'modules.deals.fields.type',
                            ],
                            3 => [
                                'name' => 'probability',
                                'type' => 'percentage',
                                'label' => 'modules.deals.fields.probability',
                            ],
                            4 => [
                                'name' => 'expected_close_date',
                                'type' => 'date',
                                'label' => 'modules.deals.fields.expected_close_date',
                            ],
                        ],
                    ],
                                        1 => [
                        'name' => 'orders_products',
                        'type' => 'many-to-many',
                        'label' => 'relationships.orders_products',
                        'fields' => [
                            0 => [
                                'name' => 'order_number',
                                'type' => 'text',
                                'label' => 'modules.orders.fields.order_number',
                            ],
                            1 => [
                                'name' => 'total_amount',
                                'type' => 'currency',
                                'label' => 'modules.orders.fields.total_amount',
                            ],
                            2 => [
                                'name' => 'due_date',
                                'type' => 'date',
                                'label' => 'modules.orders.fields.due_date',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'linkingPanel' => [
        'columns' => [
            0 => [
                'name' => 'name',
                'type' => 'text',
                'label' => 'modules.defaults.name',
            ],
            1 => [
                'name' => 'price',
                'type' => 'currency',
                'label' => 'modules.products.fields.price',
            ],
            2 => [
                'name' => 'sku',
                'type' => 'text',
                'label' => 'modules.products.fields.sku',
            ],
            3 => [
                'name' => 'unit',
                'type' => 'select',
                'label' => 'modules.products.fields.unit',
            ],
            4 => [
                'name' => 'tax_rate',
                'type' => 'percentage',
                'label' => 'modules.products.fields.tax_rate',
            ],
        ],
    ],
];
