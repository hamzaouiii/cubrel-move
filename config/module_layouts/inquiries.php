<?php

return array (
  'list' => 
  array (
    'columns' => 
    array (
      0 => 
      array (
        'name' => 'name',
        'type' => 'textfield',
        'label' => 'modules.defaults.name',
        'sortable' => true,
      ),
      1 => 
      array (
        'key' => 'inquiries_email',
        'name' => 'email',
        'type' => 'email',
        'label' => 'modules.inquiries.fields.email',
        'readonly' => false,
        'required' => false,
        'sortable' => false,
        'dropdown_list' => NULL,
      ),
      2 => 
      array (
        'key' => 'inquiries_status',
        'name' => 'status',
        'type' => 'dropdown',
        'label' => 'modules.inquiries.fields.status',
        'readonly' => false,
        'required' => false,
        'sortable' => false,
        'dropdown_list' => NULL,
      ),
      3 => 
      array (
        'key' => 'inquiries_phone',
        'name' => 'phone',
        'type' => 'textfield',
        'label' => 'modules.inquiries.fields.phone',
        'readonly' => false,
        'required' => false,
        'sortable' => false,
        'dropdown_list' => NULL,
      ),
      4 => 
      array (
        'name' => 'created_at',
        'type' => 'datetime',
        'label' => 'modules.defaults.created_at',
        'sortable' => true,
      ),
      5 => 
      array (
        'name' => 'updated_at',
        'type' => 'datetime',
        'label' => 'modules.defaults.updated_at',
        'sortable' => true,
      ),
    ),
  ),
  'record' => 
  array (
    'sections' => 
    array (
      0 => 
      array (
        'name' => 'Card',
        'layout' => 
        array (
          0 => 
          array (
            'name' => 'name',
            'type' => 'textfield',
            'label' => 'modules.defaults.name',
            'readonly' => false,
            'required' => true,
            'sortable' => true,
          ),
          1 => 
          array (
            'name' => 'email',
            'type' => 'email',
            'label' => 'modules.inquiries.fields.email',
            'readonly' => false,
            'required' => false,
          ),
          2 => 
          array (
            'name' => 'phone',
            'type' => 'textfield',
            'label' => 'modules.inquiries.fields.phone',
            'readonly' => false,
            'required' => false,
          ),
          3 => 
          array (
            'name' => 'status',
            'type' => 'dropdown',
            'label' => 'modules.inquiries.fields.status',
            'readonly' => false,
            'required' => false,
          ),
          4 => 
          array (
            'name' => 'message',
            'type' => 'longtext',
            'label' => 'modules.inquiries.fields.message',
            'readonly' => false,
            'required' => false,
          ),
          5 => 
          array (
            'name' => 'ip',
            'type' => 'textfield',
            'label' => 'modules.inquiries.fields.ip',
            'readonly' => false,
            'required' => false,
          ),
          6 => 
          array (
            'name' => 'user_agent',
            'type' => 'longtext',
            'label' => 'modules.inquiries.fields.user_agent',
            'readonly' => false,
            'required' => false,
          ),
          7 => 
          array (
            'name' => 'description',
            'type' => 'longText',
            'label' => 'modules.defaults.description',
            'readonly' => false,
            'required' => true,
            'sortable' => true,
          ),
          8 => 
          array (
            'name' => 'created_at',
            'type' => 'datetime',
            'label' => 'modules.defaults.created_at',
            'readonly' => true,
            'required' => true,
            'sortable' => true,
          ),
          9 => 
          array (
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
  'related' => 
  array (
    'columns' => 
    array (
      0 => 
      array (
        'layout' => 
        array (
          0 => 
          array (
            'name' => 'accounts_inquiries',
            'type' => 'one-to-many',
            'label' => 'relationships.accounts_inquiries',
            'panelHeader' => 
            array (
              0 => 
              array (
                'name' => 'name',
                'type' => 'textfield',
                'label' => 'modules.accounts.fields.name',
              ),
              1 => 
              array (
                'name' => 'phone',
                'type' => 'textfield',
                'label' => 'modules.accounts.fields.phone',
              ),
              2 => 
              array (
                'name' => 'website',
                'type' => 'textfield',
                'label' => 'modules.accounts.fields.website',
              ),
              3 => 
              array (
                'name' => 'email',
                'type' => 'email',
                'label' => 'modules.accounts.fields.email',
              ),
            ),
            'relationship' => 
            array (
              'id' => '36652d92-605e-4222-813b-acb96a40fc9b',
              'name' => 'accounts_inquiries',
              'role' => 'child',
              'side' => 'right',
              'label' => 'relationships.accounts_inquiries',
              'created_at' => '2026-02-26 12:51:55',
              'join_table' => 'relationship_links',
              'left_class' => 'App\\Models\\Modules\\Account',
              'other_side' => 'left',
              'updated_at' => '2026-02-26 12:51:55',
              'left_module' => 'accounts',
              'right_class' => 'App\\Models\\Modules\\ContactMessage',
              'current_side' => 'right',
              'related_slug' => 'accounts',
              'right_module' => 'inquiries',
              'related_class' => 'App\\Models\\Modules\\Account',
              'other_id_field' => 'left_id',
              'related_fields' => 
              array (
                0 => 
                array (
                  'id' => '3402c316-74c0-402c-91aa-f486ee4a298c',
                  'key' => 'accounts_updated_at',
                  'name' => 'updated_at',
                  'type' => 'datetime',
                  'label' => 'modules.accounts.fields.updated_at',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d23-719d-8e44-7dec9e463640',
                  'dropdown_list_id' => NULL,
                ),
                1 => 
                array (
                  'id' => '35a12507-6fb1-418a-8eea-f00ff1c4cff6',
                  'key' => 'accounts_shipping_address',
                  'name' => 'shipping_address',
                  'type' => 'longtext',
                  'label' => 'modules.accounts.fields.shipping_address',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d23-719d-8e44-7dec9e463640',
                  'dropdown_list_id' => NULL,
                ),
                2 => 
                array (
                  'id' => '3cd168c5-e2e5-47c3-9711-98ecd053e586',
                  'key' => 'accounts_description',
                  'name' => 'description',
                  'type' => 'longtext',
                  'label' => 'modules.accounts.fields.description',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d23-719d-8e44-7dec9e463640',
                  'dropdown_list_id' => NULL,
                ),
                3 => 
                array (
                  'id' => '567e5638-18b8-49d2-a0d4-5809f544853c',
                  'key' => 'accounts_email',
                  'name' => 'email',
                  'type' => 'email',
                  'label' => 'modules.accounts.fields.email',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d23-719d-8e44-7dec9e463640',
                  'dropdown_list_id' => NULL,
                ),
                4 => 
                array (
                  'id' => '6e214509-f49b-4490-812e-f434fdc8f628',
                  'key' => 'accounts_billing_address',
                  'name' => 'billing_address',
                  'type' => 'longtext',
                  'label' => 'modules.accounts.fields.billing_address',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d23-719d-8e44-7dec9e463640',
                  'dropdown_list_id' => NULL,
                ),
                5 => 
                array (
                  'id' => '80388c1c-c48a-4eb2-bd32-9ca5e9865a52',
                  'key' => 'accounts_city',
                  'name' => 'city',
                  'type' => 'textfield',
                  'label' => 'modules.accounts.fields.city',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d23-719d-8e44-7dec9e463640',
                  'dropdown_list_id' => NULL,
                ),
                6 => 
                array (
                  'id' => '92ddbac5-d8f5-4542-858c-40ebee57240e',
                  'key' => 'accounts_phone',
                  'name' => 'phone',
                  'type' => 'textfield',
                  'label' => 'modules.accounts.fields.phone',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d23-719d-8e44-7dec9e463640',
                  'dropdown_list_id' => NULL,
                ),
                7 => 
                array (
                  'id' => '9444e745-7130-4157-a14e-d7e8121c62e6',
                  'key' => 'accounts_website',
                  'name' => 'website',
                  'type' => 'textfield',
                  'label' => 'modules.accounts.fields.website',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d23-719d-8e44-7dec9e463640',
                  'dropdown_list_id' => NULL,
                ),
                8 => 
                array (
                  'id' => 'a749709c-20eb-4673-9885-7155586273c4',
                  'key' => 'accounts_country',
                  'name' => 'country',
                  'type' => 'textfield',
                  'label' => 'modules.accounts.fields.country',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d23-719d-8e44-7dec9e463640',
                  'dropdown_list_id' => NULL,
                ),
                9 => 
                array (
                  'id' => 'becc86c4-914e-4f76-94f2-0bf36acd1cd5',
                  'key' => 'accounts_name',
                  'name' => 'name',
                  'type' => 'textfield',
                  'label' => 'modules.accounts.fields.name',
                  'readonly' => false,
                  'required' => true,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d23-719d-8e44-7dec9e463640',
                  'dropdown_list_id' => NULL,
                ),
                10 => 
                array (
                  'id' => 'dac23314-0da8-4fe1-9796-84229aba3aff',
                  'key' => 'accounts_created_at',
                  'name' => 'created_at',
                  'type' => 'datetime',
                  'label' => 'modules.accounts.fields.created_at',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d23-719d-8e44-7dec9e463640',
                  'dropdown_list_id' => NULL,
                ),
              ),
              'left_module_key' => NULL,
              'current_id_field' => 'right_id',
              'right_module_key' => NULL,
              'current_module_id' => NULL,
              'relationship_type' => 'one-to-many',
            ),
          ),
        ),
      ),
      1 => 
      array (
        'layout' => 
        array (
          0 => 
          array (
            'name' => 'inquiries_emails',
            'type' => 'one-to-many',
            'label' => 'relationships.inquiries_emails',
            'panelHeader' => 
            array (
              0 => 
              array (
                'name' => 'subject',
                'type' => 'textfield',
                'label' => 'modules.emails.fields.subject',
              ),
              1 => 
              array (
                'name' => 'status',
                'type' => 'dropdown',
                'label' => 'modules.emails.fields.status',
              ),
              2 => 
              array (
                'name' => 'to',
                'type' => 'email',
                'label' => 'modules.emails.fields.to',
              ),
              3 => 
              array (
                'name' => 'updated_at',
                'type' => 'datetime',
                'label' => 'modules.emails.fields.updated_at',
              ),
              4 => 
              array (
                'name' => 'created_at',
                'type' => 'datetime',
                'label' => 'modules.emails.fields.created_at',
              ),
            ),
            'relationship' => 
            array (
              'id' => 'a475f480-db07-4377-b604-83c324d975b6',
              'name' => 'inquiries_emails',
              'role' => 'parent',
              'side' => 'left',
              'label' => 'relationships.inquiries_emails',
              'created_at' => '2026-02-26 12:51:55',
              'join_table' => 'relationship_links',
              'left_class' => 'App\\Models\\Modules\\ContactMessage',
              'other_side' => 'right',
              'updated_at' => '2026-02-26 12:51:55',
              'left_module' => 'inquiries',
              'right_class' => 'App\\Models\\Modules\\Email',
              'current_side' => 'left',
              'related_slug' => 'emails',
              'right_module' => 'emails',
              'related_class' => 'App\\Models\\Modules\\Email',
              'other_id_field' => 'right_id',
              'related_fields' => 
              array (
                0 => 
                array (
                  'id' => '1f9d4f5b-6459-41b8-b1c7-5080af3ee4bf',
                  'key' => 'emails_description',
                  'name' => 'description',
                  'type' => 'longtext',
                  'label' => 'modules.emails.fields.description',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d32-7255-82f6-59342a40af4b',
                  'dropdown_list_id' => NULL,
                ),
                1 => 
                array (
                  'id' => '26713f41-0337-4190-9c51-8ff9bbb31391',
                  'key' => 'emails_status',
                  'name' => 'status',
                  'type' => 'dropdown',
                  'label' => 'modules.emails.fields.status',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d32-7255-82f6-59342a40af4b',
                  'dropdown_list_id' => NULL,
                ),
                2 => 
                array (
                  'id' => '51621b8a-69d8-42b1-a1c7-6f1ecebe231d',
                  'key' => 'emails_mailable_class',
                  'name' => 'mailable_class',
                  'type' => 'textfield',
                  'label' => 'modules.emails.fields.mailable_class',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d32-7255-82f6-59342a40af4b',
                  'dropdown_list_id' => NULL,
                ),
                3 => 
                array (
                  'id' => '73c68003-dec2-4d1a-8cd1-caa3a9127906',
                  'key' => 'emails_subject',
                  'name' => 'subject',
                  'type' => 'textfield',
                  'label' => 'modules.emails.fields.subject',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d32-7255-82f6-59342a40af4b',
                  'dropdown_list_id' => NULL,
                ),
                4 => 
                array (
                  'id' => '97d640ba-ac82-4081-836d-b9fcb2c1bd38',
                  'key' => 'emails_related_id',
                  'name' => 'related_id',
                  'type' => 'textfield',
                  'label' => 'modules.emails.fields.related_id',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d32-7255-82f6-59342a40af4b',
                  'dropdown_list_id' => NULL,
                ),
                5 => 
                array (
                  'id' => 'c0060ceb-0025-479e-b8cd-8991dbcbeca2',
                  'key' => 'emails_updated_at',
                  'name' => 'updated_at',
                  'type' => 'datetime',
                  'label' => 'modules.emails.fields.updated_at',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d32-7255-82f6-59342a40af4b',
                  'dropdown_list_id' => NULL,
                ),
                6 => 
                array (
                  'id' => 'c2c6ef65-427b-4504-a2d4-19103035f16a',
                  'key' => 'emails_to',
                  'name' => 'to',
                  'type' => 'email',
                  'label' => 'modules.emails.fields.to',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d32-7255-82f6-59342a40af4b',
                  'dropdown_list_id' => NULL,
                ),
                7 => 
                array (
                  'id' => 'd9ef6ddd-4ceb-4793-adf9-97499ca64827',
                  'key' => 'emails_created_at',
                  'name' => 'created_at',
                  'type' => 'datetime',
                  'label' => 'modules.emails.fields.created_at',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d32-7255-82f6-59342a40af4b',
                  'dropdown_list_id' => NULL,
                ),
              ),
              'left_module_key' => NULL,
              'current_id_field' => 'left_id',
              'right_module_key' => NULL,
              'current_module_id' => NULL,
              'relationship_type' => 'one-to-many',
            ),
          ),
        ),
      ),
    ),
  ),
  'linking-panel' => 
  array (
    'columns' => 
    array (
      0 => 
      array (
        'name' => 'name',
        'type' => 'textfield',
        'label' => 'modules.defaults.name',
        'sortable' => true,
      ),
      1 => 
      array (
        'key' => 'inquiries_status',
        'name' => 'status',
        'type' => 'dropdown',
        'label' => 'modules.inquiries.fields.status',
        'readonly' => false,
        'required' => false,
        'sortable' => false,
        'dropdown_list' => NULL,
      ),
      2 => 
      array (
        'name' => 'created_at',
        'type' => 'datetime',
        'label' => 'modules.defaults.created_at',
        'sortable' => true,
      ),
      3 => 
      array (
        'name' => 'updated_at',
        'type' => 'datetime',
        'label' => 'modules.defaults.updated_at',
        'sortable' => true,
      ),
    ),
  ),
);
