<?php

return     [

  'required' => 'Das Feld :attribute ist erforderlich.',
  'string' => 'Das Feld :attribute muss eine Zeichenkette sein.',
  'max' => [
    'string' => 'Das Feld :attribute darf maximal :max Zeichen enthalten.',
  ],
  'unique' => 'Der Wert für :attribute existiert bereits.',
  'exists' => 'Der ausgewählte Wert für :attribute ist ungültig.',
  'in' => 'Der ausgewählte Wert für :attribute ist ungültig.',
  'not_in' => 'Der Wert für :attribute ist reserviert und kann nicht verwendet werden.',
  'required_if' => 'Das Feld :attribute ist erforderlich.',
  'attributes' => [
    'name' => 'Name',
    'key' => 'Name',
    'label' => 'Bezeichnung',
    'right_module' => 'Modul',
    'type' => 'Beziehungstyp',
    'slug' => 'Systembezeichnung',
    'dropdown' => 'Dropdown Liste',
    'dropdown_list' => 'Dropdown Liste',
    'source_module' => 'Quellmodul',
    'target_module' => 'Zielmodul',
    'conditions.*.field' => 'Bedingungsfeld',
    'conditions.*.operator' => 'Bedingungsoperator',
    'conditions.*.value' => 'Bedingungswert',
    'field_mappings.*.target_field' => 'Zielfeld',
    'field_mappings.*.mode' => 'Zuordnungsmodus',
    'field_mappings.*.source_field' => 'Quellfeld',
    'field_mappings.*.value' => 'Zuordnungswert',
    'field_mappings.*.expression.*.type' => 'Ausdruckstyp',
    'field_mappings.*.expression.*.value' => 'Ausdruckswert',
    'relationships.*' => 'Beziehung',
  ],
];
