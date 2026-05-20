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
                'name' => 'email',
                'type' => 'email',
                'label' => 'modules.inquiries.fields.email',
            ],
            2 => [
                'name' => 'status',
                'type' => 'select',
                'label' => 'modules.inquiries.fields.status',
            ],
            3 => [
                'name' => 'phone',
                'type' => 'text',
                'label' => 'modules.inquiries.fields.phone',
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
                        'name' => 'email',
                        'type' => 'email',
                        'label' => 'modules.inquiries.fields.email',
                    ],
                    2 => [
                        'name' => 'phone',
                        'type' => 'text',
                        'label' => 'modules.inquiries.fields.phone',
                    ],
                    3 => [
                        'name' => 'status',
                        'type' => 'select',
                        'label' => 'modules.inquiries.fields.status',
                    ],
                    4 => [
                        'name' => 'message',
                        'type' => 'longtext',
                        'label' => 'modules.inquiries.fields.message',
                    ],
                    5 => [
                        'name' => 'ip',
                        'type' => 'text',
                        'label' => 'modules.inquiries.fields.ip',
                    ],
                    6 => [
                        'name' => 'user_agent',
                        'type' => 'longtext',
                        'label' => 'modules.inquiries.fields.user_agent',
                    ],
                    7 => [
                        'name' => 'description',
                        'type' => 'longText',
                        'label' => 'modules.defaults.description',
                    ],
                    8 => [
                        'name' => 'created_at',
                        'type' => 'datetime',
                        'label' => 'modules.defaults.created_at',
                    ],
                    9 => [
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
                        'name' => 'accounts_inquiries',
                        'type' => 'one-to-many',
                        'label' => 'relationships.accounts_inquiries',
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
                                'name' => 'website',
                                'type' => 'url',
                                'label' => 'modules.accounts.fields.website',
                            ],
                            3 => [
                                'name' => 'email',
                                'type' => 'email',
                                'label' => 'modules.accounts.fields.email',
                            ],
                        ],
                    ],
                ],
            ],
            1 => [
                'layout' => [
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
                'name' => 'status',
                'type' => 'select',
                'label' => 'modules.inquiries.fields.status',
            ],
            2 => [
                'name' => 'created_at',
                'type' => 'datetime',
                'label' => 'modules.defaults.created_at',
            ],
            3 => [
                'name' => 'updated_at',
                'type' => 'datetime',
                'label' => 'modules.defaults.updated_at',
            ],
        ],
    ],
];
