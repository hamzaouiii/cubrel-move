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
            6 => [
                'name' => 'owner_id',
                'type' => 'record',
                'label' => 'modules.defaults.owner_id',
            ],
        ],
    ],
    'related' => [
        'columns' => [
            0 => [
                'layout' => [
                    0 => [
                        'name' => 'accounts_cases',
                        'type' => 'one-to-many',
                        'label' => 'relationships.accounts_cases',
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
                                'name' => 'phone',
                                'type' => 'phone',
                                'label' => 'modules.accounts.fields.phone',
                            ],
                            3 => [
                                'name' => 'website',
                                'type' => 'url',
                                'label' => 'modules.accounts.fields.website',
                            ],
                        ],
                    ],
                ],
            ],
            1 => [
                'layout' => [
                    0 => [
                        'name' => 'contacts_cases',
                        'type' => 'one-to-many',
                        'label' => 'relationships.contacts_cases',
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
                'name' => 'opened_at',
                'type' => 'datetime',
                'label' => 'modules.cases.fields.opened_at',
            ],
            2 => [
                'name' => 'closed_at',
                'type' => 'datetime',
                'label' => 'modules.cases.fields.closed_at',
            ],
            3 => [
                'name' => 'status',
                'type' => 'select',
                'label' => 'modules.cases.fields.status',
            ],
            4 => [
                'name' => 'priority',
                'type' => 'select',
                'label' => 'modules.cases.fields.priority',
            ],
        ],
    ],
    'record' => [
        'sections' => [
            0 => [
                'name' => 'Details',
                'layout' => [
                    0 => [
                        'name' => 'name',
                        'type' => 'text',
                        'label' => 'modules.defaults.name',
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
                        'name' => 'description',
                        'type' => 'longtext',
                        'label' => 'modules.cases.fields.description',
                    ],
                    5 => [
                        'name' => 'owner_id',
                        'type' => 'record',
                        'label' => 'modules.defaults.owner_id',
                    ],
                ],
            ],
            1 => [
                'name' => 'Dates',
                'layout' => [
                    0 => [
                        'name' => 'opened_at',
                        'type' => 'datetime',
                        'label' => 'modules.cases.fields.opened_at',
                    ],
                    1 => [
                        'name' => 'closed_at',
                        'type' => 'datetime',
                        'label' => 'modules.cases.fields.closed_at',
                    ],
                    2 => [
                        'name' => 'created_at',
                        'type' => 'datetime',
                        'label' => 'modules.cases.fields.created_at',
                    ],
                    3 => [
                        'name' => 'updated_at',
                        'type' => 'datetime',
                        'label' => 'modules.cases.fields.updated_at',
                    ],
                ],
            ],
        ],
    ],
];
