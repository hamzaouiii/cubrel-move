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
                'name' => 'number',
                'type' => 'number',
                'label' => 'modules.quotes.fields.number',
            ],
            2 => [
                'name' => 'valid_until',
                'type' => 'date',
                'label' => 'modules.quotes.fields.valid_until',
            ],
            3 => [
                'name' => 'status',
                'type' => 'select',
                'label' => 'modules.quotes.fields.status',
            ],
            4 => [
                'name' => 'total',
                'type' => 'number',
                'label' => 'modules.quotes.fields.total',
            ],
            5 => [
                'name' => 'owner_id',
                'type' => 'record',
                'label' => 'modules.defaults.owner_id',
            ],
            6 => [
                'name' => 'created_at',
                'type' => 'datetime',
                'label' => 'modules.defaults.created_at',
            ],
            7 => [
                'name' => 'updated_at',
                'type' => 'datetime',
                'label' => 'modules.defaults.updated_at',
            ],
        ],
    ],
    'record' => [
        'sections' => [
            0 => [
                'name' => 'General',
                'layout' => [
                    0 => [
                        'name' => 'name',
                        'type' => 'text',
                        'label' => 'modules.defaults.name',
                    ],
                    1 => [
                        'name' => 'status',
                        'type' => 'select',
                        'label' => 'modules.quotes.fields.status',
                    ],
                    2 => [
                        'name' => 'number',
                        'type' => 'number',
                        'label' => 'modules.quotes.fields.number',
                    ],

                    3 => [
                        'name' => 'owner_id',
                        'type' => 'record',
                        'label' => 'modules.defaults.owner_id',
                    ],
                    4 => [
                        'name' => 'description',
                        'type' => 'longtext',
                        'label' => 'modules.quotes.fields.description',
                    ],
                ],
            ],
            1 => [
                'name' => 'Financial',
                'layout' => [
                 0 => [
                        'name' => 'subtotal',
                        'type' => 'currency',
                        'label' => 'modules.invoices.fields.subtotal',
                    ],
                    1 => [
                        'name' => 'tax',
                        'type' => 'currency',
                        'label' => 'modules.invoices.fields.tax',
                    ],
                    2 => [
                        'name' => 'discount',
                        'type' => 'currency',
                        'label' => 'modules.invoices.fields.discount',
                    ],
                    3 => [
                        'name' => 'total',
                        'type' => 'currency',
                        'label' => 'modules.invoices.fields.total',
                    ],
                ],
            ],
            2 => [
                'name' => 'Dates',
                'layout' => [
                    0 => [
                        'name' => 'valid_until',
                        'type' => 'date',
                        'label' => 'modules.quotes.fields.valid_until',
                    ],
                    1 => [
                        'name' => 'updated_at',
                        'type' => 'datetime',
                        'label' => 'modules.defaults.updated_at',
                    ],
                    2 => [
                        'name' => 'created_at',
                        'type' => 'datetime',
                        'label' => 'modules.defaults.created_at',
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
                        'name' => 'accounts_quotes',
                        'type' => 'one-to-many',
                        'label' => 'relationships.accounts_quotes',
                        'fields' => [
                            0 => [
                                'name' => 'name',
                                'type' => 'text',
                                'label' => 'modules.accounts.fields.name',
                            ],
                            1 => [
                                'name' => 'email',
                                'type' => 'email',
                                'label' => 'modules.accounts.fields.email',
                            ],
                            2 => [
                                'name' => 'website',
                                'type' => 'url',
                                'label' => 'modules.accounts.fields.website',
                            ],
                            3 => [
                                'name' => 'phone',
                                'type' => 'phone',
                                'label' => 'modules.accounts.fields.phone',
                            ],
                        ],
                    ],
                    1 => [
                        'name' => 'deals_quotes',
                        'type' => 'one-to-many',
                        'label' => 'relationships.deals_quotes',
                        'fields' => [
                            0 => [
                                'name' => 'name',
                                'type' => 'text',
                                'label' => 'modules.deals.fields.name',
                            ],
                            1 => [
                                'name' => 'expected_close_date',
                                'type' => 'date',
                                'label' => 'modules.deals.fields.expected_close_date',
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
                                'name' => 'sales_stage',
                                'type' => 'select',
                                'label' => 'modules.deals.fields.sales_stage',
                            ],
                        ],
                    ],
                ],
            ],
            1 => [
                'layout' => [
                    0 => [
                        'name' => 'quotes_products',
                        'type' => 'many-to-many',
                        'label' => 'relationships.quotes_products',
                        'fields' => [
                            0 => [
                                'name' => 'name',
                                'type' => 'text',
                                'label' => 'modules.products.fields.name',
                            ],
                            1 => [
                                'name' => 'sku',
                                'type' => 'text',
                                'label' => 'modules.products.fields.sku',
                            ],
                            2 => [
                                'name' => 'category',
                                'type' => 'text',
                                'label' => 'modules.products.fields.category',
                            ],
                            3 => [
                                'name' => 'is_active',
                                'type' => 'checkbox',
                                'label' => 'modules.products.fields.is_active',
                            ],
                            4 => [
                                'name' => 'price',
                                'type' => 'number',
                                'label' => 'modules.products.fields.price',
                            ],
                        ],
                    ],
                    1 => [
                        'name' => 'quotes_invoices',
                        'type' => 'one-to-one',
                        'label' => 'relationships.quotes_invoices',
                        'fields' => [
                            0 => [
                                'name' => 'name',
                                'type' => 'text',
                                'label' => 'modules.invoices.fields.name',
                            ],
                            1 => [
                                'name' => 'number',
                                'type' => 'number',
                                'label' => 'modules.invoices.fields.number',
                            ],
                            2 => [
                                'name' => 'total',
                                'type' => 'number',
                                'label' => 'modules.invoices.fields.total',
                            ],
                            3 => [
                                'name' => 'status',
                                'type' => 'select',
                                'label' => 'modules.invoices.fields.status',
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
];
