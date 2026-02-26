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
        'id' => '21c067b3-9498-4139-8211-f486647c38aa',
        'key' => 'contacts_account_id',
        'name' => 'account_id',
        'type' => 'relationship',
        'label' => 'modules.contacts.fields.account_id',
        'readonly' => false,
        'required' => false,
        'sortable' => true,
        'module_id' => '019c9a01-7d25-7136-a50d-5fa6fdc46656',
        'dropdown_list' => NULL,
        'dropdown_list_id' => NULL,
      ),
      2 => 
      array (
        'id' => 'ab103add-6343-4bb5-904c-04ec3447b202',
        'key' => 'contacts_position',
        'name' => 'position',
        'type' => 'textfield',
        'label' => 'modules.contacts.fields.position',
        'readonly' => false,
        'required' => false,
        'sortable' => true,
        'module_id' => '019c9a01-7d25-7136-a50d-5fa6fdc46656',
        'dropdown_list' => NULL,
        'dropdown_list_id' => NULL,
      ),
      3 => 
      array (
        'id' => '2e94cba5-b0dd-4959-b4a9-3a3863d4c915',
        'key' => 'contacts_phone',
        'name' => 'phone',
        'type' => 'textfield',
        'label' => 'modules.contacts.fields.phone',
        'readonly' => false,
        'required' => false,
        'sortable' => true,
        'module_id' => '019c9a01-7d25-7136-a50d-5fa6fdc46656',
        'dropdown_list' => NULL,
        'dropdown_list_id' => NULL,
      ),
      4 => 
      array (
        'id' => '36a49d40-b37f-4340-9d78-397ee7d9a64b',
        'key' => 'contacts_email',
        'name' => 'email',
        'type' => 'email',
        'label' => 'modules.contacts.fields.email',
        'readonly' => false,
        'required' => false,
        'sortable' => true,
        'module_id' => '019c9a01-7d25-7136-a50d-5fa6fdc46656',
        'dropdown_list' => NULL,
        'dropdown_list_id' => NULL,
      ),
      5 => 
      array (
        'name' => 'created_at',
        'type' => 'datetime',
        'label' => 'modules.defaults.created_at',
        'sortable' => true,
      ),
      6 => 
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
            'name' => 'account_id',
            'type' => 'relationship',
            'label' => 'modules.contacts.fields.account_id',
            'readonly' => false,
            'required' => false,
          ),
          2 => 
          array (
            'name' => 'position',
            'type' => 'textfield',
            'label' => 'modules.contacts.fields.position',
            'readonly' => false,
            'required' => false,
          ),
          3 => 
          array (
            'name' => 'phone',
            'type' => 'textfield',
            'label' => 'modules.contacts.fields.phone',
            'readonly' => false,
            'required' => false,
          ),
          4 => 
          array (
            'name' => 'email',
            'type' => 'email',
            'label' => 'modules.contacts.fields.email',
            'readonly' => false,
            'required' => false,
          ),
          5 => 
          array (
            'name' => 'notes',
            'type' => 'longtext',
            'label' => 'modules.contacts.fields.notes',
            'readonly' => false,
            'required' => false,
          ),
          6 => 
          array (
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
      array (
        'name' => 'System',
        'layout' => 
        array (
          0 => 
          array (
            'name' => 'created_at',
            'type' => 'datetime',
            'label' => 'modules.defaults.created_at',
            'readonly' => true,
            'required' => true,
            'sortable' => true,
          ),
          1 => 
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
            'name' => 'accounts_contacts',
            'type' => 'one-to-many',
            'label' => 'relationships.accounts_contacts',
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
                'name' => 'description',
                'type' => 'longtext',
                'label' => 'modules.accounts.fields.description',
              ),
              3 => 
              array (
                'name' => 'website',
                'type' => 'textfield',
                'label' => 'modules.accounts.fields.website',
              ),
            ),
            'relationship' => 
            array (
              'id' => '1a23945d-414a-4a3e-a55d-9e479db574f3',
              'name' => 'accounts_contacts',
              'role' => 'child',
              'side' => 'right',
              'label' => 'relationships.accounts_contacts',
              'created_at' => '2026-02-26 12:51:55',
              'join_table' => 'relationship_links',
              'left_class' => 'App\\Models\\Modules\\Account',
              'other_side' => 'left',
              'updated_at' => '2026-02-26 12:51:55',
              'left_module' => 'accounts',
              'right_class' => 'App\\Models\\Modules\\Contact',
              'current_side' => 'right',
              'related_slug' => 'accounts',
              'right_module' => 'contacts',
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
          1 => 
          array (
            'name' => 'contacts_cases',
            'type' => 'one-to-many',
            'label' => 'relationships.contacts_cases',
            'panelHeader' => 
            array (
              0 => 
              array (
                'name' => 'name',
                'type' => 'textfield',
                'label' => 'modules.cases.fields.name',
              ),
              1 => 
              array (
                'name' => 'status',
                'type' => 'dropdown',
                'label' => 'modules.cases.fields.status',
              ),
              2 => 
              array (
                'name' => 'priority',
                'type' => 'dropdown',
                'label' => 'modules.cases.fields.priority',
              ),
              3 => 
              array (
                'name' => 'opened_at',
                'type' => 'datetime',
                'label' => 'modules.cases.fields.opened_at',
              ),
              4 => 
              array (
                'name' => 'closed_at',
                'type' => 'datetime',
                'label' => 'modules.cases.fields.closed_at',
              ),
            ),
            'relationship' => 
            array (
              'id' => '8534a5a5-726c-4729-b405-83ac6bc66b82',
              'name' => 'contacts_cases',
              'role' => 'parent',
              'side' => 'left',
              'label' => 'relationships.contacts_cases',
              'created_at' => '2026-02-26 12:51:55',
              'join_table' => 'relationship_links',
              'left_class' => 'App\\Models\\Modules\\Contact',
              'other_side' => 'right',
              'updated_at' => '2026-02-26 12:51:55',
              'left_module' => 'contacts',
              'right_class' => 'App\\Models\\Modules\\SupportCase',
              'current_side' => 'left',
              'related_slug' => 'cases',
              'right_module' => 'cases',
              'related_class' => 'App\\Models\\Modules\\SupportCase',
              'other_id_field' => 'right_id',
              'related_fields' => 
              array (
                0 => 
                array (
                  'id' => '1f5f53de-8cde-4b57-8f21-9d2d7a237a3e',
                  'key' => 'cases_opened_at',
                  'name' => 'opened_at',
                  'type' => 'datetime',
                  'label' => 'modules.cases.fields.opened_at',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d30-709f-93bb-af1934744060',
                  'dropdown_list_id' => NULL,
                ),
                1 => 
                array (
                  'id' => '2d0e78a5-d39f-4174-9d72-3d6f78dc7587',
                  'key' => 'cases_account_id',
                  'name' => 'account_id',
                  'type' => 'relationship',
                  'label' => 'modules.cases.fields.account_id',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d30-709f-93bb-af1934744060',
                  'dropdown_list_id' => NULL,
                ),
                2 => 
                array (
                  'id' => '3c36adae-35e3-4b03-af78-c0161fbe04c9',
                  'key' => 'cases_closed_at',
                  'name' => 'closed_at',
                  'type' => 'datetime',
                  'label' => 'modules.cases.fields.closed_at',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d30-709f-93bb-af1934744060',
                  'dropdown_list_id' => NULL,
                ),
                3 => 
                array (
                  'id' => '49098d13-bfee-41ef-b7ec-03e677f07cb0',
                  'key' => 'cases_name',
                  'name' => 'name',
                  'type' => 'textfield',
                  'label' => 'modules.cases.fields.name',
                  'readonly' => false,
                  'required' => true,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d30-709f-93bb-af1934744060',
                  'dropdown_list_id' => NULL,
                ),
                4 => 
                array (
                  'id' => '7c6c6599-5d2d-4169-85f1-6b46277ab7b8',
                  'key' => 'cases_contact_id',
                  'name' => 'contact_id',
                  'type' => 'relationship',
                  'label' => 'modules.cases.fields.contact_id',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d30-709f-93bb-af1934744060',
                  'dropdown_list_id' => NULL,
                ),
                5 => 
                array (
                  'id' => '8aaa72e4-6d8b-4c26-84d7-0388e6881c0c',
                  'key' => 'cases_status',
                  'name' => 'status',
                  'type' => 'dropdown',
                  'label' => 'modules.cases.fields.status',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d30-709f-93bb-af1934744060',
                  'dropdown_list_id' => NULL,
                ),
                6 => 
                array (
                  'id' => '8b20a4aa-056c-4f17-844a-b64a08899b78',
                  'key' => 'cases_priority',
                  'name' => 'priority',
                  'type' => 'dropdown',
                  'label' => 'modules.cases.fields.priority',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d30-709f-93bb-af1934744060',
                  'dropdown_list_id' => NULL,
                ),
                7 => 
                array (
                  'id' => '8b803c3d-0c70-4d60-afaa-d813b8669f44',
                  'key' => 'cases_subject',
                  'name' => 'subject',
                  'type' => 'textfield',
                  'label' => 'modules.cases.fields.subject',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d30-709f-93bb-af1934744060',
                  'dropdown_list_id' => NULL,
                ),
                8 => 
                array (
                  'id' => 'ab290084-4e2f-4e32-a2ae-3786ff73e239',
                  'key' => 'cases_description',
                  'name' => 'description',
                  'type' => 'longtext',
                  'label' => 'modules.cases.fields.description',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d30-709f-93bb-af1934744060',
                  'dropdown_list_id' => NULL,
                ),
                9 => 
                array (
                  'id' => 'c2c5d0c6-d83d-4ab0-9ba5-65a8a4fb8e30',
                  'key' => 'cases_created_at',
                  'name' => 'created_at',
                  'type' => 'datetime',
                  'label' => 'modules.cases.fields.created_at',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d30-709f-93bb-af1934744060',
                  'dropdown_list_id' => NULL,
                ),
                10 => 
                array (
                  'id' => 'd535a96a-1cf3-4eba-a4b9-c705597bf127',
                  'key' => 'cases_updated_at',
                  'name' => 'updated_at',
                  'type' => 'datetime',
                  'label' => 'modules.cases.fields.updated_at',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d30-709f-93bb-af1934744060',
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
          2 => 
          array (
            'name' => 'contacts_emails',
            'type' => 'one-to-many',
            'label' => 'relationships.contacts_emails',
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
                'name' => 'created_at',
                'type' => 'datetime',
                'label' => 'modules.emails.fields.created_at',
              ),
              4 => 
              array (
                'name' => 'updated_at',
                'type' => 'datetime',
                'label' => 'modules.emails.fields.updated_at',
              ),
            ),
            'relationship' => 
            array (
              'id' => '0bcfaf93-5292-4ead-ac4f-b0a939180828',
              'name' => 'contacts_emails',
              'role' => 'parent',
              'side' => 'left',
              'label' => 'relationships.contacts_emails',
              'created_at' => '2026-02-26 12:51:55',
              'join_table' => 'relationship_links',
              'left_class' => 'App\\Models\\Modules\\Contact',
              'other_side' => 'right',
              'updated_at' => '2026-02-26 12:51:55',
              'left_module' => 'contacts',
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
      1 => 
      array (
        'layout' => 
        array (
          0 => 
          array (
            'name' => 'contacts_leads',
            'type' => 'one-to-one',
            'label' => 'relationships.contacts_leads',
            'panelHeader' => 
            array (
              0 => 
              array (
                'name' => 'name',
                'type' => 'textfield',
                'label' => 'modules.leads.fields.name',
              ),
              1 => 
              array (
                'name' => 'email',
                'type' => 'email',
                'label' => 'modules.leads.fields.email',
              ),
              2 => 
              array (
                'name' => 'phone',
                'type' => 'textfield',
                'label' => 'modules.leads.fields.phone',
              ),
            ),
            'relationship' => 
            array (
              'id' => '95339ab9-7c1d-4642-aaa5-66cdb260a0ff',
              'name' => 'contacts_leads',
              'role' => 'sibling',
              'side' => 'left',
              'label' => 'relationships.contacts_leads',
              'created_at' => '2026-02-26 12:51:55',
              'join_table' => 'relationship_links',
              'left_class' => 'App\\Models\\Modules\\Contact',
              'other_side' => 'right',
              'updated_at' => '2026-02-26 12:51:55',
              'left_module' => 'contacts',
              'right_class' => 'App\\Models\\Modules\\Lead',
              'current_side' => 'left',
              'related_slug' => 'leads',
              'right_module' => 'leads',
              'related_class' => 'App\\Models\\Modules\\Lead',
              'other_id_field' => 'right_id',
              'related_fields' => 
              array (
                0 => 
                array (
                  'id' => '17b914ee-e060-4be4-950b-97ae6f586ac6',
                  'key' => 'leads_first_name',
                  'name' => 'first_name',
                  'type' => 'textfield',
                  'label' => 'modules.leads.fields.first_name',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d1e-719b-bcf0-8a132c1108a4',
                  'dropdown_list_id' => NULL,
                ),
                1 => 
                array (
                  'id' => '3984e0db-9a88-48dc-9551-6834dd3edd90',
                  'key' => 'leads_created_at',
                  'name' => 'created_at',
                  'type' => 'datetime',
                  'label' => 'modules.leads.fields.created_at',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d1e-719b-bcf0-8a132c1108a4',
                  'dropdown_list_id' => NULL,
                ),
                2 => 
                array (
                  'id' => '3c95a406-d160-4c1e-adcb-10c2a215dd4c',
                  'key' => 'leads_street',
                  'name' => 'street',
                  'type' => 'longtext',
                  'label' => 'modules.leads.fields.street',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d1e-719b-bcf0-8a132c1108a4',
                  'dropdown_list_id' => NULL,
                ),
                3 => 
                array (
                  'id' => '4c8e8d3e-4551-47cf-80aa-ab2e961ea902',
                  'key' => 'leads_description',
                  'name' => 'description',
                  'type' => 'longtext',
                  'label' => 'modules.leads.fields.description',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d1e-719b-bcf0-8a132c1108a4',
                  'dropdown_list_id' => NULL,
                ),
                4 => 
                array (
                  'id' => '57d029c8-4173-4eee-9f2e-688208520e53',
                  'key' => 'leads_email',
                  'name' => 'email',
                  'type' => 'email',
                  'label' => 'modules.leads.fields.email',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d1e-719b-bcf0-8a132c1108a4',
                  'dropdown_list_id' => NULL,
                ),
                5 => 
                array (
                  'id' => '5d3657c4-d698-4b71-b171-bc87c56f96d2',
                  'key' => 'leads_company',
                  'name' => 'company',
                  'type' => 'textfield',
                  'label' => 'modules.leads.fields.company',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d1e-719b-bcf0-8a132c1108a4',
                  'dropdown_list_id' => NULL,
                ),
                6 => 
                array (
                  'id' => '735382c1-80d1-4b75-aa4d-12014e1a103a',
                  'key' => 'leads_phone',
                  'name' => 'phone',
                  'type' => 'textfield',
                  'label' => 'modules.leads.fields.phone',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d1e-719b-bcf0-8a132c1108a4',
                  'dropdown_list_id' => NULL,
                ),
                7 => 
                array (
                  'id' => '82c1a60b-b5b4-43cb-9d0a-4865ac6a7d00',
                  'key' => 'leads_zip',
                  'name' => 'zip',
                  'type' => 'textfield',
                  'label' => 'modules.leads.fields.zip',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d1e-719b-bcf0-8a132c1108a4',
                  'dropdown_list_id' => NULL,
                ),
                8 => 
                array (
                  'id' => '89ad5151-1c73-45b4-a1fb-d0e80c758946',
                  'key' => 'leads_city',
                  'name' => 'city',
                  'type' => 'textfield',
                  'label' => 'modules.leads.fields.city',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d1e-719b-bcf0-8a132c1108a4',
                  'dropdown_list_id' => NULL,
                ),
                9 => 
                array (
                  'id' => 'a807a244-5a75-47bf-8204-d8203bdd2a46',
                  'key' => 'leads_updated_at',
                  'name' => 'updated_at',
                  'type' => 'datetime',
                  'label' => 'modules.leads.fields.updated_at',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d1e-719b-bcf0-8a132c1108a4',
                  'dropdown_list_id' => NULL,
                ),
                10 => 
                array (
                  'id' => 'ee05478d-e921-45cd-a270-eaf4866fd274',
                  'key' => 'leads_name',
                  'name' => 'name',
                  'type' => 'textfield',
                  'label' => 'modules.leads.fields.name',
                  'readonly' => false,
                  'required' => true,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d1e-719b-bcf0-8a132c1108a4',
                  'dropdown_list_id' => NULL,
                ),
                11 => 
                array (
                  'id' => 'f1e2dcce-8e8d-4cfb-92b5-5d4cb6470b0b',
                  'key' => 'leads_last_name',
                  'name' => 'last_name',
                  'type' => 'textfield',
                  'label' => 'modules.leads.fields.last_name',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d1e-719b-bcf0-8a132c1108a4',
                  'dropdown_list_id' => NULL,
                ),
              ),
              'left_module_key' => NULL,
              'current_id_field' => 'left_id',
              'right_module_key' => NULL,
              'current_module_id' => NULL,
              'relationship_type' => 'one-to-one',
            ),
          ),
          1 => 
          array (
            'name' => 'opportunities_contacts',
            'type' => 'many-to-many',
            'label' => 'relationships.opportunities_contacts',
            'panelHeader' => 
            array (
              0 => 
              array (
                'name' => 'name',
                'type' => 'textfield',
                'label' => 'modules.opportunities.fields.name',
              ),
              1 => 
              array (
                'name' => 'type',
                'type' => 'dropdown',
                'label' => 'modules.opportunities.fields.type',
              ),
              2 => 
              array (
                'name' => 'sales_stage',
                'type' => 'dropdown',
                'label' => 'modules.opportunities.fields.sales_stage',
              ),
              3 => 
              array (
                'name' => 'expected_close_date',
                'type' => 'date',
                'label' => 'modules.opportunities.fields.expected_close_date',
              ),
            ),
            'relationship' => 
            array (
              'id' => '0d39eb09-4497-46d8-856f-f31752c9c90d',
              'name' => 'opportunities_contacts',
              'role' => 'related',
              'side' => 'right',
              'label' => 'relationships.opportunities_contacts',
              'created_at' => '2026-02-26 12:51:55',
              'join_table' => 'relationship_links',
              'left_class' => 'App\\Models\\Modules\\Opportunity',
              'other_side' => 'left',
              'updated_at' => '2026-02-26 12:51:55',
              'left_module' => 'opportunities',
              'right_class' => 'App\\Models\\Modules\\Contact',
              'current_side' => 'right',
              'related_slug' => 'opportunities',
              'right_module' => 'contacts',
              'related_class' => 'App\\Models\\Modules\\Opportunity',
              'other_id_field' => 'left_id',
              'related_fields' => 
              array (
                0 => 
                array (
                  'id' => '19c0a630-f246-4f21-9d9e-46b28d3b7fd3',
                  'key' => 'opportunities_name',
                  'name' => 'name',
                  'type' => 'textfield',
                  'label' => 'modules.opportunities.fields.name',
                  'readonly' => false,
                  'required' => true,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d27-7102-ad87-fcb10376ce88',
                  'dropdown_list_id' => NULL,
                ),
                1 => 
                array (
                  'id' => '332cef23-bdfe-4c37-a0ee-618b8061f469',
                  'key' => 'opportunities_account_id',
                  'name' => 'account_id',
                  'type' => 'relationship',
                  'label' => 'modules.opportunities.fields.account_id',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d27-7102-ad87-fcb10376ce88',
                  'dropdown_list_id' => NULL,
                ),
                2 => 
                array (
                  'id' => '66bf23f2-83b8-4f66-bf0a-e2684411b7ff',
                  'key' => 'opportunities_assigned_user_id',
                  'name' => 'assigned_user_id',
                  'type' => 'relationship',
                  'label' => 'modules.opportunities.fields.assigned_user_id',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d27-7102-ad87-fcb10376ce88',
                  'dropdown_list_id' => NULL,
                ),
                3 => 
                array (
                  'id' => '81024ac2-12b6-4db0-ab8c-2ca934856413',
                  'key' => 'opportunities_description',
                  'name' => 'description',
                  'type' => 'longtext',
                  'label' => 'modules.opportunities.fields.description',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d27-7102-ad87-fcb10376ce88',
                  'dropdown_list_id' => NULL,
                ),
                4 => 
                array (
                  'id' => '8d3af557-a3cd-465a-9178-392f008cdfe4',
                  'key' => 'opportunities_expected_close_date',
                  'name' => 'expected_close_date',
                  'type' => 'date',
                  'label' => 'modules.opportunities.fields.expected_close_date',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d27-7102-ad87-fcb10376ce88',
                  'dropdown_list_id' => NULL,
                ),
                5 => 
                array (
                  'id' => '8d918237-d5cf-4408-9025-d02e7a8e98dc',
                  'key' => 'opportunities_type',
                  'name' => 'type',
                  'type' => 'dropdown',
                  'label' => 'modules.opportunities.fields.type',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d27-7102-ad87-fcb10376ce88',
                  'dropdown_list_id' => NULL,
                ),
                6 => 
                array (
                  'id' => '968d2028-fb71-48ae-899d-ee8cc74b7d6e',
                  'key' => 'opportunities_probability',
                  'name' => 'probability',
                  'type' => 'number',
                  'label' => 'modules.opportunities.fields.probability',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d27-7102-ad87-fcb10376ce88',
                  'dropdown_list_id' => NULL,
                ),
                7 => 
                array (
                  'id' => 'af743728-390a-41c7-bc79-d6b4201b542a',
                  'key' => 'opportunities_created_at',
                  'name' => 'created_at',
                  'type' => 'datetime',
                  'label' => 'modules.opportunities.fields.created_at',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d27-7102-ad87-fcb10376ce88',
                  'dropdown_list_id' => NULL,
                ),
                8 => 
                array (
                  'id' => 'b8c0af5e-e42f-4938-bfa5-2ad2c52316dc',
                  'key' => 'opportunities_amount',
                  'name' => 'amount',
                  'type' => 'textfield',
                  'label' => 'modules.opportunities.fields.amount',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d27-7102-ad87-fcb10376ce88',
                  'dropdown_list_id' => NULL,
                ),
                9 => 
                array (
                  'id' => 'eb12e7d3-5033-42c4-a8c9-eb0f3ab2dc06',
                  'key' => 'opportunities_currency',
                  'name' => 'currency',
                  'type' => 'textfield',
                  'label' => 'modules.opportunities.fields.currency',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d27-7102-ad87-fcb10376ce88',
                  'dropdown_list_id' => NULL,
                ),
                10 => 
                array (
                  'id' => 'ecda194e-44e9-440e-baf3-199be52e011a',
                  'key' => 'opportunities_sales_stage',
                  'name' => 'sales_stage',
                  'type' => 'dropdown',
                  'label' => 'modules.opportunities.fields.sales_stage',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d27-7102-ad87-fcb10376ce88',
                  'dropdown_list_id' => NULL,
                ),
                11 => 
                array (
                  'id' => 'f48867df-2cfa-4563-8af9-a2a28cb5db7e',
                  'key' => 'opportunities_updated_at',
                  'name' => 'updated_at',
                  'type' => 'datetime',
                  'label' => 'modules.opportunities.fields.updated_at',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d27-7102-ad87-fcb10376ce88',
                  'dropdown_list_id' => NULL,
                ),
              ),
              'left_module_key' => NULL,
              'current_id_field' => 'right_id',
              'right_module_key' => NULL,
              'current_module_id' => NULL,
              'relationship_type' => 'many-to-many',
            ),
          ),
          2 => 
          array (
            'name' => 'contacts_invoices',
            'type' => 'one-to-many',
            'label' => 'relationships.contacts_invoices',
            'panelHeader' => 
            array (
              0 => 
              array (
                'name' => 'name',
                'type' => 'textfield',
                'label' => 'modules.invoices.fields.name',
              ),
              1 => 
              array (
                'name' => 'number',
                'type' => 'number',
                'label' => 'modules.invoices.fields.number',
              ),
              2 => 
              array (
                'name' => 'total',
                'type' => 'number',
                'label' => 'modules.invoices.fields.total',
              ),
              3 => 
              array (
                'name' => 'due_date',
                'type' => 'date',
                'label' => 'modules.invoices.fields.due_date',
              ),
              4 => 
              array (
                'name' => 'status',
                'type' => 'dropdown',
                'label' => 'modules.invoices.fields.status',
              ),
              5 => 
              array (
                'name' => 'issue_date',
                'type' => 'date',
                'label' => 'modules.invoices.fields.issue_date',
              ),
            ),
            'relationship' => 
            array (
              'id' => '5e088541-e79b-4da5-b987-6e25e296d099',
              'name' => 'contacts_invoices',
              'role' => 'parent',
              'side' => 'left',
              'label' => 'relationships.contacts_invoices',
              'created_at' => '2026-02-26 12:51:55',
              'join_table' => 'relationship_links',
              'left_class' => 'App\\Models\\Modules\\Contact',
              'other_side' => 'right',
              'updated_at' => '2026-02-26 12:51:55',
              'left_module' => 'contacts',
              'right_class' => 'App\\Models\\Modules\\Invoice',
              'current_side' => 'left',
              'related_slug' => 'invoices',
              'right_module' => 'invoices',
              'related_class' => 'App\\Models\\Modules\\Invoice',
              'other_id_field' => 'right_id',
              'related_fields' => 
              array (
                0 => 
                array (
                  'id' => '00d5aca1-6f1d-4ae4-9ad3-1e8bcd133c7a',
                  'key' => 'invoices_description',
                  'name' => 'description',
                  'type' => 'longtext',
                  'label' => 'modules.invoices.fields.description',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d2d-7006-ad73-f2ae19efe0ec',
                  'dropdown_list_id' => NULL,
                ),
                1 => 
                array (
                  'id' => '087e790c-a206-4007-8cef-82ddcef4f81b',
                  'key' => 'invoices_total',
                  'name' => 'total',
                  'type' => 'number',
                  'label' => 'modules.invoices.fields.total',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d2d-7006-ad73-f2ae19efe0ec',
                  'dropdown_list_id' => NULL,
                ),
                2 => 
                array (
                  'id' => '14d9ed78-1ef6-4909-a20f-b891f0a2beb5',
                  'key' => 'invoices_account_id',
                  'name' => 'account_id',
                  'type' => 'relationship',
                  'label' => 'modules.invoices.fields.account_id',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d2d-7006-ad73-f2ae19efe0ec',
                  'dropdown_list_id' => NULL,
                ),
                3 => 
                array (
                  'id' => '24e14aa1-a77d-49fa-a29a-70d8a3ce73d8',
                  'key' => 'invoices_number',
                  'name' => 'number',
                  'type' => 'number',
                  'label' => 'modules.invoices.fields.number',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d2d-7006-ad73-f2ae19efe0ec',
                  'dropdown_list_id' => NULL,
                ),
                4 => 
                array (
                  'id' => '315ce1ab-60bf-4859-ac4a-d69bfafc4a28',
                  'key' => 'invoices_created_at',
                  'name' => 'created_at',
                  'type' => 'datetime',
                  'label' => 'modules.invoices.fields.created_at',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d2d-7006-ad73-f2ae19efe0ec',
                  'dropdown_list_id' => NULL,
                ),
                5 => 
                array (
                  'id' => '3e23f992-5e15-48ab-9c67-36742c24cd2c',
                  'key' => 'invoices_notes',
                  'name' => 'notes',
                  'type' => 'longtext',
                  'label' => 'modules.invoices.fields.notes',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d2d-7006-ad73-f2ae19efe0ec',
                  'dropdown_list_id' => NULL,
                ),
                6 => 
                array (
                  'id' => '4a7e5061-aa97-44d2-959e-f1991929e884',
                  'key' => 'invoices_due_date',
                  'name' => 'due_date',
                  'type' => 'date',
                  'label' => 'modules.invoices.fields.due_date',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d2d-7006-ad73-f2ae19efe0ec',
                  'dropdown_list_id' => NULL,
                ),
                7 => 
                array (
                  'id' => '67f3ba7f-45c9-4e1d-b98f-bd9d75297a7c',
                  'key' => 'invoices_quote_id',
                  'name' => 'quote_id',
                  'type' => 'dropdown',
                  'label' => 'modules.invoices.fields.quote_id',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d2d-7006-ad73-f2ae19efe0ec',
                  'dropdown_list_id' => NULL,
                ),
                8 => 
                array (
                  'id' => '755025a7-6522-4d65-a975-3e25b92a13c9',
                  'key' => 'invoices_status',
                  'name' => 'status',
                  'type' => 'dropdown',
                  'label' => 'modules.invoices.fields.status',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d2d-7006-ad73-f2ae19efe0ec',
                  'dropdown_list_id' => NULL,
                ),
                9 => 
                array (
                  'id' => '76555618-6cda-4da4-9111-9b0a583168ee',
                  'key' => 'invoices_issue_date',
                  'name' => 'issue_date',
                  'type' => 'date',
                  'label' => 'modules.invoices.fields.issue_date',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d2d-7006-ad73-f2ae19efe0ec',
                  'dropdown_list_id' => NULL,
                ),
                10 => 
                array (
                  'id' => 'a07b65ef-dab8-4801-8df7-bd13eb2c7ec8',
                  'key' => 'invoices_tax',
                  'name' => 'tax',
                  'type' => 'number',
                  'label' => 'modules.invoices.fields.tax',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d2d-7006-ad73-f2ae19efe0ec',
                  'dropdown_list_id' => NULL,
                ),
                11 => 
                array (
                  'id' => 'c9036f17-ab41-45bf-965b-2a1d56d0e648',
                  'key' => 'invoices_currency',
                  'name' => 'currency',
                  'type' => 'textfield',
                  'label' => 'modules.invoices.fields.currency',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d2d-7006-ad73-f2ae19efe0ec',
                  'dropdown_list_id' => NULL,
                ),
                12 => 
                array (
                  'id' => 'd8c0ad44-3a99-44f2-99b4-68862cdccc80',
                  'key' => 'invoices_name',
                  'name' => 'name',
                  'type' => 'textfield',
                  'label' => 'modules.invoices.fields.name',
                  'readonly' => false,
                  'required' => true,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d2d-7006-ad73-f2ae19efe0ec',
                  'dropdown_list_id' => NULL,
                ),
                13 => 
                array (
                  'id' => 'dc1c01b3-415b-457d-b87c-7a39ac32c7ae',
                  'key' => 'invoices_contact_id',
                  'name' => 'contact_id',
                  'type' => 'relationship',
                  'label' => 'modules.invoices.fields.contact_id',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d2d-7006-ad73-f2ae19efe0ec',
                  'dropdown_list_id' => NULL,
                ),
                14 => 
                array (
                  'id' => 'dd8f38dd-b237-496e-8d60-8f5fc3f3fb8e',
                  'key' => 'invoices_subtotal',
                  'name' => 'subtotal',
                  'type' => 'number',
                  'label' => 'modules.invoices.fields.subtotal',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d2d-7006-ad73-f2ae19efe0ec',
                  'dropdown_list_id' => NULL,
                ),
                15 => 
                array (
                  'id' => 'ec70306b-8735-4bc2-90cb-4f386ac39cec',
                  'key' => 'invoices_updated_at',
                  'name' => 'updated_at',
                  'type' => 'datetime',
                  'label' => 'modules.invoices.fields.updated_at',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d2d-7006-ad73-f2ae19efe0ec',
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
        'id' => '21c067b3-9498-4139-8211-f486647c38aa',
        'key' => 'contacts_account_id',
        'name' => 'account_id',
        'type' => 'relationship',
        'label' => 'modules.contacts.fields.account_id',
        'readonly' => false,
        'required' => false,
        'sortable' => true,
        'module_id' => '019c9a01-7d25-7136-a50d-5fa6fdc46656',
        'dropdown_list' => NULL,
        'dropdown_list_id' => NULL,
      ),
      2 => 
      array (
        'id' => '2e94cba5-b0dd-4959-b4a9-3a3863d4c915',
        'key' => 'contacts_phone',
        'name' => 'phone',
        'type' => 'textfield',
        'label' => 'modules.contacts.fields.phone',
        'readonly' => false,
        'required' => false,
        'sortable' => true,
        'module_id' => '019c9a01-7d25-7136-a50d-5fa6fdc46656',
        'dropdown_list' => NULL,
        'dropdown_list_id' => NULL,
      ),
      3 => 
      array (
        'id' => '36a49d40-b37f-4340-9d78-397ee7d9a64b',
        'key' => 'contacts_email',
        'name' => 'email',
        'type' => 'email',
        'label' => 'modules.contacts.fields.email',
        'readonly' => false,
        'required' => false,
        'sortable' => true,
        'module_id' => '019c9a01-7d25-7136-a50d-5fa6fdc46656',
        'dropdown_list' => NULL,
        'dropdown_list_id' => NULL,
      ),
      4 => 
      array (
        'id' => 'ab103add-6343-4bb5-904c-04ec3447b202',
        'key' => 'contacts_position',
        'name' => 'position',
        'type' => 'textfield',
        'label' => 'modules.contacts.fields.position',
        'readonly' => false,
        'required' => false,
        'sortable' => true,
        'module_id' => '019c9a01-7d25-7136-a50d-5fa6fdc46656',
        'dropdown_list' => NULL,
        'dropdown_list_id' => NULL,
      ),
    ),
  ),
);
