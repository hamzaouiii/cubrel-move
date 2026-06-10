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
                'label' => 'modules.leads.fields.email',
            ],
            2 => [
                'name' => 'phone',
                'type' => 'text',
                'label' => 'modules.leads.fields.phone',
            ],
            3 => [
                'name' => 'company',
                'type' => 'text',
                'label' => 'modules.leads.fields.company',
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
                        'name' => 'email',
                        'type' => 'email',
                        'label' => 'modules.leads.fields.email',
                    ],
                    2 => [
                        'name' => 'phone',
                        'type' => 'text',
                        'label' => 'modules.leads.fields.phone',
                    ],
                    3 => [
                        'name' => 'company',
                        'type' => 'text',
                        'label' => 'modules.leads.fields.company',
                    ],
                    4 => [
                        'name' => 'address',
                        'type' => 'address',
                        'label' => 'modules.leads.fields.address',
                    ],
                    5 => [
                        'name' => 'description',
                        'type' => 'longText',
                        'label' => 'modules.defaults.description',
                    ],
                    6 => [
                        'name' => 'owner_id',
                        'type' => 'record',
                        'label' => 'modules.defaults.owner_id',
                    ],
                    7 => [
                        'name' => 'created_at',
                        'type' => 'datetime',
                        'label' => 'modules.defaults.created_at',
                    ],
                    8 => [
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
                        'name' => 'contacts_leads',
                        'type' => 'one-to-one',
                        'label' => 'relationships.contacts_leads',
                        'fields' => [
                            0 => [
                                'name' => 'name',
                                'type' => 'text',
                                'label' => 'modules.contacts.fields.name',
                            ],
                            1 => [
                                'name' => 'email',
                                'type' => 'email',
                                'label' => 'modules.contacts.fields.email',
                            ],
                            2 => [
                                'name' => 'phone',
                                'type' => 'phone',
                                'label' => 'modules.contacts.fields.phone',
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
                'name' => 'email',
                'type' => 'email',
                'label' => 'modules.leads.fields.email',
            ],
            2 => [
                'name' => 'company',
                'type' => 'text',
                'label' => 'modules.leads.fields.company',
            ],
            3 => [
                'name' => 'phone',
                'type' => 'text',
                'label' => 'modules.leads.fields.phone',
            ],
        ],
    ],
];
