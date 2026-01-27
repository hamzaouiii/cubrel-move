<?php

return [
  'label' => 'Fields',
  'name'   => 'System Name',
  'field_label' => 'Display Label',
  'type'  => 'Type',
  'create_new_field' => 'Create new field',
  'back_to_list' => 'Back to fields',
  'field_create_success' => 'Field created successfully.',
  'field_update_success' => 'Field updated successfully.',
  'field_create_error' => 'An error occurred while creating field.',
  'field_update_error' => 'An error occurred while updating field.',
  'field_reset_success' => 'Field reset to Database values.',
  'key_is_taken_error'    => 'A field with the same system name already exists',
  'types' => [
    'longtext' => 'Long text',
    'textfield' => 'Text',
    'datetime' => 'Date & time',
    'email' => 'Email',
    'dropdown' => 'Dropdown',
    'date' => 'Date',
    'number' => 'Number',
    'relationship' => 'Relationship'

  ],
  'metadata' => [
    'name' => 'System Name',
    // 'key' => 'Key',
    'type' => 'Type',
    'label' => 'Display Label',
    'readonly' => 'Readonly',
    'hidden' => 'Hidden',
    'nullable' => 'Nullable',
    'required' => 'Required',
    'searchable' => 'Searchable',
    'filterable' => 'Filterable',
    'sortable' => 'Sortable',
    'default_value' => 'Default Value',
    'options' => 'Options',
    'min_length' => 'Minimun Length',
    'max_length' => 'Maximun Length',
    'regex' => 'Regex Rules',
  ]
];
