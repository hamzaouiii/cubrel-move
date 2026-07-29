<?php

return     [


  'required' => 'The :attribute field is required.',
  'string' => 'The :attribute must be a string.',
  'max' => [
    'string' => 'The :attribute may not be greater than :max characters.',
  ],
  'unique' => 'The :attribute has already been taken.',
  'exists' => 'The selected :attribute is invalid.',
  'in' => 'The selected :attribute is invalid.',
  'not_in' => 'The value for :attribute is a reserved keyword cannot be used',
  'required_if' => 'The :attribute field is required.',

  'attributes' => [
    'name' => 'Name',
    'label' => 'Label',
    'right_module' => 'Module',
    'type' => 'Relationship type',
    'key' => 'System Name',
    'dropdown' => 'Dropdown List',
    'dropdown_list' => 'Dropdown List',
    'slug' => 'System Key',
    'source_module' => 'Source module',
    'target_module' => 'Target module',
    'conditions.*.field' => 'Condition field',
    'conditions.*.operator' => 'Condition operator',
    'conditions.*.value' => 'Condition value',
    'field_mappings.*.target_field' => 'Target field',
    'field_mappings.*.mode' => 'Mapping mode',
    'field_mappings.*.source_field' => 'Source field',
    'field_mappings.*.value' => 'Mapping value',
    'field_mappings.*.expression.*.type' => 'Expression part type',
    'field_mappings.*.expression.*.value' => 'Expression part value',
    'relationships.*' => 'Relationship',

  ],
];
