<?php
return [
  'list' => [
    "actions" =>
    [],
    "columns" => [
      [
        "name" => "name",
        "type" => "text",
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
        "name" => "owner_id",
        "type" => "user",
        "label" =>  "modules.defaults.owner_id",
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
      [
        "name" => "created_by",
        "type" => "record",
        "label" => "modules.defaults.created_by",
        "sortable" => true
      ],
      [
        "name" => "updated_by",
        "type" => "record",
        "label" => "modules.defaults.updated_by",
        "sortable" => true
      ],
    ],
    "defaultSort" => null,
  ],
  'record' => [
    'sections' => [
      [
        'name' => 'Card',
        'layout' => [
          [
            'name' => 'name',
            'type' => 'text',
            'label' => 'modules.defaults.name',
            'required' => true,
            'readonly' => false,
            'sortable' => true
          ],
          [
            "name" => "owner_id",
            "type" => "user",
            "label" =>  "modules.defaults.owner_id",
            "sortable" => true
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
          ],
          [
            'name' => 'created_by',
            'type' => 'record',
            'label' => 'modules.defaults.created_by',
            'readonly' => true,
            'sortable' => true
          ],
          [
            'name' => 'updated_by',
            'type' => 'record',
            'label' => 'modules.defaults.updated_by',
            'readonly' => true,
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
  'linkingPanel' => [
    "columns" => [
      [
        "name" => "name",
        "type" => "text",
        "label" =>  "modules.defaults.name",
        "sortable" => true
      ],
      [
        "name" => "owner_id",
        "type" => "user",
        "label" =>  "modules.defaults.owner_id",
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
    ],
    "defaultSort" => null,
  ],
  // Governs the create/edit line-item sheet: which of the shared line_items
  // module's fields appear (in order), and which field on the configured
  // line_item_source_module (falls back to 'products') autofills each one.
  'lineItemsSnapshot' => [
    'fields' => [
      ['name' => 'name', 'source_field' => 'name'],
      ['name' => 'quantity', 'source_field' => null],
      ['name' => 'unit', 'source_field' => 'unit'],
      ['name' => 'unit_price', 'source_field' => 'price'],
      ['name' => 'discount', 'source_field' => null],
      ['name' => 'tax_rate', 'source_field' => 'tax_rate'],
      ['name' => 'note', 'source_field' => null],
    ],
  ],
];
