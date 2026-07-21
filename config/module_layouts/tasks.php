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
                'name' => 'due_at',
                'type' => 'datetime',
                'label' => 'modules.tasks.fields.due_at',
            ],
            2 => [
                'name' => 'status',
                'type' => 'select',
                'label' => 'modules.tasks.fields.status',
            ],
            3 => [
                'name' => 'priority',
                'type' => 'select',
                'label' => 'modules.tasks.fields.priority',
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
                'name' => 'due_at',
                'type' => 'datetime',
                'label' => 'modules.tasks.fields.due_at',
            ],
            2 => [
                'name' => 'status',
                'type' => 'select',
                'label' => 'modules.tasks.fields.status',
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
                        'label' => 'modules.tasks.fields.status',
                    ],
                    2 => [
                        'name' => 'priority',
                        'type' => 'select',
                        'label' => 'modules.tasks.fields.priority',
                    ],
                    3 => [
                        'name' => 'description',
                        'type' => 'longtext',
                        'label' => 'modules.tasks.fields.description',
                    ],
                    4 => [
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
                        'name' => 'due_at',
                        'type' => 'datetime',
                        'label' => 'modules.tasks.fields.due_at',
                    ],
                    1 => [
                        'name' => 'completed_at',
                        'type' => 'datetime',
                        'label' => 'modules.tasks.fields.completed_at',
                    ],
                    2 => [
                        'name' => 'created_at',
                        'type' => 'datetime',
                        'label' => 'modules.tasks.fields.created_at',
                    ],
                    3 => [
                        'name' => 'updated_at',
                        'type' => 'datetime',
                        'label' => 'modules.tasks.fields.updated_at',
                    ],
                ],
            ],
        ],
    ],
];
