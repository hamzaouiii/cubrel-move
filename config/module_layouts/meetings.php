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
                'name' => 'start_at',
                'type' => 'datetime',
                'label' => 'modules.meetings.fields.start_at',
            ],
            2 => [
                'name' => 'end_at',
                'type' => 'datetime',
                'label' => 'modules.meetings.fields.end_at',
            ],
            3 => [
                'name' => 'duration',
                'type' => 'duration',
                'label' => 'modules.meetings.fields.duration',
            ],
            4 => [
                'name' => 'status',
                'type' => 'select',
                'label' => 'modules.meetings.fields.status',
            ],
            5 => [
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
                'name' => 'start_at',
                'type' => 'datetime',
                'label' => 'modules.meetings.fields.start_at',
            ],
            2 => [
                'name' => 'end_at',
                'type' => 'datetime',
                'label' => 'modules.meetings.fields.end_at',
            ],
            3 => [
                'name' => 'status',
                'type' => 'select',
                'label' => 'modules.meetings.fields.status',
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
                        'name' => 'status',
                        'type' => 'select',
                        'label' => 'modules.meetings.fields.status',
                    ],
                    2 => [
                        'name' => 'location',
                        'type' => 'address',
                        'label' => 'modules.meetings.fields.location',
                    ],
                    3 => [
                        'name' => 'description',
                        'type' => 'longtext',
                        'label' => 'modules.meetings.fields.description',
                    ],
                    4 => [
                        'name' => 'owner_id',
                        'type' => 'record',
                        'label' => 'modules.defaults.owner_id',
                    ],
                ],
            ],
            1 => [
                'name' => 'Attendees',
                'has_attendees' => true,
                'layout' => [],
            ],
            2 => [
                'name' => 'Timeline',
                'layout' => [
                    0 => [
                        'name' => 'start_at',
                        'type' => 'datetime',
                        'label' => 'modules.meetings.fields.start_at',
                    ],
                    1 => [
                        'name' => 'end_at',
                        'type' => 'datetime',
                        'label' => 'modules.meetings.fields.end_at',
                    ],
                    2 => [
                        'name' => 'duration',
                        'type' => 'duration',
                        'label' => 'modules.meetings.fields.duration',
                    ],
                    3 => [
                        'name' => 'created_at',
                        'type' => 'datetime',
                        'label' => 'modules.meetings.fields.created_at',
                    ],
                    4 => [
                        'name' => 'updated_at',
                        'type' => 'datetime',
                        'label' => 'modules.meetings.fields.updated_at',
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
