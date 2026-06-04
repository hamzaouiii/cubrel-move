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
            4 => [
                'name' => 'owner_id',
                'type' => 'record',
                'label' => 'modules.defaults.owner_id',
            ],
            5 => [
                'name' => 'created_at',
                'type' => 'datetime',
                'label' => 'modules.defaults.created_at',
            ],
            6 => [
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
                        'name' => 'number',
                        'type' => 'number',
                        'label' => 'modules.invoices.fields.number',
                    ],
                    2 => [
                        'name' => 'status',
                        'type' => 'select',
                        'label' => 'modules.invoices.fields.status',
                    ],
                    3 => [
                        'name' => 'due_date',
                        'type' => 'date',
                        'label' => 'modules.invoices.fields.due_date',
                    ],
                    4 => [
                        'name' => 'issue_date',
                        'type' => 'date',
                        'label' => 'modules.invoices.fields.issue_date',
                    ],
                ],
            ],
            1 => [
                'name' => 'Line Items',
                'has_line_items' => true,
                'layout' => [],
            ],
            2 => [
                'name' => 'Financial',
                'layout' => [
                    0 => [
                        'name' => 'subtotal',
                        'type' => 'number',
                        'label' => 'modules.invoices.fields.subtotal',
                    ],
                    1 => [
                        'name' => 'tax',
                        'type' => 'number',
                        'label' => 'modules.invoices.fields.tax',
                    ],
                    2 => [
                        'name' => 'total',
                        'type' => 'number',
                        'label' => 'modules.invoices.fields.total',
                    ],
                    3 => [
                        'name' => 'currency',
                        'type' => 'text',
                        'label' => 'modules.invoices.fields.currency',
                    ],
                ],
            ],
            3 => [
                'name' => 'Details',
                'layout' => [

                    0 => [
                        'name' => 'notes',
                        'type' => 'longtext',
                        'label' => 'modules.invoices.fields.notes',
                    ],
                    1 => [
                        'name' => 'description',
                        'type' => 'longtext',
                        'label' => 'modules.invoices.fields.description',
                    ],
                ],
            ],
            4 => [
                'name' => 'Dates',
                'layout' => [

                    0 => [
                        'name' => 'created_at',
                        'type' => 'datetime',
                        'label' => 'modules.invoices.fields.created_at',
                    ],
                    1 => [
                        'name' => 'updated_at',
                        'type' => 'datetime',
                        'label' => 'modules.invoices.fields.updated_at',
                    ],
                    2 => [
                        'name' => 'owner_id',
                        'type' => 'record',
                        'label' => 'modules.defaults.owner_id',
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
                        'name' => 'accounts_invoices',
                        'type' => 'one-to-many',
                        'label' => 'relationships.accounts_invoices',
                        'fields' => [
                            0 => [
                                'name' => 'name',
                                'type' => 'text',
                                'label' => 'modules.accounts.fields.name',
                            ],
                            1 => [
                                'name' => 'phone',
                                'type' => 'phone',
                                'label' => 'modules.accounts.fields.phone',
                            ],
                            2 => [
                                'name' => 'email',
                                'type' => 'email',
                                'label' => 'modules.accounts.fields.email',
                            ],
                            3 => [
                                'name' => 'website',
                                'type' => 'url',
                                'label' => 'modules.accounts.fields.website',
                            ],
                        ],
                    ],
                    1 => [
                        'name' => 'orders_invoices',
                        'type' => 'one-to-one',
                        'label' => 'relationships.orders_invoices',
                        'fields' => [
                            0 => [
                                'name' => 'order_number',
                                'type' => 'text',
                                'label' => 'modules.orders.fields.order_number',
                            ],
                            1 => [
                                'name' => 'total_amount',
                                'type' => 'number',
                                'label' => 'modules.orders.fields.total_amount',
                            ],
                            2 => [
                                'name' => 'status',
                                'type' => 'select',
                                'label' => 'modules.orders.fields.status',
                            ],
                            3 => [
                                'name' => 'order_date',
                                'type' => 'date',
                                'label' => 'modules.orders.fields.order_date',
                            ],
                            4 => [
                                'name' => 'due_date',
                                'type' => 'date',
                                'label' => 'modules.orders.fields.due_date',
                            ],
                        ],
                    ],
                ],
            ],
            1 => [
                'layout' => [
                    0 => [
                        'name' => 'contacts_invoices',
                        'type' => 'one-to-many',
                        'label' => 'relationships.contacts_invoices',
                        'fields' => [
                            0 => [
                                'name' => 'name',
                                'type' => 'text',
                                'label' => 'modules.contacts.fields.name',
                            ],
                            1 => [
                                'name' => 'position',
                                'type' => 'text',
                                'label' => 'modules.contacts.fields.position',
                            ],
                            2 => [
                                'name' => 'phone',
                                'type' => 'phone',
                                'label' => 'modules.contacts.fields.phone',
                            ],
                            3 => [
                                'name' => 'email',
                                'type' => 'email',
                                'label' => 'modules.contacts.fields.email',
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
                            4 => [
                                'name' => 'number',
                                'type' => 'number',
                                'label' => 'modules.quotes.fields.number',
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
                'name' => 'due_date',
                'type' => 'date',
                'label' => 'modules.invoices.fields.due_date',
            ],
            4 => [
                'name' => 'status',
                'type' => 'select',
                'label' => 'modules.invoices.fields.status',
            ],
        ],
    ],
];
