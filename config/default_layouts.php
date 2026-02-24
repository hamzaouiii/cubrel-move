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
        "name" => "description",
        "type" => "longText",
        "label" =>  "modules.defaults.description",
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
            'type' => 'textfield',
            'label' => 'modules.defaults.name',
            'required' => true,
            'readonly' => false,
            'sortable' => true
          ],
          [
            'name' => 'description',
            'type' => 'longText',
            'label' => 'modules.defaults.description',
            'required' => true,
            'readonly' => false,
            'sortable' => true
          ],
          [
            'name' => 'created_at',
            'type' => 'datetime',
            'label' => 'modules.defaults.created_at',
            'readonly' => true,
            'required' => true,
            'sortable' => true
          ],
          [
            'name' => 'updated_at',
            'type' => 'datetime',
            'label' => 'modules.defaults.updated_at',
            'readonly' => true,
            'required' => true,
            'sortable' => true
          ]
        ]
      ]
    ]
  ],
  'related' => [
    'columns' => [
      0 => [],
      1 => [],
    ]
  ],
  'linking-panel' => [
    "columns" => [
      [
        "name" => "name",
        "type" => "textfield",
        "label" =>  "modules.defaults.name",
        "sortable" => true
      ],
      [
        "name" => "description",
        "type" => "longText",
        "label" =>  "modules.defaults.description",
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
];
