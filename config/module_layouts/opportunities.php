<?php

return array(
  'list' =>
  array(
    'columns' =>
    array(
      0 =>
      array(
        'key' => 'opportunities_name',
        'name' => 'name',
        'type' => 'textfield',
        'label' => 'modules.opportunities.fields.name',
        'readonly' => false,
        'required' => true,
        'sortable' => true,
        'dropdown_list' => NULL,
      ),
      1 =>
      array(
        'key' => 'opportunities_account_id',
        'name' => 'account_id',
        'type' => 'relationship',
        'label' => 'modules.opportunities.fields.account_id',
        'readonly' => false,
        'required' => false,
        'sortable' => false,
        'dropdown_list' => NULL,
      ),
      2 =>
      array(
        'key' => 'opportunities_expected_close_date',
        'name' => 'expected_close_date',
        'type' => 'date',
        'label' => 'modules.opportunities.fields.expected_close_date',
        'readonly' => false,
        'required' => false,
        'sortable' => false,
        'dropdown_list' => NULL,
      ),
      3 =>
      array(
        'key' => 'opportunities_type',
        'name' => 'type',
        'type' => 'dropdown',
        'label' => 'modules.opportunities.fields.type',
        'readonly' => false,
        'required' => false,
        'sortable' => false,
        'dropdown_list' => NULL,
      ),
      4 =>
      array(
        'key' => 'opportunities_amount',
        'name' => 'amount',
        'type' => 'textfield',
        'label' => 'modules.opportunities.fields.amount',
        'readonly' => false,
        'required' => false,
        'sortable' => false,
        'dropdown_list' => NULL,
      ),
      5 =>
      array(
        'key' => 'opportunities_sales_stage',
        'name' => 'sales_stage',
        'type' => 'dropdown',
        'label' => 'modules.opportunities.fields.sales_stage',
        'readonly' => false,
        'required' => false,
        'sortable' => false,
        'dropdown_list' => NULL,
      ),
      6 =>
      array(
        'key' => 'opportunities_probability',
        'name' => 'probability',
        'type' => 'number',
        'label' => 'modules.opportunities.fields.probability',
        'readonly' => false,
        'required' => false,
        'sortable' => false,
        'dropdown_list' => NULL,
      ),
      7 =>
      array(
        'key' => 'opportunities_updated_at',
        'name' => 'updated_at',
        'type' => 'datetime',
        'label' => 'modules.opportunities.fields.updated_at',
        'readonly' => false,
        'required' => false,
        'sortable' => false,
        'dropdown_list' => NULL,
      ),
      8 =>
      array(
        'key' => 'opportunities_created_at',
        'name' => 'created_at',
        'type' => 'datetime',
        'label' => 'modules.opportunities.fields.created_at',
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
        'name' => 'General',
        'layout' =>
        array(
          0 =>
          array(
            'name' => 'name',
            'type' => 'textfield',
            'label' => 'modules.defaults.name',
            'readonly' => false,
            'required' => true,
            'sortable' => true,
          ),
          1 =>
          array(
            'name' => 'account_id',
            'type' => 'relationship',
            'label' => 'modules.opportunities.fields.account_id',
            'readonly' => false,
            'required' => false,
          ),
          2 =>
          array(
            'name' => 'sales_stage',
            'type' => 'dropdown',
            'label' => 'modules.opportunities.fields.sales_stage',
            'readonly' => false,
            'required' => false,
          ),
          3 =>
          array(
            'name' => 'type',
            'type' => 'dropdown',
            'label' => 'modules.opportunities.fields.type',
            'readonly' => false,
            'required' => false,
          ),
          4 =>
          array(
            'name' => 'assigned_user_id',
            'type' => 'relationship',
            'label' => 'modules.opportunities.fields.assigned_user_id',
            'readonly' => false,
            'required' => false,
          ),
        ),
      ),
      1 =>
      array(
        'name' => 'Financial',
        'layout' =>
        array(
          0 =>
          array(
            'name' => 'amount',
            'type' => 'textfield',
            'label' => 'modules.opportunities.fields.amount',
            'readonly' => false,
            'required' => false,
          ),
          1 =>
          array(
            'name' => 'currency',
            'type' => 'textfield',
            'label' => 'modules.opportunities.fields.currency',
            'readonly' => false,
            'required' => false,
          ),
          2 =>
          array(
            'name' => 'probability',
            'type' => 'number',
            'label' => 'modules.opportunities.fields.probability',
            'readonly' => false,
            'required' => false,
          ),
        ),
      ),
      2 =>
      array(
        'name' => 'Timeline',
        'layout' =>
        array(
          0 =>
          array(
            'name' => 'expected_close_date',
            'type' => 'date',
            'label' => 'modules.opportunities.fields.expected_close_date',
            'readonly' => false,
            'required' => false,
          ),
        ),
      ),
      3 =>
      array(
        'name' => 'Details',
        'layout' =>
        array(
          0 =>
          array(
            'name' => 'description',
            'type' => 'longText',
            'label' => 'modules.defaults.description',
            'readonly' => false,
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
          2 =>
          array(
            'name' => 'created_at',
            'type' => 'datetime',
            'label' => 'modules.defaults.created_at',
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
            'name' => 'accounts_opportunities',
            'type' => 'one-to-many',
            'label' => 'relationships.accounts_opportunities',
            'panelHeader' =>
            array(
              0 =>
              array(
                'name' => 'name',
                'type' => 'textfield',
                'label' => 'modules.accounts.fields.name',
              ),
              1 =>
              array(
                'name' => 'phone',
                'type' => 'textfield',
                'label' => 'modules.accounts.fields.phone',
              ),
              2 =>
              array(
                'name' => 'website',
                'type' => 'textfield',
                'label' => 'modules.accounts.fields.website',
              ),
              3 =>
              array(
                'name' => 'email',
                'type' => 'email',
                'label' => 'modules.accounts.fields.email',
              ),
            ),
            'relationship' =>
            array(
              'id' => 'f22ce995-5703-4696-b411-5605cdf776e9',
              'name' => 'accounts_opportunities',
              'role' => 'child',
              'side' => 'right',
              'label' => 'relationships.accounts_opportunities',
              'created_at' => '2026-02-26 12:51:55',
              'join_table' => 'relationship_links',
              'left_class' => 'App\\Models\\Modules\\Account',
              'other_side' => 'left',
              'updated_at' => '2026-02-26 12:51:55',
              'left_module' => 'accounts',
              'right_class' => 'App\\Models\\Modules\\Opportunity',
              'current_side' => 'right',
              'related_slug' => 'accounts',
              'right_module' => 'opportunities',
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
                  'type' => 'textfield',
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
                  'type' => 'textfield',
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
                  'type' => 'textfield',
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
                  'type' => 'textfield',
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
                  'type' => 'textfield',
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
            'name' => 'opportunities_contacts',
            'type' => 'many-to-many',
            'label' => 'relationships.opportunities_contacts',
            'panelHeader' =>
            array(
              0 =>
              array(
                'name' => 'name',
                'type' => 'textfield',
                'label' => 'modules.contacts.fields.name',
              ),
              1 =>
              array(
                'name' => 'email',
                'type' => 'email',
                'label' => 'modules.contacts.fields.email',
              ),
              2 =>
              array(
                'name' => 'phone',
                'type' => 'textfield',
                'label' => 'modules.contacts.fields.phone',
              ),
              3 =>
              array(
                'name' => 'position',
                'type' => 'textfield',
                'label' => 'modules.contacts.fields.position',
              ),
              4 =>
              array(
                'name' => 'created_at',
                'type' => 'datetime',
                'label' => 'modules.contacts.fields.created_at',
              ),
            ),
            'relationship' =>
            array(
              'id' => '0d39eb09-4497-46d8-856f-f31752c9c90d',
              'name' => 'opportunities_contacts',
              'role' => 'related',
              'side' => 'left',
              'label' => 'relationships.opportunities_contacts',
              'created_at' => '2026-02-26 12:51:55',
              'join_table' => 'relationship_links',
              'left_class' => 'App\\Models\\Modules\\Opportunity',
              'other_side' => 'right',
              'updated_at' => '2026-02-26 12:51:55',
              'left_module' => 'opportunities',
              'right_class' => 'App\\Models\\Modules\\Contact',
              'current_side' => 'left',
              'related_slug' => 'contacts',
              'right_module' => 'contacts',
              'related_class' => 'App\\Models\\Modules\\Contact',
              'other_id_field' => 'right_id',
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
                  'type' => 'textfield',
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
                  'type' => 'textfield',
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
                  'type' => 'textfield',
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
                  'type' => 'textfield',
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
                  'type' => 'textfield',
                  'label' => 'modules.contacts.fields.last_name',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d25-7136-a50d-5fa6fdc46656',
                  'dropdown_list_id' => NULL,
                ),
              ),
              'left_module_key' => NULL,
              'current_id_field' => 'left_id',
              'right_module_key' => NULL,
              'current_module_id' => NULL,
              'relationship_type' => 'many-to-many',
            ),
          ),
          2 =>
          array(
            'name' => 'opportunities_quotes',
            'type' => 'one-to-many',
            'label' => 'relationships.opportunities_quotes',
            'panelHeader' =>
            array(
              0 =>
              array(
                'name' => 'name',
                'type' => 'textfield',
                'label' => 'modules.quotes.fields.name',
              ),
              1 =>
              array(
                'name' => 'valid_until',
                'type' => 'date',
                'label' => 'modules.quotes.fields.valid_until',
              ),
              2 =>
              array(
                'name' => 'subtotal',
                'type' => 'number',
                'label' => 'modules.quotes.fields.subtotal',
              ),
              3 =>
              array(
                'name' => 'tax',
                'type' => 'number',
                'label' => 'modules.quotes.fields.tax',
              ),
              4 =>
              array(
                'name' => 'total',
                'type' => 'number',
                'label' => 'modules.quotes.fields.total',
              ),
              5 =>
              array(
                'name' => 'number',
                'type' => 'number',
                'label' => 'modules.quotes.fields.number',
              ),
              6 =>
              array(
                'name' => 'status',
                'type' => 'dropdown',
                'label' => 'modules.quotes.fields.status',
              ),
            ),
            'relationship' =>
            array(
              'id' => '0d7865ed-a51e-4d4f-96d1-5174bee219a9',
              'name' => 'opportunities_quotes',
              'role' => 'parent',
              'side' => 'left',
              'label' => 'relationships.opportunities_quotes',
              'created_at' => '2026-02-26 12:51:55',
              'join_table' => 'relationship_links',
              'left_class' => 'App\\Models\\Modules\\Opportunity',
              'other_side' => 'right',
              'updated_at' => '2026-02-26 12:51:55',
              'left_module' => 'opportunities',
              'right_class' => 'App\\Models\\Modules\\Quote',
              'current_side' => 'left',
              'related_slug' => 'quotes',
              'right_module' => 'quotes',
              'related_class' => 'App\\Models\\Modules\\Quote',
              'other_id_field' => 'right_id',
              'related_fields' =>
              array(
                0 =>
                array(
                  'id' => '0655fe29-a857-4819-9359-4b014ba21218',
                  'key' => 'quotes_valid_until',
                  'name' => 'valid_until',
                  'type' => 'date',
                  'label' => 'modules.quotes.fields.valid_until',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d29-73fd-91b3-ceecaa419259',
                  'dropdown_list_id' => NULL,
                ),
                1 =>
                array(
                  'id' => '10aa8ce0-49f7-4b49-b930-1fe0d05a5db9',
                  'key' => 'quotes_currency',
                  'name' => 'currency',
                  'type' => 'dropdown',
                  'label' => 'modules.quotes.fields.currency',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d29-73fd-91b3-ceecaa419259',
                  'dropdown_list_id' => NULL,
                ),
                2 =>
                array(
                  'id' => '1a3f3488-accb-46ca-944e-fceba1cc7387',
                  'key' => 'quotes_tax',
                  'name' => 'tax',
                  'type' => 'number',
                  'label' => 'modules.quotes.fields.tax',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d29-73fd-91b3-ceecaa419259',
                  'dropdown_list_id' => NULL,
                ),
                3 =>
                array(
                  'id' => '6779d2a5-9e07-4115-afd0-e7a2ee4d33d9',
                  'key' => 'quotes_name',
                  'name' => 'name',
                  'type' => 'textfield',
                  'label' => 'modules.quotes.fields.name',
                  'readonly' => false,
                  'required' => true,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d29-73fd-91b3-ceecaa419259',
                  'dropdown_list_id' => NULL,
                ),
                4 =>
                array(
                  'id' => '6feecfa9-03be-4139-bb4f-62e68f125ea3',
                  'key' => 'quotes_created_at',
                  'name' => 'created_at',
                  'type' => 'datetime',
                  'label' => 'modules.quotes.fields.created_at',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d29-73fd-91b3-ceecaa419259',
                  'dropdown_list_id' => NULL,
                ),
                5 =>
                array(
                  'id' => '7062e749-110b-4605-bf0c-eb8b47cf0ef1',
                  'key' => 'quotes_account_id',
                  'name' => 'account_id',
                  'type' => 'relationship',
                  'label' => 'modules.quotes.fields.account_id',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d29-73fd-91b3-ceecaa419259',
                  'dropdown_list_id' => NULL,
                ),
                6 =>
                array(
                  'id' => '7d6cc4fa-3273-420c-9e87-55c3040baaed',
                  'key' => 'quotes_description',
                  'name' => 'description',
                  'type' => 'longtext',
                  'label' => 'modules.quotes.fields.description',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d29-73fd-91b3-ceecaa419259',
                  'dropdown_list_id' => NULL,
                ),
                7 =>
                array(
                  'id' => 'a63356fe-57bd-43ee-bdc1-8635a41f759b',
                  'key' => 'quotes_contact_id',
                  'name' => 'contact_id',
                  'type' => 'relationship',
                  'label' => 'modules.quotes.fields.contact_id',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d29-73fd-91b3-ceecaa419259',
                  'dropdown_list_id' => NULL,
                ),
                8 =>
                array(
                  'id' => 'b752cd89-9405-49fb-b3e3-db87a34726fd',
                  'key' => 'quotes_notes',
                  'name' => 'notes',
                  'type' => 'longtext',
                  'label' => 'modules.quotes.fields.notes',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d29-73fd-91b3-ceecaa419259',
                  'dropdown_list_id' => NULL,
                ),
                9 =>
                array(
                  'id' => 'b8071940-5e07-4303-ac32-36a17698a341',
                  'key' => 'quotes_subtotal',
                  'name' => 'subtotal',
                  'type' => 'number',
                  'label' => 'modules.quotes.fields.subtotal',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d29-73fd-91b3-ceecaa419259',
                  'dropdown_list_id' => NULL,
                ),
                10 =>
                array(
                  'id' => 'c3703723-529d-489f-ab64-8704d1d623fe',
                  'key' => 'quotes_updated_at',
                  'name' => 'updated_at',
                  'type' => 'datetime',
                  'label' => 'modules.quotes.fields.updated_at',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d29-73fd-91b3-ceecaa419259',
                  'dropdown_list_id' => NULL,
                ),
                11 =>
                array(
                  'id' => 'c98a33dd-d346-459a-ba15-19da554f4724',
                  'key' => 'quotes_number',
                  'name' => 'number',
                  'type' => 'number',
                  'label' => 'modules.quotes.fields.number',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d29-73fd-91b3-ceecaa419259',
                  'dropdown_list_id' => NULL,
                ),
                12 =>
                array(
                  'id' => 'd0a97b84-0749-474f-85d2-dece78660878',
                  'key' => 'quotes_status',
                  'name' => 'status',
                  'type' => 'dropdown',
                  'label' => 'modules.quotes.fields.status',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d29-73fd-91b3-ceecaa419259',
                  'dropdown_list_id' => NULL,
                ),
                13 =>
                array(
                  'id' => 'd4251e8d-59a4-4de7-ae56-cd3f380f6e8c',
                  'key' => 'quotes_total',
                  'name' => 'total',
                  'type' => 'number',
                  'label' => 'modules.quotes.fields.total',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d29-73fd-91b3-ceecaa419259',
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
            'name' => 'opportunities_products',
            'type' => 'many-to-many',
            'label' => 'relationships.opportunities_products',
            'panelHeader' =>
            array(
              0 =>
              array(
                'name' => 'name',
                'type' => 'textfield',
                'label' => 'modules.products.fields.name',
              ),
              1 =>
              array(
                'name' => 'is_active',
                'type' => 'checkbox',
                'label' => 'modules.products.fields.is_active',
              ),
              2 =>
              array(
                'name' => 'price',
                'type' => 'number',
                'label' => 'modules.products.fields.price',
              ),
              3 =>
              array(
                'name' => 'category',
                'type' => 'textfield',
                'label' => 'modules.products.fields.category',
              ),
              4 =>
              array(
                'name' => 'sku',
                'type' => 'textfield',
                'label' => 'modules.products.fields.sku',
              ),
            ),
            'relationship' =>
            array(
              'id' => '76a15616-9c0c-4725-8964-9a2ece25ca60',
              'name' => 'opportunities_products',
              'role' => 'related',
              'side' => 'left',
              'label' => 'relationships.opportunities_products',
              'created_at' => '2026-02-26 12:51:55',
              'join_table' => 'relationship_links',
              'left_class' => 'App\\Models\\Modules\\Opportunity',
              'other_side' => 'right',
              'updated_at' => '2026-02-26 12:51:55',
              'left_module' => 'opportunities',
              'right_class' => 'App\\Models\\Modules\\Product',
              'current_side' => 'left',
              'related_slug' => 'products',
              'right_module' => 'products',
              'related_class' => 'App\\Models\\Modules\\Product',
              'other_id_field' => 'right_id',
              'related_fields' =>
              array(
                0 =>
                array(
                  'id' => '0f6c9459-b729-4988-b368-6fe963128cd3',
                  'key' => 'products_name',
                  'name' => 'name',
                  'type' => 'textfield',
                  'label' => 'modules.products.fields.name',
                  'readonly' => false,
                  'required' => true,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d2e-71dd-af8f-2f86c9c1958e',
                  'dropdown_list_id' => NULL,
                ),
                1 =>
                array(
                  'id' => '3e257cdb-2809-47a2-8579-d2382902d063',
                  'key' => 'products_price',
                  'name' => 'price',
                  'type' => 'number',
                  'label' => 'modules.products.fields.price',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d2e-71dd-af8f-2f86c9c1958e',
                  'dropdown_list_id' => NULL,
                ),
                2 =>
                array(
                  'id' => '4987279e-32a4-4d41-a1ef-030fe90273aa',
                  'key' => 'products_updated_at',
                  'name' => 'updated_at',
                  'type' => 'datetime',
                  'label' => 'modules.products.fields.updated_at',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d2e-71dd-af8f-2f86c9c1958e',
                  'dropdown_list_id' => NULL,
                ),
                3 =>
                array(
                  'id' => '5521451c-4164-4cb1-a62f-1b8aeeed7c80',
                  'key' => 'products_sku',
                  'name' => 'sku',
                  'type' => 'textfield',
                  'label' => 'modules.products.fields.sku',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d2e-71dd-af8f-2f86c9c1958e',
                  'dropdown_list_id' => NULL,
                ),
                4 =>
                array(
                  'id' => '58f0af4a-a5a0-4875-a4f2-b52e6f83afd5',
                  'key' => 'products_currency',
                  'name' => 'currency',
                  'type' => 'dropdown',
                  'label' => 'modules.products.fields.currency',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d2e-71dd-af8f-2f86c9c1958e',
                  'dropdown_list_id' => NULL,
                ),
                5 =>
                array(
                  'id' => '63c9c6ef-2477-47cd-bac1-dbbc15afb794',
                  'key' => 'products_description',
                  'name' => 'description',
                  'type' => 'longtext',
                  'label' => 'modules.products.fields.description',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d2e-71dd-af8f-2f86c9c1958e',
                  'dropdown_list_id' => NULL,
                ),
                6 =>
                array(
                  'id' => '6f24eddc-6b6e-4a40-9f5d-aed101e864d0',
                  'key' => 'products_category',
                  'name' => 'category',
                  'type' => 'textfield',
                  'label' => 'modules.products.fields.category',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d2e-71dd-af8f-2f86c9c1958e',
                  'dropdown_list_id' => NULL,
                ),
                7 =>
                array(
                  'id' => '7846187f-4ec5-449f-be7b-5c42ef1f8150',
                  'key' => 'products_is_active',
                  'name' => 'is_active',
                  'type' => 'checkbox',
                  'label' => 'modules.products.fields.is_active',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d2e-71dd-af8f-2f86c9c1958e',
                  'dropdown_list_id' => NULL,
                ),
                8 =>
                array(
                  'id' => 'ad265b33-86f0-4454-b0d9-83f8752e01c5',
                  'key' => 'products_created_at',
                  'name' => 'created_at',
                  'type' => 'datetime',
                  'label' => 'modules.products.fields.created_at',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d2e-71dd-af8f-2f86c9c1958e',
                  'dropdown_list_id' => NULL,
                ),
              ),
              'left_module_key' => NULL,
              'current_id_field' => 'left_id',
              'right_module_key' => NULL,
              'current_module_id' => NULL,
              'relationship_type' => 'many-to-many',
            ),
          ),
          1 =>
          array(
            'name' => 'opportunities_orders',
            'type' => 'one-to-many',
            'label' => 'relationships.opportunities_orders',
            'panelHeader' =>
            array(
              0 =>
              array(
                'name' => 'order_number',
                'type' => 'textfield',
                'label' => 'modules.orders.fields.order_number',
              ),
              1 =>
              array(
                'name' => 'status',
                'type' => 'dropdown',
                'label' => 'modules.orders.fields.status',
              ),
              2 =>
              array(
                'name' => 'total_amount',
                'type' => 'number',
                'label' => 'modules.orders.fields.total_amount',
              ),
              3 =>
              array(
                'name' => 'order_date',
                'type' => 'date',
                'label' => 'modules.orders.fields.order_date',
              ),
              4 =>
              array(
                'name' => 'due_date',
                'type' => 'date',
                'label' => 'modules.orders.fields.due_date',
              ),
            ),
            'relationship' =>
            array(
              'id' => 'b152ec23-5394-4f5e-b115-97c08e1fd307',
              'name' => 'opportunities_orders',
              'role' => 'parent',
              'side' => 'left',
              'label' => 'relationships.opportunities_orders',
              'created_at' => '2026-02-26 12:51:55',
              'join_table' => 'relationship_links',
              'left_class' => 'App\\Models\\Modules\\Opportunity',
              'other_side' => 'right',
              'updated_at' => '2026-02-26 12:51:55',
              'left_module' => 'opportunities',
              'right_class' => 'App\\Models\\Modules\\Order',
              'current_side' => 'left',
              'related_slug' => 'orders',
              'right_module' => 'orders',
              'related_class' => 'App\\Models\\Modules\\Order',
              'other_id_field' => 'right_id',
              'related_fields' =>
              array(
                0 =>
                array(
                  'id' => '01af584c-efa3-4bba-9d8d-0097e0f940d5',
                  'key' => 'orders_description',
                  'name' => 'description',
                  'type' => 'longtext',
                  'label' => 'modules.orders.fields.description',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d2b-7084-8d41-b9073812c298',
                  'dropdown_list_id' => NULL,
                ),
                1 =>
                array(
                  'id' => '1d9fae8b-59a2-4636-b913-6c6830b11e33',
                  'key' => 'orders_currency',
                  'name' => 'currency',
                  'type' => 'dropdown',
                  'label' => 'modules.orders.fields.currency',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d2b-7084-8d41-b9073812c298',
                  'dropdown_list_id' => NULL,
                ),
                2 =>
                array(
                  'id' => '46494b01-e52e-405d-99e8-b23a39146654',
                  'key' => 'orders_assigned_user_id',
                  'name' => 'assigned_user_id',
                  'type' => 'relationship',
                  'label' => 'modules.orders.fields.assigned_user_id',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d2b-7084-8d41-b9073812c298',
                  'dropdown_list_id' => NULL,
                ),
                3 =>
                array(
                  'id' => '5599b798-c538-4ef0-b95b-f991c8ea654c',
                  'key' => 'orders_due_date',
                  'name' => 'due_date',
                  'type' => 'date',
                  'label' => 'modules.orders.fields.due_date',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d2b-7084-8d41-b9073812c298',
                  'dropdown_list_id' => NULL,
                ),
                4 =>
                array(
                  'id' => '71fd0096-b375-4130-9360-14898d34be54',
                  'key' => 'orders_created_at',
                  'name' => 'created_at',
                  'type' => 'datetime',
                  'label' => 'modules.orders.fields.created_at',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d2b-7084-8d41-b9073812c298',
                  'dropdown_list_id' => NULL,
                ),
                5 =>
                array(
                  'id' => '762b2a05-4289-4b54-9df8-7744d49860d2',
                  'key' => 'orders_order_number',
                  'name' => 'order_number',
                  'type' => 'textfield',
                  'label' => 'modules.orders.fields.order_number',
                  'readonly' => false,
                  'required' => true,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d2b-7084-8d41-b9073812c298',
                  'dropdown_list_id' => NULL,
                ),
                6 =>
                array(
                  'id' => '7d85f1c1-1b5c-4ab9-bb01-d8ca0a45372b',
                  'key' => 'orders_opportunity_id',
                  'name' => 'opportunity_id',
                  'type' => 'relationship',
                  'label' => 'modules.orders.fields.opportunity_id',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d2b-7084-8d41-b9073812c298',
                  'dropdown_list_id' => NULL,
                ),
                7 =>
                array(
                  'id' => '7dd074dd-9412-44cf-86d7-f5cc87ba3952',
                  'key' => 'orders_total_amount',
                  'name' => 'total_amount',
                  'type' => 'number',
                  'label' => 'modules.orders.fields.total_amount',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d2b-7084-8d41-b9073812c298',
                  'dropdown_list_id' => NULL,
                ),
                8 =>
                array(
                  'id' => '85a76065-897d-4c8d-b99e-2d3e62e1c12c',
                  'key' => 'orders_account_id',
                  'name' => 'account_id',
                  'type' => 'relationship',
                  'label' => 'modules.orders.fields.account_id',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d2b-7084-8d41-b9073812c298',
                  'dropdown_list_id' => NULL,
                ),
                9 =>
                array(
                  'id' => 'b07a0add-22cb-4266-b781-e124c959d697',
                  'key' => 'orders_updated_at',
                  'name' => 'updated_at',
                  'type' => 'datetime',
                  'label' => 'modules.orders.fields.updated_at',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d2b-7084-8d41-b9073812c298',
                  'dropdown_list_id' => NULL,
                ),
                10 =>
                array(
                  'id' => 'ec061aed-2984-434b-9205-48ed02c96c75',
                  'key' => 'orders_order_date',
                  'name' => 'order_date',
                  'type' => 'date',
                  'label' => 'modules.orders.fields.order_date',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d2b-7084-8d41-b9073812c298',
                  'dropdown_list_id' => NULL,
                ),
                11 =>
                array(
                  'id' => 'f8c1a685-3a75-42aa-8404-05d5025389c5',
                  'key' => 'orders_status',
                  'name' => 'status',
                  'type' => 'dropdown',
                  'label' => 'modules.orders.fields.status',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d2b-7084-8d41-b9073812c298',
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
  array(
    'columns' =>
    array(
      0 =>
      array(
        'name' => 'name',
        'type' => 'textfield',
        'label' => 'modules.defaults.name',
        'sortable' => true,
      ),
      1 =>
      array(
        'key' => 'opportunities_expected_close_date',
        'name' => 'expected_close_date',
        'type' => 'date',
        'label' => 'modules.opportunities.fields.expected_close_date',
        'readonly' => false,
        'required' => false,
        'sortable' => false,
        'dropdown_list' => NULL,
      ),
      2 =>
      array(
        'key' => 'opportunities_type',
        'name' => 'type',
        'type' => 'dropdown',
        'label' => 'modules.opportunities.fields.type',
        'readonly' => false,
        'required' => false,
        'sortable' => false,
        'dropdown_list' => NULL,
      ),
      3 =>
      array(
        'key' => 'opportunities_probability',
        'name' => 'probability',
        'type' => 'number',
        'label' => 'modules.opportunities.fields.probability',
        'readonly' => false,
        'required' => false,
        'sortable' => false,
        'dropdown_list' => NULL,
      ),
      4 =>
      array(
        'key' => 'opportunities_amount',
        'name' => 'amount',
        'type' => 'textfield',
        'label' => 'modules.opportunities.fields.amount',
        'readonly' => false,
        'required' => false,
        'sortable' => false,
        'dropdown_list' => NULL,
      ),
      5 =>
      array(
        'key' => 'opportunities_sales_stage',
        'name' => 'sales_stage',
        'type' => 'dropdown',
        'label' => 'modules.opportunities.fields.sales_stage',
        'readonly' => false,
        'required' => false,
        'sortable' => false,
        'dropdown_list' => NULL,
      ),
      6 =>
      array(
        'key' => 'opportunities_currency',
        'name' => 'currency',
        'type' => 'textfield',
        'label' => 'modules.opportunities.fields.currency',
        'readonly' => false,
        'required' => false,
        'sortable' => false,
        'dropdown_list' => NULL,
      ),
    ),
  ),
);
