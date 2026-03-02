<?php

return array(
  'list' =>
  array(
    'columns' =>
    array(
      0 =>
      array(
        'key' => 'emails_subject',
        'name' => 'subject',
        'type' => 'text',
        'label' => 'modules.emails.fields.subject',
        'readonly' => false,
        'required' => false,
        'sortable' => true,
        'dropdown_list' => NULL,
      ),
      1 =>
      array(
        'key' => 'emails_to',
        'name' => 'to',
        'type' => 'email',
        'label' => 'modules.emails.fields.to',
        'readonly' => false,
        'required' => false,
        'sortable' => true,
        'dropdown_list' => NULL,
      ),
      2 =>
      array(
        'key' => 'emails_status',
        'name' => 'status',
        'type' => 'select',
        'label' => 'modules.emails.fields.status',
        'readonly' => false,
        'required' => false,
        'sortable' => true,
        'dropdown_list' => NULL,
      ),
      3 =>
      array(
        'name' => 'created_at',
        'type' => 'datetime',
        'label' => 'modules.defaults.created_at',
        'sortable' => true,
      ),
      4 =>
      array(
        'name' => 'updated_at',
        'type' => 'datetime',
        'label' => 'modules.defaults.updated_at',
        'sortable' => true,
      ),
    ),
  ),
  'record' =>
  array(
    'sections' =>
    array(
      0 =>
      array(
        'name' => 'Card',
        'layout' =>
        array(
          0 =>
          array(
            'name' => 'subject',
            'type' => 'text',
            'label' => 'modules.emails.fields.subject',
            'readonly' => false,
            'required' => false,
          ),
          1 =>
          array(
            'name' => 'to',
            'type' => 'email',
            'label' => 'modules.emails.fields.to',
            'readonly' => false,
            'required' => false,
          ),
          2 =>
          array(
            'name' => 'description',
            'type' => 'longText',
            'label' => 'modules.defaults.description',
            'readonly' => false,
            'required' => true,
            'sortable' => true,
          ),
          3 =>
          array(
            'name' => 'status',
            'type' => 'select',
            'label' => 'modules.emails.fields.status',
            'readonly' => false,
            'required' => false,
          ),
          4 =>
          array(
            'name' => 'created_at',
            'type' => 'datetime',
            'label' => 'modules.defaults.created_at',
            'readonly' => true,
            'required' => true,
            'sortable' => true,
          ),
          5 =>
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
            'name' => 'contacts_emails',
            'type' => 'one-to-many',
            'label' => 'relationships.contacts_emails',
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
                'name' => 'created_at',
                'type' => 'datetime',
                'label' => 'modules.contacts.fields.created_at',
              ),
              2 =>
              array(
                'name' => 'updated_at',
                'type' => 'datetime',
                'label' => 'modules.contacts.fields.updated_at',
              ),
            ),
            'relationship' =>
            array(
              'id' => '0bcfaf93-5292-4ead-ac4f-b0a939180828',
              'name' => 'contacts_emails',
              'role' => 'child',
              'side' => 'right',
              'label' => 'relationships.contacts_emails',
              'created_at' => '2026-02-26 12:51:55',
              'join_table' => 'relationship_links',
              'left_class' => 'App\\Models\\Modules\\Contact',
              'other_side' => 'left',
              'updated_at' => '2026-02-26 12:51:55',
              'left_module' => 'contacts',
              'right_class' => 'App\\Models\\Modules\\Email',
              'current_side' => 'right',
              'related_slug' => 'contacts',
              'right_module' => 'emails',
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
          1 =>
          array(
            'name' => 'cases_emails',
            'type' => 'one-to-many',
            'label' => 'relationships.cases_emails',
            'panelHeader' =>
            array(
              0 =>
              array(
                'name' => 'name',
                'type' => 'text',
                'label' => 'modules.cases.fields.name',
              ),
              1 =>
              array(
                'name' => 'created_at',
                'type' => 'datetime',
                'label' => 'modules.cases.fields.created_at',
              ),
              2 =>
              array(
                'name' => 'updated_at',
                'type' => 'datetime',
                'label' => 'modules.cases.fields.updated_at',
              ),
            ),
            'relationship' =>
            array(
              'id' => '67b0eccc-cf33-4760-aaeb-dfe7bd8398ec',
              'name' => 'cases_emails',
              'role' => 'child',
              'side' => 'right',
              'label' => 'relationships.cases_emails',
              'created_at' => '2026-02-26 12:51:55',
              'join_table' => 'relationship_links',
              'left_class' => 'App\\Models\\Modules\\SupportCase',
              'other_side' => 'left',
              'updated_at' => '2026-02-26 12:51:55',
              'left_module' => 'cases',
              'right_class' => 'App\\Models\\Modules\\Email',
              'current_side' => 'right',
              'related_slug' => 'cases',
              'right_module' => 'emails',
              'related_class' => 'App\\Models\\Modules\\SupportCase',
              'other_id_field' => 'left_id',
              'related_fields' =>
              array(
                0 =>
                array(
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
                array(
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
                array(
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
                array(
                  'id' => '49098d13-bfee-41ef-b7ec-03e677f07cb0',
                  'key' => 'cases_name',
                  'name' => 'name',
                  'type' => 'text',
                  'label' => 'modules.cases.fields.name',
                  'readonly' => false,
                  'required' => true,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d30-709f-93bb-af1934744060',
                  'dropdown_list_id' => NULL,
                ),
                4 =>
                array(
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
                array(
                  'id' => '8aaa72e4-6d8b-4c26-84d7-0388e6881c0c',
                  'key' => 'cases_status',
                  'name' => 'status',
                  'type' => 'select',
                  'label' => 'modules.cases.fields.status',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d30-709f-93bb-af1934744060',
                  'dropdown_list_id' => NULL,
                ),
                6 =>
                array(
                  'id' => '8b20a4aa-056c-4f17-844a-b64a08899b78',
                  'key' => 'cases_priority',
                  'name' => 'priority',
                  'type' => 'select',
                  'label' => 'modules.cases.fields.priority',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d30-709f-93bb-af1934744060',
                  'dropdown_list_id' => NULL,
                ),
                7 =>
                array(
                  'id' => '8b803c3d-0c70-4d60-afaa-d813b8669f44',
                  'key' => 'cases_subject',
                  'name' => 'subject',
                  'type' => 'text',
                  'label' => 'modules.cases.fields.subject',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d30-709f-93bb-af1934744060',
                  'dropdown_list_id' => NULL,
                ),
                8 =>
                array(
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
                array(
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
                array(
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
              'current_id_field' => 'right_id',
              'right_module_key' => NULL,
              'current_module_id' => NULL,
              'relationship_type' => 'one-to-many',
            ),
          ),
          2 =>
          array(
            'name' => 'inquiries_emails',
            'type' => 'one-to-many',
            'label' => 'relationships.inquiries_emails',
            'panelHeader' =>
            array(
              0 =>
              array(
                'name' => 'name',
                'type' => 'text',
                'label' => 'modules.inquiries.fields.name',
              ),
              1 =>
              array(
                'name' => 'created_at',
                'type' => 'datetime',
                'label' => 'modules.inquiries.fields.created_at',
              ),
              2 =>
              array(
                'name' => 'updated_at',
                'type' => 'datetime',
                'label' => 'modules.inquiries.fields.updated_at',
              ),
            ),
            'relationship' =>
            array(
              'id' => 'a475f480-db07-4377-b604-83c324d975b6',
              'name' => 'inquiries_emails',
              'role' => 'child',
              'side' => 'right',
              'label' => 'relationships.inquiries_emails',
              'created_at' => '2026-02-26 12:51:55',
              'join_table' => 'relationship_links',
              'left_class' => 'App\\Models\\Modules\\ContactMessage',
              'other_side' => 'left',
              'updated_at' => '2026-02-26 12:51:55',
              'left_module' => 'inquiries',
              'right_class' => 'App\\Models\\Modules\\Email',
              'current_side' => 'right',
              'related_slug' => 'inquiries',
              'right_module' => 'emails',
              'related_class' => 'App\\Models\\Modules\\ContactMessage',
              'other_id_field' => 'left_id',
              'related_fields' =>
              array(
                0 =>
                array(
                  'id' => '10bdf2cc-8631-4235-828a-1b20be13062f',
                  'key' => 'inquiries_ip',
                  'name' => 'ip',
                  'type' => 'text',
                  'label' => 'modules.inquiries.fields.ip',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d34-70c0-8606-f3728098ad59',
                  'dropdown_list_id' => NULL,
                ),
                1 =>
                array(
                  'id' => '332d4add-f424-4ca6-a652-a71b1ba361ec',
                  'key' => 'inquiries_email',
                  'name' => 'email',
                  'type' => 'email',
                  'label' => 'modules.inquiries.fields.email',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d34-70c0-8606-f3728098ad59',
                  'dropdown_list_id' => NULL,
                ),
                2 =>
                array(
                  'id' => '474f2c8e-03e7-45df-93e9-0c33f2d1e730',
                  'key' => 'inquiries_updated_at',
                  'name' => 'updated_at',
                  'type' => 'datetime',
                  'label' => 'modules.inquiries.fields.updated_at',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d34-70c0-8606-f3728098ad59',
                  'dropdown_list_id' => NULL,
                ),
                3 =>
                array(
                  'id' => '54939f6f-23ab-415c-845e-cdbad9a5b12b',
                  'key' => 'inquiries_name',
                  'name' => 'name',
                  'type' => 'text',
                  'label' => 'modules.inquiries.fields.name',
                  'readonly' => false,
                  'required' => true,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d34-70c0-8606-f3728098ad59',
                  'dropdown_list_id' => NULL,
                ),
                4 =>
                array(
                  'id' => '56498acd-9b6c-4f38-992c-fbd0ba870323',
                  'key' => 'inquiries_user_agent',
                  'name' => 'user_agent',
                  'type' => 'longtext',
                  'label' => 'modules.inquiries.fields.user_agent',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d34-70c0-8606-f3728098ad59',
                  'dropdown_list_id' => NULL,
                ),
                5 =>
                array(
                  'id' => '5b0fca0b-7e57-4d23-9270-081108e7fc3e',
                  'key' => 'inquiries_message',
                  'name' => 'message',
                  'type' => 'longtext',
                  'label' => 'modules.inquiries.fields.message',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d34-70c0-8606-f3728098ad59',
                  'dropdown_list_id' => NULL,
                ),
                6 =>
                array(
                  'id' => '5f1c2f5e-4951-4022-be87-440c7b90cc8d',
                  'key' => 'inquiries_status',
                  'name' => 'status',
                  'type' => 'select',
                  'label' => 'modules.inquiries.fields.status',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d34-70c0-8606-f3728098ad59',
                  'dropdown_list_id' => NULL,
                ),
                7 =>
                array(
                  'id' => 'a2e15f17-15cd-4a32-bba1-46b037ff81c7',
                  'key' => 'inquiries_created_at',
                  'name' => 'created_at',
                  'type' => 'datetime',
                  'label' => 'modules.inquiries.fields.created_at',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d34-70c0-8606-f3728098ad59',
                  'dropdown_list_id' => NULL,
                ),
                8 =>
                array(
                  'id' => 'b87faa6f-c873-4c63-a0d8-123a65bda132',
                  'key' => 'inquiries_phone',
                  'name' => 'phone',
                  'type' => 'text',
                  'label' => 'modules.inquiries.fields.phone',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d34-70c0-8606-f3728098ad59',
                  'dropdown_list_id' => NULL,
                ),
                9 =>
                array(
                  'id' => 'ebd67757-35ad-4c1f-b621-4ec66df74278',
                  'key' => 'inquiries_description',
                  'name' => 'description',
                  'type' => 'longtext',
                  'label' => 'modules.inquiries.fields.description',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d34-70c0-8606-f3728098ad59',
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
      array(
        'layout' =>
        array(
          0 =>
          array(
            'name' => 'leads_emails',
            'type' => 'one-to-many',
            'label' => 'relationships.leads_emails',
            'panelHeader' =>
            array(
              0 =>
              array(
                'name' => 'name',
                'type' => 'text',
                'label' => 'modules.leads.fields.name',
              ),
              1 =>
              array(
                'name' => 'created_at',
                'type' => 'datetime',
                'label' => 'modules.leads.fields.created_at',
              ),
              2 =>
              array(
                'name' => 'updated_at',
                'type' => 'datetime',
                'label' => 'modules.leads.fields.updated_at',
              ),
            ),
            'relationship' =>
            array(
              'id' => '0d0f63a8-028a-4661-93ac-696ed79c2a31',
              'name' => 'leads_emails',
              'role' => 'child',
              'side' => 'right',
              'label' => 'relationships.leads_emails',
              'created_at' => '2026-02-26 12:51:55',
              'join_table' => 'relationship_links',
              'left_class' => 'App\\Models\\Modules\\Lead',
              'other_side' => 'left',
              'updated_at' => '2026-02-26 12:51:55',
              'left_module' => 'leads',
              'right_class' => 'App\\Models\\Modules\\Email',
              'current_side' => 'right',
              'related_slug' => 'leads',
              'right_module' => 'emails',
              'related_class' => 'App\\Models\\Modules\\Lead',
              'other_id_field' => 'left_id',
              'related_fields' =>
              array(
                0 =>
                array(
                  'id' => '17b914ee-e060-4be4-950b-97ae6f586ac6',
                  'key' => 'leads_first_name',
                  'name' => 'first_name',
                  'type' => 'text',
                  'label' => 'modules.leads.fields.first_name',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d1e-719b-bcf0-8a132c1108a4',
                  'dropdown_list_id' => NULL,
                ),
                1 =>
                array(
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
                array(
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
                array(
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
                array(
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
                array(
                  'id' => '5d3657c4-d698-4b71-b171-bc87c56f96d2',
                  'key' => 'leads_company',
                  'name' => 'company',
                  'type' => 'text',
                  'label' => 'modules.leads.fields.company',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d1e-719b-bcf0-8a132c1108a4',
                  'dropdown_list_id' => NULL,
                ),
                6 =>
                array(
                  'id' => '735382c1-80d1-4b75-aa4d-12014e1a103a',
                  'key' => 'leads_phone',
                  'name' => 'phone',
                  'type' => 'text',
                  'label' => 'modules.leads.fields.phone',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d1e-719b-bcf0-8a132c1108a4',
                  'dropdown_list_id' => NULL,
                ),
                7 =>
                array(
                  'id' => '82c1a60b-b5b4-43cb-9d0a-4865ac6a7d00',
                  'key' => 'leads_zip',
                  'name' => 'zip',
                  'type' => 'text',
                  'label' => 'modules.leads.fields.zip',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d1e-719b-bcf0-8a132c1108a4',
                  'dropdown_list_id' => NULL,
                ),
                8 =>
                array(
                  'id' => '89ad5151-1c73-45b4-a1fb-d0e80c758946',
                  'key' => 'leads_city',
                  'name' => 'city',
                  'type' => 'text',
                  'label' => 'modules.leads.fields.city',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d1e-719b-bcf0-8a132c1108a4',
                  'dropdown_list_id' => NULL,
                ),
                9 =>
                array(
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
                array(
                  'id' => 'ee05478d-e921-45cd-a270-eaf4866fd274',
                  'key' => 'leads_name',
                  'name' => 'name',
                  'type' => 'text',
                  'label' => 'modules.leads.fields.name',
                  'readonly' => false,
                  'required' => true,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d1e-719b-bcf0-8a132c1108a4',
                  'dropdown_list_id' => NULL,
                ),
                11 =>
                array(
                  'id' => 'f1e2dcce-8e8d-4cfb-92b5-5d4cb6470b0b',
                  'key' => 'leads_last_name',
                  'name' => 'last_name',
                  'type' => 'text',
                  'label' => 'modules.leads.fields.last_name',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d1e-719b-bcf0-8a132c1108a4',
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
            'name' => 'accounts_emails',
            'type' => 'one-to-many',
            'label' => 'relationships.accounts_emails',
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
                'name' => 'created_at',
                'type' => 'datetime',
                'label' => 'modules.accounts.fields.created_at',
              ),
              2 =>
              array(
                'name' => 'updated_at',
                'type' => 'datetime',
                'label' => 'modules.accounts.fields.updated_at',
              ),
            ),
            'relationship' =>
            array(
              'id' => '8ea06858-84e6-4fe8-873b-6ab31c92f152',
              'name' => 'accounts_emails',
              'role' => 'child',
              'side' => 'right',
              'label' => 'relationships.accounts_emails',
              'created_at' => '2026-02-26 12:51:55',
              'join_table' => 'relationship_links',
              'left_class' => 'App\\Models\\Modules\\Account',
              'other_side' => 'left',
              'updated_at' => '2026-02-26 12:51:55',
              'left_module' => 'accounts',
              'right_class' => 'App\\Models\\Modules\\Email',
              'current_side' => 'right',
              'related_slug' => 'accounts',
              'right_module' => 'emails',
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
        'key' => 'emails_subject',
        'name' => 'subject',
        'type' => 'text',
        'label' => 'modules.emails.fields.subject',
        'readonly' => false,
        'required' => false,
        'sortable' => true,
        'dropdown_list' => NULL,
      ),
      1 =>
      array(
        'key' => 'emails_status',
        'name' => 'status',
        'type' => 'select',
        'label' => 'modules.emails.fields.status',
        'readonly' => false,
        'required' => false,
        'sortable' => true,
        'dropdown_list' => NULL,
      ),
      2 =>
      array(
        'key' => 'emails_to',
        'name' => 'to',
        'type' => 'email',
        'label' => 'modules.emails.fields.to',
        'readonly' => false,
        'required' => false,
        'sortable' => false,
        'dropdown_list' => NULL,
      ),
      3 =>
      array(
        'name' => 'created_at',
        'type' => 'datetime',
        'label' => 'modules.defaults.created_at',
        'sortable' => true,
      ),
      4 =>
      array(
        'name' => 'updated_at',
        'type' => 'datetime',
        'label' => 'modules.defaults.updated_at',
        'sortable' => true,
      ),
    ),
  ),
);
