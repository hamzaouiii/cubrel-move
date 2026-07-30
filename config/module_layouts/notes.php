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
                'name' => 'description',
                'type' => 'longtext',
                'label' => 'modules.notes.fields.description',
            ],
            2 => [
                'name' => 'owner_id',
                'type' => 'record',
                'label' => 'modules.defaults.owner_id',
            ],
            3 => [
                'name' => 'created_at',
                'type' => 'datetime',
                'label' => 'modules.notes.fields.created_at',
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
    'linkingPanel' => [
        'columns' => [
            0 => [
                'name' => 'name',
                'type' => 'text',
                'label' => 'modules.defaults.name',
            ],
            1 => [
                'name' => 'created_at',
                'type' => 'datetime',
                'label' => 'modules.notes.fields.created_at',
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
                        'name' => 'description',
                        'type' => 'longtext',
                        'label' => 'modules.notes.fields.description',
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
];
