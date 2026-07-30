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
                'name' => 'from_address',
                'type' => 'text',
                'label' => 'modules.emails.fields.from_address',
            ],
            2 => [
                'name' => 'sent_at',
                'type' => 'datetime',
                'label' => 'modules.emails.fields.sent_at',
            ],
            3 => [
                'name' => 'owner_id',
                'type' => 'record',
                'label' => 'modules.defaults.owner_id',
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
                'name' => 'from_address',
                'type' => 'text',
                'label' => 'modules.emails.fields.from_address',
            ],
            2 => [
                'name' => 'sent_at',
                'type' => 'datetime',
                'label' => 'modules.emails.fields.sent_at',
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
                        'name' => 'from_name',
                        'type' => 'text',
                        'label' => 'modules.emails.fields.from_name',
                    ],
                    2 => [
                        'name' => 'from_address',
                        'type' => 'text',
                        'label' => 'modules.emails.fields.from_address',
                    ],
                    3 => [
                        'name' => 'to_addresses',
                        'type' => 'json',
                        'label' => 'modules.emails.fields.to_addresses',
                    ],
                    4 => [
                        'name' => 'cc_addresses',
                        'type' => 'json',
                        'label' => 'modules.emails.fields.cc_addresses',
                    ],
                    5 => [
                        'name' => 'body',
                        'type' => 'longtext',
                        'label' => 'modules.emails.fields.body',
                    ],
                    6 => [
                        'name' => 'owner_id',
                        'type' => 'record',
                        'label' => 'modules.defaults.owner_id',
                    ],
                ],
            ],
            1 => [
                'name' => 'Timeline',
                'layout' => [
                    0 => [
                        'name' => 'sent_at',
                        'type' => 'datetime',
                        'label' => 'modules.emails.fields.sent_at',
                    ],
                    1 => [
                        'name' => 'direction',
                        'type' => 'select',
                        'label' => 'modules.emails.fields.direction',
                    ],
                    2 => [
                        'name' => 'mailbox',
                        'type' => 'text',
                        'label' => 'modules.emails.fields.mailbox',
                    ],
                    3 => [
                        'name' => 'created_at',
                        'type' => 'datetime',
                        'label' => 'modules.emails.fields.created_at',
                    ],
                    4 => [
                        'name' => 'updated_at',
                        'type' => 'datetime',
                        'label' => 'modules.emails.fields.updated_at',
                    ],
                    5 => [
                        'name' => 'created_by',
                        'type' => 'record',
                        'label' => 'modules.defaults.created_by',
                    ],
                    6 => [
                        'name' => 'updated_by',
                        'type' => 'record',
                        'label' => 'modules.defaults.updated_by',
                    ],
                ],
            ],
        ],
    ],
];
