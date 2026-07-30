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
                'name' => 'direction',
                'type' => 'select',
                'label' => 'modules.calls.fields.direction',
            ],
            2 => [
                'name' => 'call_at',
                'type' => 'datetime',
                'label' => 'modules.calls.fields.call_at',
            ],
            3 => [
                'name' => 'status',
                'type' => 'select',
                'label' => 'modules.calls.fields.status',
            ],
            4 => [
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
                'name' => 'call_at',
                'type' => 'datetime',
                'label' => 'modules.calls.fields.call_at',
            ],
            2 => [
                'name' => 'status',
                'type' => 'select',
                'label' => 'modules.calls.fields.status',
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
                        'name' => 'direction',
                        'type' => 'select',
                        'label' => 'modules.calls.fields.direction',
                    ],
                    2 => [
                        'name' => 'status',
                        'type' => 'select',
                        'label' => 'modules.calls.fields.status',
                    ],
                    3 => [
                        'name' => 'outcome',
                        'type' => 'select',
                        'label' => 'modules.calls.fields.outcome',
                    ],
                    4 => [
                        'name' => 'description',
                        'type' => 'longtext',
                        'label' => 'modules.calls.fields.description',
                    ],
                    5 => [
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
                        'name' => 'call_at',
                        'type' => 'datetime',
                        'label' => 'modules.calls.fields.call_at',
                    ],
                    1 => [
                        'name' => 'duration_minutes',
                        'type' => 'number',
                        'label' => 'modules.calls.fields.duration_minutes',
                    ],
                    2 => [
                        'name' => 'created_at',
                        'type' => 'datetime',
                        'label' => 'modules.calls.fields.created_at',
                    ],
                    3 => [
                        'name' => 'updated_at',
                        'type' => 'datetime',
                        'label' => 'modules.calls.fields.updated_at',
                    ],
                    4 => [
                        'name' => 'created_by',
                        'type' => 'record',
                        'label' => 'modules.defaults.created_by',
                    ],
                    5 => [
                        'name' => 'updated_by',
                        'type' => 'record',
                        'label' => 'modules.defaults.updated_by',
                    ],
                ],
            ],
        ],
    ],
];
