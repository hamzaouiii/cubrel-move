<?php

return array(
  'list' =>
  array(
    'columns' =>
    array(
      0 =>
      array(
        'name' => 'name',
        'type' => 'text',
        'label' => 'modules.defaults.name',
        'sortable' => true,
      ),
      1 =>
      array(
        'key' => 'cases_subject',
        'name' => 'subject',
        'type' => 'text',
        'label' => 'modules.cases.fields.subject',
        'readonly' => false,
        'required' => false,
        'sortable' => false,
        'dropdown_list' => NULL,
      ),
      2 =>
      array(
        'key' => 'cases_status',
        'name' => 'status',
        'type' => 'select',
        'label' => 'modules.cases.fields.status',
        'readonly' => false,
        'required' => false,
        'sortable' => false,
        'dropdown_list' => NULL,
      ),
      3 =>
      array(
        'key' => 'cases_priority',
        'name' => 'priority',
        'type' => 'select',
        'label' => 'modules.cases.fields.priority',
        'readonly' => false,
        'required' => false,
        'sortable' => false,
        'dropdown_list' => NULL,
      ),
      4 =>
      array(
        'key' => 'cases_opened_at',
        'name' => 'opened_at',
        'type' => 'datetime',
        'label' => 'modules.cases.fields.opened_at',
        'readonly' => false,
        'required' => false,
        'sortable' => false,
        'dropdown_list' => NULL,
      ),
      5 =>
      array(
        'key' => 'cases_closed_at',
        'name' => 'closed_at',
        'type' => 'datetime',
        'label' => 'modules.cases.fields.closed_at',
        'readonly' => false,
        'required' => false,
        'sortable' => false,
        'dropdown_list' => NULL,
      ),
    ),
  ),
  'related' =>
  array(
    'columns' =>
    array(
      0 =>
      array(
        'layout' =>
        array(
          0 =>
          array(
            'name' => 'accounts_cases',
            'type' => 'one-to-many',
            'label' => 'relationships.accounts_cases',
            'panelHeader' =>
            array(
              0 =>
              array(
                'name' => 'name',
                'type' => 'text',
                'label' => 'modules.accounts.fields.name',
              ),
              1 =>
              array(
                'name' => 'email',
                'type' => 'email',
                'label' => 'modules.accounts.fields.email',
              ),
              2 =>
              array(
                'name' => 'phone',
                'type' => 'text',
                'label' => 'modules.accounts.fields.phone',
              ),
              3 =>
              array(
                'name' => 'website',
                'type' => 'text',
                'label' => 'modules.accounts.fields.website',
              ),
            ),
            'relationship' =>
            array(
              'id' => 'f1411f99-3e27-4a56-a12e-33fd94fa74a5',
              'name' => 'accounts_cases',
              'role' => 'child',
              'side' => 'right',
              'label' => 'relationships.accounts_cases',
              'created_at' => '2026-02-26 12:51:55',
              'join_table' => 'relationship_links',
              'left_class' => 'App\\Models\\Modules\\Account',
              'other_side' => 'left',
              'updated_at' => '2026-02-26 12:51:55',
              'left_module' => 'accounts',
              'right_class' => 'App\\Models\\Modules\\SupportCase',
              'current_side' => 'right',
              'related_slug' => 'accounts',
              'right_module' => 'cases',
              'related_class' => 'App\\Models\\Modules\\Account',
              'other_id_field' => 'left_id',
              'related_fields' =>
              array(
                0 =>
                array(
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
                array(
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
                array(
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
                array(
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
                array(
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
                array(
                  'id' => '80388c1c-c48a-4eb2-bd32-9ca5e9865a52',
                  'key' => 'accounts_city',
                  'name' => 'city',
                  'type' => 'text',
                  'label' => 'modules.accounts.fields.city',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d23-719d-8e44-7dec9e463640',
                  'dropdown_list_id' => NULL,
                ),
                6 =>
                array(
                  'id' => '92ddbac5-d8f5-4542-858c-40ebee57240e',
                  'key' => 'accounts_phone',
                  'name' => 'phone',
                  'type' => 'text',
                  'label' => 'modules.accounts.fields.phone',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d23-719d-8e44-7dec9e463640',
                  'dropdown_list_id' => NULL,
                ),
                7 =>
                array(
                  'id' => '9444e745-7130-4157-a14e-d7e8121c62e6',
                  'key' => 'accounts_website',
                  'name' => 'website',
                  'type' => 'text',
                  'label' => 'modules.accounts.fields.website',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d23-719d-8e44-7dec9e463640',
                  'dropdown_list_id' => NULL,
                ),
                8 =>
                array(
                  'id' => 'a749709c-20eb-4673-9885-7155586273c4',
                  'key' => 'accounts_country',
                  'name' => 'country',
                  'type' => 'text',
                  'label' => 'modules.accounts.fields.country',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d23-719d-8e44-7dec9e463640',
                  'dropdown_list_id' => NULL,
                ),
                9 =>
                array(
                  'id' => 'becc86c4-914e-4f76-94f2-0bf36acd1cd5',
                  'key' => 'accounts_name',
                  'name' => 'name',
                  'type' => 'text',
                  'label' => 'modules.accounts.fields.name',
                  'readonly' => false,
                  'required' => true,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d23-719d-8e44-7dec9e463640',
                  'dropdown_list_id' => NULL,
                ),
                10 =>
                array(
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
          array(
            'name' => 'cases_emails',
            'type' => 'one-to-many',
            'label' => 'relationships.cases_emails',
            'panelHeader' =>
            array(
              0 =>
              array(
                'name' => 'subject',
                'type' => 'text',
                'label' => 'modules.emails.fields.subject',
              ),
              1 =>
              array(
                'name' => 'status',
                'type' => 'select',
                'label' => 'modules.emails.fields.status',
              ),
              2 =>
              array(
                'name' => 'to',
                'type' => 'email',
                'label' => 'modules.emails.fields.to',
              ),
            ),
            'relationship' =>
            array(
              'id' => '67b0eccc-cf33-4760-aaeb-dfe7bd8398ec',
              'name' => 'cases_emails',
              'role' => 'parent',
              'side' => 'left',
              'label' => 'relationships.cases_emails',
              'created_at' => '2026-02-26 12:51:55',
              'join_table' => 'relationship_links',
              'left_class' => 'App\\Models\\Modules\\SupportCase',
              'other_side' => 'right',
              'updated_at' => '2026-02-26 12:51:55',
              'left_module' => 'cases',
              'right_class' => 'App\\Models\\Modules\\Email',
              'current_side' => 'left',
              'related_slug' => 'emails',
              'right_module' => 'emails',
              'related_class' => 'App\\Models\\Modules\\Email',
              'other_id_field' => 'right_id',
              'related_fields' =>
              array(
                0 =>
                array(
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
                array(
                  'id' => '26713f41-0337-4190-9c51-8ff9bbb31391',
                  'key' => 'emails_status',
                  'name' => 'status',
                  'type' => 'select',
                  'label' => 'modules.emails.fields.status',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d32-7255-82f6-59342a40af4b',
                  'dropdown_list_id' => NULL,
                ),
                2 =>
                array(
                  'id' => '51621b8a-69d8-42b1-a1c7-6f1ecebe231d',
                  'key' => 'emails_mailable_class',
                  'name' => 'mailable_class',
                  'type' => 'text',
                  'label' => 'modules.emails.fields.mailable_class',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d32-7255-82f6-59342a40af4b',
                  'dropdown_list_id' => NULL,
                ),
                3 =>
                array(
                  'id' => '73c68003-dec2-4d1a-8cd1-caa3a9127906',
                  'key' => 'emails_subject',
                  'name' => 'subject',
                  'type' => 'text',
                  'label' => 'modules.emails.fields.subject',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d32-7255-82f6-59342a40af4b',
                  'dropdown_list_id' => NULL,
                ),
                4 =>
                array(
                  'id' => '97d640ba-ac82-4081-836d-b9fcb2c1bd38',
                  'key' => 'emails_related_id',
                  'name' => 'related_id',
                  'type' => 'text',
                  'label' => 'modules.emails.fields.related_id',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d32-7255-82f6-59342a40af4b',
                  'dropdown_list_id' => NULL,
                ),
                5 =>
                array(
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
                array(
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
                array(
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
      array(
        'layout' =>
        array(
          0 =>
          array(
            'name' => 'contacts_cases',
            'type' => 'one-to-many',
            'label' => 'relationships.contacts_cases',
            'panelHeader' =>
            array(
              0 =>
              array(
                'name' => 'name',
                'type' => 'text',
                'label' => 'modules.contacts.fields.name',
              ),
              1 =>
              array(
                'name' => 'position',
                'type' => 'text',
                'label' => 'modules.contacts.fields.position',
              ),
              2 =>
              array(
                'name' => 'phone',
                'type' => 'text',
                'label' => 'modules.contacts.fields.phone',
              ),
              3 =>
              array(
                'name' => 'email',
                'type' => 'email',
                'label' => 'modules.contacts.fields.email',
              ),
            ),
            'relationship' =>
            array(
              'id' => '8534a5a5-726c-4729-b405-83ac6bc66b82',
              'name' => 'contacts_cases',
              'role' => 'child',
              'side' => 'right',
              'label' => 'relationships.contacts_cases',
              'created_at' => '2026-02-26 12:51:55',
              'join_table' => 'relationship_links',
              'left_class' => 'App\\Models\\Modules\\Contact',
              'other_side' => 'left',
              'updated_at' => '2026-02-26 12:51:55',
              'left_module' => 'contacts',
              'right_class' => 'App\\Models\\Modules\\SupportCase',
              'current_side' => 'right',
              'related_slug' => 'contacts',
              'right_module' => 'cases',
              'related_class' => 'App\\Models\\Modules\\Contact',
              'other_id_field' => 'left_id',
              'related_fields' =>
              array(
                0 =>
                array(
                  'id' => '21c067b3-9498-4139-8211-f486647c38aa',
                  'key' => 'contacts_account_id',
                  'name' => 'account_id',
                  'type' => 'relationship',
                  'label' => 'modules.contacts.fields.account_id',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d25-7136-a50d-5fa6fdc46656',
                  'dropdown_list_id' => NULL,
                ),
                1 =>
                array(
                  'id' => '2e94cba5-b0dd-4959-b4a9-3a3863d4c915',
                  'key' => 'contacts_phone',
                  'name' => 'phone',
                  'type' => 'text',
                  'label' => 'modules.contacts.fields.phone',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d25-7136-a50d-5fa6fdc46656',
                  'dropdown_list_id' => NULL,
                ),
                2 =>
                array(
                  'id' => '3639da7c-4682-40e3-b432-721c03df6bad',
                  'key' => 'contacts_description',
                  'name' => 'description',
                  'type' => 'longtext',
                  'label' => 'modules.contacts.fields.description',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d25-7136-a50d-5fa6fdc46656',
                  'dropdown_list_id' => NULL,
                ),
                3 =>
                array(
                  'id' => '36a49d40-b37f-4340-9d78-397ee7d9a64b',
                  'key' => 'contacts_email',
                  'name' => 'email',
                  'type' => 'email',
                  'label' => 'modules.contacts.fields.email',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d25-7136-a50d-5fa6fdc46656',
                  'dropdown_list_id' => NULL,
                ),
                4 =>
                array(
                  'id' => '475ea507-37cf-4d00-ab16-8504038a7367',
                  'key' => 'contacts_updated_at',
                  'name' => 'updated_at',
                  'type' => 'datetime',
                  'label' => 'modules.contacts.fields.updated_at',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d25-7136-a50d-5fa6fdc46656',
                  'dropdown_list_id' => NULL,
                ),
                5 =>
                array(
                  'id' => '8cd8ce53-f0b0-42e0-9b08-98cd0582c671',
                  'key' => 'contacts_created_at',
                  'name' => 'created_at',
                  'type' => 'datetime',
                  'label' => 'modules.contacts.fields.created_at',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d25-7136-a50d-5fa6fdc46656',
                  'dropdown_list_id' => NULL,
                ),
                6 =>
                array(
                  'id' => '8e75d601-1663-4e88-a2cf-8fed8cdcb7e6',
                  'key' => 'contacts_first_name',
                  'name' => 'first_name',
                  'type' => 'text',
                  'label' => 'modules.contacts.fields.first_name',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d25-7136-a50d-5fa6fdc46656',
                  'dropdown_list_id' => NULL,
                ),
                7 =>
                array(
                  'id' => 'a581c759-819d-4ade-97c0-65fa1537b078',
                  'key' => 'contacts_name',
                  'name' => 'name',
                  'type' => 'text',
                  'label' => 'modules.contacts.fields.name',
                  'readonly' => false,
                  'required' => true,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d25-7136-a50d-5fa6fdc46656',
                  'dropdown_list_id' => NULL,
                ),
                8 =>
                array(
                  'id' => 'ab103add-6343-4bb5-904c-04ec3447b202',
                  'key' => 'contacts_position',
                  'name' => 'position',
                  'type' => 'text',
                  'label' => 'modules.contacts.fields.position',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d25-7136-a50d-5fa6fdc46656',
                  'dropdown_list_id' => NULL,
                ),
                9 =>
                array(
                  'id' => 'ab4e2c1f-072f-4afd-8280-784fd83ad6fd',
                  'key' => 'contacts_notes',
                  'name' => 'notes',
                  'type' => 'longtext',
                  'label' => 'modules.contacts.fields.notes',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d25-7136-a50d-5fa6fdc46656',
                  'dropdown_list_id' => NULL,
                ),
                10 =>
                array(
                  'id' => 'cbcaa446-3d7e-4111-8e15-d8f3fcaa90e9',
                  'key' => 'contacts_last_name',
                  'name' => 'last_name',
                  'type' => 'text',
                  'label' => 'modules.contacts.fields.last_name',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d25-7136-a50d-5fa6fdc46656',
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
    ),
  ),
  'linking-panel' =>
  array(
    'columns' =>
    array(
      0 =>
      array(
        'name' => 'name',
        'type' => 'text',
        'label' => 'modules.defaults.name',
        'sortable' => true,
      ),
      1 =>
      array(
        'key' => 'cases_opened_at',
        'name' => 'opened_at',
        'type' => 'datetime',
        'label' => 'modules.cases.fields.opened_at',
        'readonly' => false,
        'required' => false,
        'sortable' => false,
        'dropdown_list' => NULL,
      ),
      2 =>
      array(
        'key' => 'cases_closed_at',
        'name' => 'closed_at',
        'type' => 'datetime',
        'label' => 'modules.cases.fields.closed_at',
        'readonly' => false,
        'required' => false,
        'sortable' => false,
        'dropdown_list' => NULL,
      ),
      3 =>
      array(
        'key' => 'cases_status',
        'name' => 'status',
        'type' => 'select',
        'label' => 'modules.cases.fields.status',
        'readonly' => false,
        'required' => false,
        'sortable' => false,
        'dropdown_list' => NULL,
      ),
      4 =>
      array(
        'key' => 'cases_priority',
        'name' => 'priority',
        'type' => 'select',
        'label' => 'modules.cases.fields.priority',
        'readonly' => false,
        'required' => false,
        'sortable' => false,
        'dropdown_list' => NULL,
      ),
    ),
  ),
  'record' =>
  array(
    'sections' =>
    array(
      0 =>
      array(
        'name' => 'Details',
        'layout' =>
        array(
          0 =>
          array(
            'name' => 'name',
            'type' => 'text',
            'label' => 'modules.defaults.name',
            'readonly' => false,
            'required' => true,
            'sortable' => true,
          ),
          1 =>
          array(
            'name' => 'subject',
            'type' => 'text',
            'label' => 'modules.cases.fields.subject',
            'readonly' => false,
            'required' => false,
          ),
          2 =>
          array(
            'name' => 'status',
            'type' => 'select',
            'label' => 'modules.cases.fields.status',
            'readonly' => false,
            'required' => false,
          ),
          3 =>
          array(
            'name' => 'priority',
            'type' => 'select',
            'label' => 'modules.cases.fields.priority',
            'readonly' => false,
            'required' => false,
          ),
          4 =>
          array(
            'name' => 'description',
            'type' => 'longtext',
            'label' => 'modules.cases.fields.description',
            'readonly' => false,
            'required' => false,
          ),
        ),
      ),
      1 =>
      array(
        'name' => 'Dates',
        'layout' =>
        array(
          0 =>
          array(
            'name' => 'opened_at',
            'type' => 'datetime',
            'label' => 'modules.cases.fields.opened_at',
            'readonly' => false,
            'required' => false,
          ),
          1 =>
          array(
            'name' => 'closed_at',
            'type' => 'datetime',
            'label' => 'modules.cases.fields.closed_at',
            'readonly' => false,
            'required' => false,
          ),
          2 =>
          array(
            'name' => 'created_at',
            'type' => 'datetime',
            'label' => 'modules.cases.fields.created_at',
            'readonly' => false,
            'required' => false,
          ),
          3 =>
          array(
            'name' => 'updated_at',
            'type' => 'datetime',
            'label' => 'modules.cases.fields.updated_at',
            'readonly' => false,
            'required' => false,
          ),
        ),
      ),
    ),
  ),
);
