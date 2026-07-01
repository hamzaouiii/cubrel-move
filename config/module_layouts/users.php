<?php

return array(

  'list' =>
  array(
    'columns' =>
    array(
      0 =>
      array(
        'name' => 'avatar',
        'type' => 'image',
        'label' => 'modules.users.fields.avatar',
      ),
      1 =>
      array(
        'name' => 'name',
        'type' => 'text',
        'label' => 'modules.defaults.name',
      ),
      2 =>
      array(
        'name' => 'username',
        'type' => 'text',
        'label' => 'modules.users.fields.username',
      ),
      3 =>
      array(
        'name' => 'is_admin',
        'type' => 'checkbox',
        'label' => 'modules.users.fields.is_admin',
      ),
      4 =>  array(
            'name' => 'type',
            'type' => 'select',
            'label' => 'modules.users.fields.type',
          ),
          5=>
      array(
        'name' => 'status',
        'type' => 'select',
        'label' => 'modules.users.fields.status',
      ),
      6 =>
      array(
        'name' => 'email',
        'type' => 'email',
        'label' => 'modules.users.fields.email',
      ),
      7 =>
      array(
        'name' => 'created_at',
        'type' => 'datetime',
        'label' => 'modules.defaults.created_at',
      ),
    ),
  ),
  'record' =>
  array(
    'sections' =>
    array(
      0 =>
      array(
        'name' => 'Profile',
        'layout' =>
        array(
          0 =>
          array(
            'name' => 'username',
            'type' => 'text',
            'label' => 'modules.users.fields.username',
            'readonly' => false,
            'required' => true,
          ),
          1 =>
          array(
            'name' => 'title',
            'type' => 'text',
            'label' => 'modules.users.fields.title',
            'readonly' => false,
            'required' => false,
            'searchable' => true,
          ),
          2 =>
          array(
            'name' => 'first_name',
            'type' => 'text',
            'label' => 'modules.users.fields.first_name',
            'readonly' => false,
            'required' => false,
          ),
          3 =>
          array(
            'name' => 'last_name',
            'type' => 'text',
            'label' => 'modules.users.fields.last_name',
            'readonly' => false,
            'required' => false,
          ),
          4 =>
          array(
            'name' => 'phone',
            'type' => 'phone',
            'label' => 'modules.users.fields.phone',
            'readonly' => false,
            'required' => false,
          ),
          5 =>
          array(
            'name' => 'email',
            'type' => 'email',
            'label' => 'modules.users.fields.email',
            'readonly' => false,
            'required' => false,
          ),
          6 =>
          array(
            'name' => 'mobile',
            'type' => 'phone',
            'label' => 'modules.users.fields.mobile',
            'readonly' => false,
            'required' => false,
          ),
          7 =>
          array(
            'name' => 'description',
            'type' => 'longText',
            'label' => 'modules.defaults.description',
            'readonly' => false,
            'required' => true,
            'sortable' => true,
          ),
        ),
      ),
      1 =>
      array(
        'name' => 'User Information',
        'layout' =>
        array(
          0 =>
          array(
            'name' => 'type',
            'type' => 'select',
            'label' => 'modules.users.fields.type',
            'required' => false,
          ),
          1 =>
          array(
            'name' => 'is_admin',
            'type' => 'checkbox',
            'label' => 'modules.users.fields.is_admin',
            'readonly' => true,
            'required' => false,
          ),
          2 =>
          array(
            'name' => 'status',
            'type' => 'select',
            'label' => 'modules.users.fields.status',
            'readonly' => true,
            'required' => false,
          ),
        ),
      ),
      2 =>
      array(
        'name' => 'Meta',
        'layout' =>
        array(
          0 =>
          array(
            'name' => 'created_at',
            'type' => 'datetime',
            'label' => 'modules.defaults.created_at',
            'readonly' => true,
            'required' => true,
            'sortable' => true,
          ),
          1 =>
          array(
            'name' => 'updated_at',
            'type' => 'datetime',
            'label' => 'modules.defaults.updated_at',
            'readonly' => true,
            'required' => true,
            'sortable' => true,
          ),
        ),
      ),
    ),
  ),

  'linkingPanel' => array(
    "columns" => array(
      0 => array(
        "name" => "name",
        "type" => "text",
        "label" =>  "modules.defaults.name",
        "sortable" => true
      ),
      1 => array(
        "name" => "email",
        "type" => "email",
        "label" =>  "modules.users.fields.email",
        "sortable" => true
      ),
      2 => array(
        "name" => "created_at",
        "type" => "datetime",
        "label" => "modules.defaults.created_at",
        "sortable" => true
      ),
      3 => array(
        "name" => "updated_at",
        "type" => "datetime",
        "label" => "modules.defaults.updated_at",
        "sortable" => true
      ),
     )
  )
);
