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
  'label_hint'        => 'Min. 4 characters',
  'regex_hint'        => 'Use for custom validation rules',

  'types' => [
    'longtext' => 'Long text',
    'text' => 'Text',
    'datetime' => 'Date & time',
    'email' => 'Email',
    'select' => 'Dropdown Field ',
    'date' => 'Date',
    'number' => 'Number',
    'relationship' => 'Relationship',
    'checkbox' => 'Checkbox',
    'url' => 'URL',
    'phone' => 'Phone'
  ],
  'metadata' => [
    'name' => 'System Name',
    // 'key' => 'Key',
    'type' => 'Type',
    'label' => 'Display Label',
    'readonly' => 'Readonly',
    'hidden' => 'Hidden',
    'required' => 'Required',
    'searchable' => 'Searchable',
    'filterable' => 'Filterable',
    'sortable' => 'Sortable',
    'default_value' => 'Default Value',
    'options' => 'Options',
    'min_length' => 'Minimun Length',
    'max_length' => 'Maximun Length',
    'regex' => 'Regex Rules',
  ],
  'validation' => [
    'is_required' => "field is required!",
    'is_required_several' => "Multiple required fields are still empty!"
  ],
  'checkbox_yes' => "Yes",
  'checkbox_no' => "No",
  'calendar' =>
  [
    'months' => [
      'january' => 'January',
      'february' => 'February',
      'march' => 'March',
      'april' => 'April',
      'may' => 'May',
      'june' => 'June',
      'july' => 'July',
      'august' => 'August',
      'september' => 'September',
      'october' => 'October',
      'november' => 'November',
      'december' => 'December'
    ],

    'weekdays_short' => [
      'sunday' => 'Su',
      'monday' => 'Mo',
      'tuesday' => 'Tu',
      'wednesday' => 'We',
      'thursday' => 'Th',
      'friday' => 'Fr',
      'saturday' => 'Sa'
    ],

    'today' => 'Today',
    'clear' => 'Clear',
    'select_date' => 'Select Date',
    'time_format' => 'HH:MM'
  ]

];
