<?php
return [
  'list' => [
    "actions" =>
    [],
    "columns" => [
      [
        "name" => "name",
        "type" => "textfield",
        "label" =>  "modules.defaults.name",
        "sortable" => true
      ],
      [
        "name" => "created_at",
        "type" => "datetime",
        "label" => "modules.defaults.created_at",
        "sortable" => true
      ],
      [
        "name" => "updated_at",
        "type" => "datetime",
        "label" => "modules.defaults.updated_at",
        "sortable" => true
      ],
      "defaultSort" =>
      null
    ]
  ],

  'record' => [
    'sections' => [
      [
        'name' => 'Card',
        'layout' => [
          [
            'name' => 'name',
            'type' => 'string',
            'label' => 'modules.defaults.name',
            'sortable' => true
          ],
          [
            'name' => 'created_at',
            'type' => 'datetime',
            'label' => 'modules.defaults.created_at',
            'readonly' => true,
            'sortable' => true
          ],
          [
            'name' => 'updated_at',
            'type' => 'datetime',
            'label' => 'modules.defaults.updated_at',
            'readonly' => true,
            'sortable' => true
          ]
        ]
      ]
    ]
  ]
];
