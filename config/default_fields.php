<?php

return [
  'name' =>
  [
    'name' => 'name',
    'searchable' => true,
    'type' => 'text',
    'required' => true,
    'label'   => 'modules.defaults.name',
    'key'     => 'default.name',
    'is_default' => true
  ],
  'description' =>
  [
    'name' => 'description',
    'searchable' => false,
    'type' => 'longtext',
    'key'     => 'default.description',
    'label'   => 'modules.defaults.description',
    'is_default' => true
  ],
  'owner_id' =>
  [
    'name' => 'owner_id',
    'searchable' => false,
    'required' => true,
    'related_module' => 'users',
    'type' => 'user',
    'key'     => 'default.owner_id',
    'label'   => 'modules.defaults.owner_id',
    'is_default' => true
  ],
  'created_at' =>
  [
    'name' => 'created_at',
    'type' => 'datetime',
    'readonly' => true,
    'key'     => 'default.created_at',
    'label'   => 'modules.defaults.created_at',
    'is_default' => true
  ],
  'updated_at' =>
  [
    'name' => 'updated_at',
    'type' => 'datetime',
    'key'     => 'default.updated_at',
    'readonly' => true,
    'label'   => 'modules.defaults.updated_at',
    'is_default' => true
  ],
];
