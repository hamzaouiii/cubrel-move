<?php

return [
    'list' => [
        'columns' => [
            0 => [
                'name' => 'name',
                'type' => 'text',
                'label' => 'modules.defaults.name',
            ],
            2 => [
                'name' => 'position',
                'type' => 'text',
                'label' => 'modules.contacts.fields.position',
                'dropdown_list_id' => null,
            ],
            3 => [
                'name' => 'phone',
                'type' => 'text',
                'label' => 'modules.contacts.fields.phone',
                'dropdown_list_id' => null,
            ],
            4 => [
                'name' => 'email',
                'type' => 'email',
                'label' => 'modules.contacts.fields.email',
                'dropdown_list_id' => null,
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
                        'name' => 'position',
                        'type' => 'text',
                        'label' => 'modules.contacts.fields.position',
                    ],
                    2 => [
                        'name' => 'phone',
                        'type' => 'text',
                        'label' => 'modules.contacts.fields.phone',
                    ],
                    3 => [
                        'name' => 'email',
                        'type' => 'email',
                        'label' => 'modules.contacts.fields.email',
                    ],
                    4 => [
                        'name' => 'notes',
                        'type' => 'longText',
                        'label' => 'modules.contacts.fields.notes',
                    ],
                    5 => [
                        'name' => 'owner_id',
                        'type' => 'record',
                        'label' => 'modules.defaults.owner_id',
                    ],
                    6 => [
                        'name' => 'description',
                        'type' => 'longText',
                        'label' => 'modules.defaults.description',
                    ],
                ],
            ],
            1 => [
                'name' => 'System',
                'layout' => [
                    0 => [
                        'name' => 'created_at',
                        'type' => 'datetime',
                        'label' => 'modules.defaults.created_at',
                    ],
                    1 => [
                        'name' => 'updated_at',
                        'type' => 'datetime',
                        'label' => 'modules.defaults.updated_at',
                    ],
                    2 => [
                        'name' => 'created_by',
                        'type' => 'record',
                        'label' => 'modules.defaults.created_by',
                    ],
                    3 => [
                        'name' => 'updated_by',
                        'type' => 'record',
                        'label' => 'modules.defaults.updated_by',
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
                                'label' => 'modules.accounts.fields.name',
                            ],
                            1 => [
                                'name' => 'phone',
                                'type' => 'phone',
                                'label' => 'modules.accounts.fields.phone',
                            ],
                            2 => [
                                'name' => 'description',
                                'type' => 'longtext',
                                'label' => 'modules.accounts.fields.description',
                            ],
                            3 => [
                                'name' => 'website',
                                'type' => 'url',
                                'label' => 'modules.accounts.fields.website',
                            ],
                        ],
                    ],
                    1 => [
                        'name' => 'contacts_cases',
                        'type' => 'one-to-many',
                        'label' => 'relationships.contacts_cases',
                        'fields' => [
                            0 => [
                                'name' => 'name',
                                'type' => 'text',
                                'label' => 'modules.cases.fields.name',
                            ],
                            1 => [
                                'name' => 'status',
                                'type' => 'select',
                                'label' => 'modules.cases.fields.status',
                            ],
                            2 => [
                                'name' => 'priority',
                                'type' => 'select',
                                'label' => 'modules.cases.fields.priority',
                            ],
                            3 => [
                                'name' => 'opened_at',
                                'type' => 'datetime',
                                'label' => 'modules.cases.fields.opened_at',
                            ],
                            4 => [
                                'name' => 'closed_at',
                                'type' => 'datetime',
                                'label' => 'modules.cases.fields.closed_at',
                            ],
                        ],
                    ],
                ],
            ],
            1 => [
                'layout' => [
                    0 => [
                        'name' => 'contacts_leads',
                        'type' => 'one-to-one',
                        'label' => 'relationships.contacts_leads',
                        'fields' => [
                            0 => [
                                'name' => 'name',
                                'type' => 'text',
                                'label' => 'modules.leads.fields.name',
                            ],
                            1 => [
                                'name' => 'email',
                                'type' => 'email',
                                'label' => 'modules.leads.fields.email',
                            ],
                            2 => [
                                'name' => 'phone',
                                'type' => 'phone',
                                'label' => 'modules.leads.fields.phone',
                            ],
                        ],
                    ],
                    1 => [
                        'name' => 'deals_contacts',
                        'type' => 'many-to-many',
                        'label' => 'relationships.deals_contacts',
                        'fields' => [
                            0 => [
                                'name' => 'name',
                                'type' => 'text',
                                'label' => 'modules.deals.fields.name',
                            ],
                            1 => [
                                'name' => 'type',
                                'type' => 'select',
                                'label' => 'modules.deals.fields.type',
                            ],
                            2 => [
                                'name' => 'sales_stage',
                                'type' => 'select',
                                'label' => 'modules.deals.fields.sales_stage',
                            ],
                            3 => [
                                'name' => 'expected_close_date',
                                'type' => 'date',
                                'label' => 'modules.deals.fields.expected_close_date',
                            ],
                        ],
                    ],
                    2 => [
                        'name' => 'contacts_invoices',
                        'type' => 'one-to-many',
                        'label' => 'relationships.contacts_invoices',
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
                                'name' => 'due_date',
                                'type' => 'date',
                                'label' => 'modules.invoices.fields.due_date',
                            ],
                            4 => [
                                'name' => 'status',
                                'type' => 'select',
                                'label' => 'modules.invoices.fields.status',
                            ],
                            5 => [
                                'name' => 'issue_date',
                                'type' => 'date',
                                'label' => 'modules.invoices.fields.issue_date',
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
            2 => [
                'id' => '2e94cba5-b0dd-4959-b4a9-3a3863d4c915',
                'name' => 'phone',
                'type' => 'text',
                'label' => 'modules.contacts.fields.phone',
                'module_id' => '019c9a01-7d25-7136-a50d-5fa6fdc46656',
                'dropdown_list_id' => null,
            ],
            3 => [
                'id' => '36a49d40-b37f-4340-9d78-397ee7d9a64b',
                'name' => 'email',
                'type' => 'email',
                'label' => 'modules.contacts.fields.email',
                'module_id' => '019c9a01-7d25-7136-a50d-5fa6fdc46656',
                'dropdown_list_id' => null,
            ],
            4 => [
                'id' => 'ab103add-6343-4bb5-904c-04ec3447b202',
                'name' => 'position',
                'type' => 'text',
                'label' => 'modules.contacts.fields.position',
                'module_id' => '019c9a01-7d25-7136-a50d-5fa6fdc46656',
                'dropdown_list_id' => null,
            ],
        ],
    ],
];
