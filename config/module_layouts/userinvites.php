<?php

return array(

  'list' =>
  array(
    'columns' =>
    array(
      0 =>
      array(
        'name' => 'email',
        'type' => 'email',
        'label' => 'modules.userinvites.fields.email',
      ),
      1 =>
      array(
        'name' => 'is_admin',
        'type' => 'checkbox',
        'label' => 'modules.userinvites.fields.is_admin',
      ),
      2 =>
      array(
        'name' => 'status',
        'type' => 'status',
        'label' => 'modules.userinvites.fields.status',
      ),
      3 =>
      array(
        'name' => 'invited_by',
        'type' => 'record',
        'label' => 'modules.userinvites.fields.invited_by',
      ),
      4 =>
      array(
        'name' => 'expires_at',
        'type' => 'datetime',
        'label' => 'modules.userinvites.fields.expires_at',
      ),
      5 =>
      array(
        'name' => 'created_at',
        'type' => 'datetime',
        'label' => 'modules.defaults.created_at',
      ),
    ),
  ),

);
