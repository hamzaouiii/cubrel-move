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
                'name' => 'phone',
                'type' => 'text',
                'label' => 'modules.accounts.fields.phone',
                'dropdown_list_id' => null,
            ],
            2 => [
                'name' => 'email',
                'type' => 'email',
                'label' => 'modules.accounts.fields.email',
                'dropdown_list_id' => null,
            ],
            3 => [
                'name' => 'owner_id',
                'type' => 'record',
                'label' => 'modules.defaults.owner_id',
            ],
            4 => [
                'name' => 'created_at',
                'type' => 'datetime',
                'label' => 'modules.defaults.created_at',
            ],
            5 => [
                'name' => 'updated_at',
                'type' => 'datetime',
                'label' => 'modules.defaults.updated_at',
            ],
            6 => [
                'name' => 'created_by',
                'type' => 'record',
                'label' => 'modules.defaults.created_by',
            ],
            7 => [
                'name' => 'updated_by',
                'type' => 'record',
                'label' => 'modules.defaults.updated_by',
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
                        'name' => 'website',
                        'type' => 'text',
                        'label' => 'modules.accounts.fields.website',
                    ],
                    2 => [
                        'name' => 'email',
                        'type' => 'email',
                        'label' => 'modules.accounts.fields.email',
                    ],
                    3 => [
                        'name' => 'phone',
                        'type' => 'text',
                        'label' => 'modules.accounts.fields.phone',
                    ],
                    4 => [
                        'name' => 'description',
                        'type' => 'longText',
                        'label' => 'modules.defaults.description',
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
                    8 => [
                        'name' => 'created_by',
                        'type' => 'record',
                        'label' => 'modules.defaults.created_by',
                    ],
                    9 => [
                        'name' => 'updated_by',
                        'type' => 'record',
                        'label' => 'modules.defaults.updated_by',
                    ],
                ],
            ],
            1 => [
                'name' => 'Address',
                'layout' => [
                    0 => [
                        'name' => 'billing_address',
                        'type' => 'address',
                        'label' => 'modules.accounts.fields.billing_address',
                    ],
                    1 => [
                        'name' => 'shipping_address',
                        'type' => 'address',
                        'label' => 'modules.accounts.fields.shipping_address',
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
                        'name' => 'accounts_contacts',
                        'type' => 'one-to-many',
                        'label' => 'relationships.accounts_contacts',
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
                            4 => [
                                'name' => 'created_at',
                                'type' => 'datetime',
                                'label' => 'modules.contacts.fields.created_at',
                            ],
                        ],
                    ],
                    1 => [
                        'name' => 'accounts_invoices',
                        'type' => 'one-to-many',
                        'label' => 'relationships.accounts_invoices',
                        'fields' => [
                            0 => [
                                'name' => 'name',
                                'type' => 'text',
                                'label' => 'modules.invoices.fields.name',
                            ],
                            1 => [
                                'name' => 'status',
                                'type' => 'select',
                                'label' => 'modules.invoices.fields.status',
                            ],
                            2 => [
                                'name' => 'number',
                                'type' => 'number',
                                'label' => 'modules.invoices.fields.number',
                            ],
                            3 => [
                                'name' => 'subtotal',
                                'type' => 'number',
                                'label' => 'modules.invoices.fields.subtotal',
                            ],
                            4 => [
                                'name' => 'tax',
                                'type' => 'number',
                                'label' => 'modules.invoices.fields.tax',
                            ],
                            5 => [
                                'name' => 'total',
                                'type' => 'number',
                                'label' => 'modules.invoices.fields.total',
                            ],
                            6 => [
                                'name' => 'issue_date',
                                'type' => 'date',
                                'label' => 'modules.invoices.fields.issue_date',
                            ],
                            7 => [
                                'name' => 'due_date',
                                'type' => 'date',
                                'label' => 'modules.invoices.fields.due_date',
                            ],
                        ],
                    ],
                    2 => [
                        'name' => 'accounts_cases',
                        'type' => 'one-to-many',
                        'label' => 'relationships.accounts_cases',
                        'fields' => [
                            0 => [
                                'name' => 'name',
                                'type' => 'text',
                                'label' => 'modules.cases.fields.name',
                            ],
                            1 => [
                                'name' => 'subject',
                                'type' => 'text',
                                'label' => 'modules.cases.fields.subject',
                            ],
                            2 => [
                                'name' => 'status',
                                'type' => 'select',
                                'label' => 'modules.cases.fields.status',
                            ],
                            3 => [
                                'name' => 'priority',
                                'type' => 'select',
                                'label' => 'modules.cases.fields.priority',
                            ],
                            4 => [
                                'name' => 'opened_at',
                                'type' => 'datetime',
                                'label' => 'modules.cases.fields.opened_at',
                            ],
                            5 => [
                                'name' => 'closed_at',
                                'type' => 'datetime',
                                'label' => 'modules.cases.fields.closed_at',
                            ],
                        ],
                    ]
                ],
            ],
            1 => [
                'layout' => [
                    0 => [
                        'name' => 'accounts_deals',
                        'type' => 'one-to-many',
                        'label' => 'relationships.accounts_deals',
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
                                'name' => 'amount',
                                'type' => 'currency',
                                'label' => 'modules.deals.fields.amount',
                            ],
                            3 => [
                                'name' => 'probability',
                                'type' => 'percentage',
                                'label' => 'modules.deals.fields.probability',
                            ],
                            4 => [
                                'name' => 'type',
                                'type' => 'select',
                                'label' => 'modules.deals.fields.type',
                            ],
                            5 => [
                                'name' => 'expected_close_date',
                                'type' => 'date',
                                'label' => 'modules.deals.fields.expected_close_date',
                            ],
                        ],
                    ],
                    1 => [
                        'name' => 'accounts_quotes',
                        'type' => 'one-to-many',
                        'label' => 'relationships.accounts_quotes',
                        'fields' => [
                            0 => [
                                'name' => 'name',
                                'type' => 'text',
                                'label' => 'modules.quotes.fields.name',
                            ],
                            1 => [
                                'name' => 'created_at',
                                'type' => 'datetime',
                                'label' => 'modules.quotes.fields.created_at',
                            ],
                            2 => [
                                'name' => 'updated_at',
                                'type' => 'datetime',
                                'label' => 'modules.quotes.fields.updated_at',
                            ],
                        ],
                    ],
                    2 => [
                        'name' => 'accounts_orders',
                        'type' => 'one-to-many',
                        'label' => 'relationships.accounts_orders',
                        'fields' => [
                            0 => [
                                'name' => 'order_number',
                                'type' => 'text',
                                'label' => 'modules.orders.fields.order_number',
                            ],
                            1 => [
                                'name' => 'order_date',
                                'type' => 'date',
                                'label' => 'modules.orders.fields.order_date',
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
                            5 => [
                                'name' => 'created_at',
                                'type' => 'datetime',
                                'label' => 'modules.orders.fields.created_at',
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
                'name' => 'website',
                'type' => 'text',
                'label' => 'modules.accounts.fields.website',
                'dropdown_list_id' => null,
            ],
            2 => [
                'name' => 'email',
                'type' => 'email',
                'label' => 'modules.accounts.fields.email',
                'dropdown_list_id' => null,
            ],
            3 => [
                'name' => 'phone',
                'type' => 'text',
                'label' => 'modules.accounts.fields.phone',
                'dropdown_list_id' => null,
            ],
        ],
    ],
];
