<?php

return [
    'list' => [
        'columns' => [
            0 => [
                'name' => 'name',
                'type' => 'text',
                'label' => 'modules.defaults.name',
                'sortable' => true,
            ],
            1 => [
                'name' => 'order_number',
                'type' => 'text',
                'label' => 'modules.orders.fields.order_number',
            ],
            2 => [
                'name' => 'status',
                'type' => 'select',
                'label' => 'modules.orders.fields.status',
            ],
            3 => [
                'name' => 'due_date',
                'type' => 'date',
                'label' => 'modules.orders.fields.due_date',
            ],
            4 => [
                'name' => 'owner_id',
                'type' => 'record',
                'label' => 'modules.defaults.owner_id',
            ],
            5 => [
                'name' => 'created_at',
                'type' => 'datetime',
                'label' => 'modules.defaults.created_at',
                'sortable' => true,
            ],
            6 => [
                'name' => 'updated_at',
                'type' => 'datetime',
                'label' => 'modules.defaults.updated_at',
                'sortable' => true,
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
                        'readonly' => false,
                        'required' => true,
                        'sortable' => true,
                    ],
                    1 => [
                        'name' => 'order_number',
                        'type' => 'text',
                        'label' => 'modules.orders.fields.order_number',
                        'readonly' => false,
                        'required' => true,
                    ],
                    2 => [
                        'name' => 'status',
                        'type' => 'select',
                        'label' => 'modules.orders.fields.status',
                        'readonly' => false,
                        'required' => false,
                    ],
                    3 => [
                        'name' => 'description',
                        'type' => 'longText',
                        'label' => 'modules.defaults.description',
                        'readonly' => false,
                        'required' => true,
                        'sortable' => true,
                    ],
                    4 => [
                        'name' => 'owner_id',
                        'type' => 'record',
                        'label' => 'modules.defaults.owner_id',
                    ],
                ],
            ],
            1 => [
                'name' => 'Line Items',
                'has_line_items' => true,
                'layout' => [
                    0 => ['name' => 'name', 'type' => 'text', 'label' => 'modules.line_items.fields.name'],
                    1 => ['name' => 'quantity', 'type' => 'decimal', 'label' => 'modules.line_items.fields.quantity'],
                    2 => ['name' => 'unit', 'type' => 'select', 'label' => 'modules.line_items.fields.unit'],
                    3 => ['name' => 'unit_price', 'type' => 'currency', 'label' => 'modules.line_items.fields.unit_price'],
                    4 => ['name' => 'discount', 'type' => 'percentage', 'label' => 'modules.line_items.fields.discount'],
                    5 => ['name' => 'tax_rate', 'type' => 'percentage', 'label' => 'modules.line_items.fields.tax_rate'],
                    6 => ['name' => 'total', 'type' => 'currency', 'label' => 'modules.line_items.fields.total'],
                ],
            ],
            2 => [
                'name' => 'Dates',
                'layout' => [
                    0 => [
                        'name' => 'due_date',
                        'type' => 'date',
                        'label' => 'modules.orders.fields.due_date',
                        'readonly' => false,
                        'required' => false,
                    ],
                    1 => [
                        'name' => 'order_date',
                        'type' => 'date',
                        'label' => 'modules.orders.fields.order_date',
                        'readonly' => false,
                        'required' => false,
                    ],
                    2 => [
                        'name' => 'created_at',
                        'type' => 'datetime',
                        'label' => 'modules.defaults.created_at',
                        'readonly' => true,
                        'required' => true,
                        'sortable' => true,
                    ],
                    3 => [
                        'name' => 'updated_at',
                        'type' => 'datetime',
                        'label' => 'modules.defaults.updated_at',
                        'readonly' => true,
                        'required' => true,
                        'sortable' => true,
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
                        'name' => 'accounts_orders',
                        'type' => 'one-to-many',
                        'label' => 'relationships.accounts_orders',
                        'fields' => [
                            0 => [
                                'name' => 'name',
                                'type' => 'text',
                                'label' => 'modules.accounts.fields.name',
                                'sortable' => false,
                            ],
                            1 => [
                                'name' => 'website',
                                'type' => 'url',
                                'label' => 'modules.accounts.fields.website',
                                'sortable' => false,
                            ],
                        ],
                    ],
                    1 => [
                        'name' => 'orders_invoices',
                        'type' => 'one-to-one',
                        'label' => 'relationships.orders_invoices',
                        'fields' => [
                            0 => [
                                'name' => 'name',
                                'type' => 'text',
                                'label' => 'modules.invoices.fields.name',
                                'sortable' => false,
                            ],
                            1 => [
                                'name' => 'number',
                                'type' => 'number',
                                'label' => 'modules.invoices.fields.number',
                                'sortable' => false,
                            ],
                            2 => [
                                'name' => 'status',
                                'type' => 'select',
                                'label' => 'modules.invoices.fields.status',
                                'sortable' => false,
                            ],
                            3 => [
                                'name' => 'total',
                                'type' => 'number',
                                'label' => 'modules.invoices.fields.total',
                                'sortable' => false,
                            ],
                        ],
                    ],
                ],
            ],
            1 => [
                'layout' => [
                    0 => [
                        'name' => 'deals_orders',
                        'type' => 'one-to-many',
                        'label' => 'relationships.deals_orders',
                        'fields' => [
                            0 => [
                                'name' => 'name',
                                'type' => 'text',
                                'label' => 'modules.deals.fields.name',
                                'sortable' => false,
                            ],
                            1 => [
                                'name' => 'sales_stage',
                                'type' => 'select',
                                'label' => 'modules.deals.fields.sales_stage',
                                'sortable' => false,
                            ],
                            2 => [
                                'name' => 'amount',
                                'type' => 'currency',
                                'label' => 'modules.deals.fields.amount',
                                'sortable' => false,
                            ],
                            3 => [
                                'name' => 'probability',
                                'type' => 'percentage',
                                'label' => 'modules.deals.fields.probability',
                                'sortable' => false,
                            ],
                            4 => [
                                'name' => 'type',
                                'type' => 'select',
                                'label' => 'modules.deals.fields.type',
                                'sortable' => false,
                            ],
                        ],
                    ],
                    1 => [
                        'name' => 'orders_products',
                        'type' => 'many-to-many',
                        'label' => 'relationships.orders_products',
                        'fields' => [
                            0 => [
                                'name' => 'name',
                                'type' => 'text',
                                'label' => 'modules.products.fields.name',
                                'sortable' => false,
                            ],
                            1 => [
                                'name' => 'category',
                                'type' => 'select',
                                'label' => 'modules.products.fields.category',
                                'sortable' => false,
                            ],
                            2 => [
                                'name' => 'is_active',
                                'type' => 'checkbox',
                                'label' => 'modules.products.fields.is_active',
                                'sortable' => false,
                            ],
                            3 => [
                                'name' => 'price',
                                'type' => 'currency',
                                'label' => 'modules.products.fields.price',
                                'sortable' => false,
                            ],
                            5 => [
                                'name' => 'sku',
                                'type' => 'text',
                                'label' => 'modules.products.fields.sku',
                                'sortable' => false,
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
                'sortable' => true,
            ],
            1 => [
                'name' => 'order_number',
                'type' => 'text',
                'label' => 'modules.orders.fields.order_number',
            ],
            2 => [
                'name' => 'status',
                'type' => 'select',
                'label' => 'modules.orders.fields.status',
            ],
        ],
    ],
];
