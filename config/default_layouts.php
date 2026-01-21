<?php
return [
  'list' => [
    "actions" =>
    [],
    "columns" => [
      [
        "name" => "name",
        "type" => "textField",
        "label" =>  "modules.defaults.name",
        "sortable" => true
      ],
      [
        "name" => "created_at",
        "type" => "dateTime",
        "label" => "modules.defaults.created_at",
        "sortable" => true
      ],
      [
        "name" => "updated_at",
        "type" => "dateTime",
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
            'type' => 'textField',
            'label' => 'modules.defaults.name',
            'required' => true,
            'readonly' => false,
            'sortable' => true
          ],
          [
            'name' => 'created_at',
            'type' => 'dateTime',
            'label' => 'modules.defaults.created_at',
            'readonly' => true,
            'required' => true,
            'sortable' => true
          ],
          [
            'name' => 'updated_at',
            'type' => 'dateTime',
            'label' => 'modules.defaults.updated_at',
            'readonly' => true,
            'required' => true,
            'sortable' => true
          ]
        ]
      ]
    ]
  ]
];
