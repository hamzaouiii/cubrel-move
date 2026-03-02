<?php

return array(
  'list' =>
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
        'id' => '0f5ba6a1-b842-45e6-a864-dfdcaf92df06',
        'key' => 'accounts_website',
        'name' => 'website',
        'type' => 'textfield',
        'label' => 'modules.accounts.fields.website',
        'readonly' => false,
        'required' => false,
        'sortable' => false,
        'module_id' => '019c99e9-1fa7-711b-853d-329ab73ed199',
        'dropdown_list' => NULL,
        'dropdown_list_id' => NULL,
      ),
      2 =>
      array(
        'id' => 'b09fafa7-644f-49e8-bb48-ceb1b4a94daf',
        'key' => 'accounts_phone',
        'name' => 'phone',
        'type' => 'textfield',
        'label' => 'modules.accounts.fields.phone',
        'readonly' => false,
        'required' => false,
        'sortable' => false,
        'module_id' => '019c99e9-1fa7-711b-853d-329ab73ed199',
        'dropdown_list' => NULL,
        'dropdown_list_id' => NULL,
      ),
      3 =>
      array(
        'id' => '7776deb0-4eb0-493b-b7d8-faef91283174',
        'key' => 'accounts_email',
        'name' => 'email',
        'type' => 'email',
        'label' => 'modules.accounts.fields.email',
        'readonly' => false,
        'required' => false,
        'sortable' => false,
        'module_id' => '019c99e9-1fa7-711b-853d-329ab73ed199',
        'dropdown_list' => NULL,
        'dropdown_list_id' => NULL,
      ),
      4 =>
      array(
        'name' => 'created_at',
        'type' => 'datetime',
        'label' => 'modules.defaults.created_at',
        'sortable' => true,
      ),
      5 =>
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
            'name' => 'name',
            'type' => 'textfield',
            'label' => 'modules.defaults.name',
            'readonly' => false,
            'required' => true,
            'sortable' => true,
          ),
          1 =>
          array(
            'name' => 'website',
            'type' => 'textfield',
            'label' => 'modules.accounts.fields.website',
            'readonly' => false,
            'required' => false,
          ),
          2 =>
          array(
            'name' => 'email',
            'type' => 'email',
            'label' => 'modules.accounts.fields.email',
            'readonly' => false,
            'required' => false,
          ),
          3 =>
          array(
            'name' => 'phone',
            'type' => 'textfield',
            'label' => 'modules.accounts.fields.phone',
            'readonly' => false,
            'required' => false,
          ),
          4 =>
          array(
            'name' => 'description',
            'type' => 'longText',
            'label' => 'modules.defaults.description',
            'readonly' => false,
            'required' => true,
            'sortable' => true,
          ),
          5 =>
          array(
            'name' => 'created_at',
            'type' => 'datetime',
            'label' => 'modules.defaults.created_at',
            'readonly' => true,
            'required' => true,
            'sortable' => true,
          ),
          6 =>
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
      1 =>
      array(
        'name' => 'Address',
        'layout' =>
        array(
          0 =>
          array(
            'name' => 'country',
            'type' => 'textfield',
            'label' => 'modules.accounts.fields.country',
            'readonly' => false,
            'required' => false,
          ),
          1 =>
          array(
            'name' => 'city',
            'type' => 'textfield',
            'label' => 'modules.accounts.fields.city',
            'readonly' => false,
            'required' => false,
          ),
          2 =>
          array(
            'name' => 'shipping_address',
            'type' => 'longtext',
            'label' => 'modules.accounts.fields.shipping_address',
            'readonly' => false,
            'required' => false,
          ),
          3 =>
          array(
            'name' => 'billing_address',
            'type' => 'longtext',
            'label' => 'modules.accounts.fields.billing_address',
            'readonly' => false,
            'required' => false,
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
            'name' => 'accounts_contacts',
            'type' => 'one-to-many',
            'label' => 'relationships.accounts_contacts',
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
                'name' => 'position',
                'type' => 'textfield',
                'label' => 'modules.contacts.fields.position',
              ),
              2 =>
              array(
                'name' => 'phone',
                'type' => 'textfield',
                'label' => 'modules.contacts.fields.phone',
              ),
              3 =>
              array(
                'name' => 'email',
                'type' => 'email',
                'label' => 'modules.contacts.fields.email',
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
              'id' => '325a40e3-d7a5-4b44-8e27-deef85dabfdd',
              'name' => 'accounts_contacts',
              'role' => 'parent',
              'side' => 'left',
              'label' => 'relationships.accounts_contacts',
              'created_at' => '2026-02-26 12:25:18',
              'join_table' => 'relationship_links',
              'left_class' => 'App\\Models\\Modules\\Account',
              'other_side' => 'right',
              'updated_at' => '2026-02-26 12:25:18',
              'left_module' => 'accounts',
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
                  'id' => '088f1ba7-8806-4e62-8ef2-2351e46b9aab',
                  'key' => 'contacts_email',
                  'name' => 'email',
                  'type' => 'email',
                  'label' => 'modules.contacts.fields.email',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fa9-72d5-9bac-545836cefe21',
                  'dropdown_list_id' => NULL,
                ),
                1 =>
                array(
                  'id' => '31fa6522-c58d-453d-81e6-c0f112ad4325',
                  'key' => 'contacts_notes',
                  'name' => 'notes',
                  'type' => 'longtext',
                  'label' => 'modules.contacts.fields.notes',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fa9-72d5-9bac-545836cefe21',
                  'dropdown_list_id' => NULL,
                ),
                2 =>
                array(
                  'id' => '3bc7e610-eff0-48c5-bb56-bcc491b0c672',
                  'key' => 'contacts_description',
                  'name' => 'description',
                  'type' => 'longtext',
                  'label' => 'modules.contacts.fields.description',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fa9-72d5-9bac-545836cefe21',
                  'dropdown_list_id' => NULL,
                ),
                3 =>
                array(
                  'id' => '949e77c4-5687-47e8-bb62-21735672337a',
                  'key' => 'contacts_account_id',
                  'name' => 'account_id',
                  'type' => 'relationship',
                  'label' => 'modules.contacts.fields.account_id',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fa9-72d5-9bac-545836cefe21',
                  'dropdown_list_id' => NULL,
                ),
                4 =>
                array(
                  'id' => '9e743bca-27ff-49ab-96ee-eeb59ac11cc8',
                  'key' => 'contacts_name',
                  'name' => 'name',
                  'type' => 'textfield',
                  'label' => 'modules.contacts.fields.name',
                  'readonly' => false,
                  'required' => true,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fa9-72d5-9bac-545836cefe21',
                  'dropdown_list_id' => NULL,
                ),
                5 =>
                array(
                  'id' => 'cc464106-aec5-4997-a43c-d064dd8075c3',
                  'key' => 'contacts_updated_at',
                  'name' => 'updated_at',
                  'type' => 'datetime',
                  'label' => 'modules.contacts.fields.updated_at',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fa9-72d5-9bac-545836cefe21',
                  'dropdown_list_id' => NULL,
                ),
                6 =>
                array(
                  'id' => 'ccc7afb8-6086-401a-8cbb-88fe5405ed31',
                  'key' => 'contacts_last_name',
                  'name' => 'last_name',
                  'type' => 'textfield',
                  'label' => 'modules.contacts.fields.last_name',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fa9-72d5-9bac-545836cefe21',
                  'dropdown_list_id' => NULL,
                ),
                7 =>
                array(
                  'id' => 'df20cb21-de29-46f4-8a3b-f56a8d1508a1',
                  'key' => 'contacts_created_at',
                  'name' => 'created_at',
                  'type' => 'datetime',
                  'label' => 'modules.contacts.fields.created_at',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fa9-72d5-9bac-545836cefe21',
                  'dropdown_list_id' => NULL,
                ),
                8 =>
                array(
                  'id' => 'e21e78c3-aa40-40a2-962c-79dd7edcf15f',
                  'key' => 'contacts_position',
                  'name' => 'position',
                  'type' => 'textfield',
                  'label' => 'modules.contacts.fields.position',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fa9-72d5-9bac-545836cefe21',
                  'dropdown_list_id' => NULL,
                ),
                9 =>
                array(
                  'id' => 'e25c4379-086e-4b5b-a03e-be680c7e1466',
                  'key' => 'contacts_first_name',
                  'name' => 'first_name',
                  'type' => 'textfield',
                  'label' => 'modules.contacts.fields.first_name',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fa9-72d5-9bac-545836cefe21',
                  'dropdown_list_id' => NULL,
                ),
                10 =>
                array(
                  'id' => 'fec95ad2-f8f1-4411-8cf6-d4a6eb79d5e9',
                  'key' => 'contacts_phone',
                  'name' => 'phone',
                  'type' => 'textfield',
                  'label' => 'modules.contacts.fields.phone',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fa9-72d5-9bac-545836cefe21',
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
          1 =>
          array(
            'name' => 'accounts_invoices',
            'type' => 'one-to-many',
            'label' => 'relationships.accounts_invoices',
            'panelHeader' =>
            array(
              0 =>
              array(
                'name' => 'name',
                'type' => 'textfield',
                'label' => 'modules.invoices.fields.name',
              ),
              1 =>
              array(
                'name' => 'status',
                'type' => 'dropdown',
                'label' => 'modules.invoices.fields.status',
              ),
              2 =>
              array(
                'name' => 'number',
                'type' => 'number',
                'label' => 'modules.invoices.fields.number',
              ),
              3 =>
              array(
                'name' => 'subtotal',
                'type' => 'number',
                'label' => 'modules.invoices.fields.subtotal',
              ),
              4 =>
              array(
                'name' => 'tax',
                'type' => 'number',
                'label' => 'modules.invoices.fields.tax',
              ),
              5 =>
              array(
                'name' => 'total',
                'type' => 'number',
                'label' => 'modules.invoices.fields.total',
              ),
              6 =>
              array(
                'name' => 'issue_date',
                'type' => 'date',
                'label' => 'modules.invoices.fields.issue_date',
              ),
              7 =>
              array(
                'name' => 'due_date',
                'type' => 'date',
                'label' => 'modules.invoices.fields.due_date',
              ),
            ),
            'relationship' =>
            array(
              'id' => '1a0d107b-c9bd-454b-9a65-5e92b9986810',
              'name' => 'accounts_invoices',
              'role' => 'parent',
              'side' => 'left',
              'label' => 'relationships.accounts_invoices',
              'created_at' => '2026-02-26 12:25:18',
              'join_table' => 'relationship_links',
              'left_class' => 'App\\Models\\Modules\\Account',
              'other_side' => 'right',
              'updated_at' => '2026-02-26 12:25:18',
              'left_module' => 'accounts',
              'right_class' => 'App\\Models\\Modules\\Invoice',
              'current_side' => 'left',
              'related_slug' => 'invoices',
              'right_module' => 'invoices',
              'related_class' => 'App\\Models\\Modules\\Invoice',
              'other_id_field' => 'right_id',
              'related_fields' =>
              array(
                0 =>
                array(
                  'id' => '053a7e92-e1ba-4163-829d-556a43a0141e',
                  'key' => 'invoices_status',
                  'name' => 'status',
                  'type' => 'dropdown',
                  'label' => 'modules.invoices.fields.status',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fb0-70d6-a9c2-a8231f894937',
                  'dropdown_list_id' => NULL,
                ),
                1 =>
                array(
                  'id' => '199dd2a9-4196-4dc2-97d5-1ae47ae8c057',
                  'key' => 'invoices_due_date',
                  'name' => 'due_date',
                  'type' => 'date',
                  'label' => 'modules.invoices.fields.due_date',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fb0-70d6-a9c2-a8231f894937',
                  'dropdown_list_id' => NULL,
                ),
                2 =>
                array(
                  'id' => '1f1166f3-dc77-4b04-ad26-b8d976a660ef',
                  'key' => 'invoices_created_at',
                  'name' => 'created_at',
                  'type' => 'datetime',
                  'label' => 'modules.invoices.fields.created_at',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fb0-70d6-a9c2-a8231f894937',
                  'dropdown_list_id' => NULL,
                ),
                3 =>
                array(
                  'id' => '20cb23df-e1d6-4f68-90b3-8117be83582d',
                  'key' => 'invoices_currency',
                  'name' => 'currency',
                  'type' => 'dropdown',
                  'label' => 'modules.invoices.fields.currency',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fb0-70d6-a9c2-a8231f894937',
                  'dropdown_list_id' => NULL,
                ),
                4 =>
                array(
                  'id' => '5724b58f-09a3-4a81-bdf5-7bef560bf541',
                  'key' => 'invoices_tax',
                  'name' => 'tax',
                  'type' => 'number',
                  'label' => 'modules.invoices.fields.tax',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fb0-70d6-a9c2-a8231f894937',
                  'dropdown_list_id' => NULL,
                ),
                5 =>
                array(
                  'id' => '5a137365-6667-4d4c-be36-b2f6d87da416',
                  'key' => 'invoices_number',
                  'name' => 'number',
                  'type' => 'number',
                  'label' => 'modules.invoices.fields.number',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fb0-70d6-a9c2-a8231f894937',
                  'dropdown_list_id' => NULL,
                ),
                6 =>
                array(
                  'id' => '77bc52b5-a1a4-4661-9177-7b83d9e3fc7c',
                  'key' => 'invoices_account_id',
                  'name' => 'account_id',
                  'type' => 'relationship',
                  'label' => 'modules.invoices.fields.account_id',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fb0-70d6-a9c2-a8231f894937',
                  'dropdown_list_id' => NULL,
                ),
                7 =>
                array(
                  'id' => '7b7461b9-23c4-417a-aad2-31d2a7a6f38c',
                  'key' => 'invoices_total',
                  'name' => 'total',
                  'type' => 'number',
                  'label' => 'modules.invoices.fields.total',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fb0-70d6-a9c2-a8231f894937',
                  'dropdown_list_id' => NULL,
                ),
                8 =>
                array(
                  'id' => '7ea1614f-e6db-425f-8ce6-42500b62e2cf',
                  'key' => 'invoices_subtotal',
                  'name' => 'subtotal',
                  'type' => 'number',
                  'label' => 'modules.invoices.fields.subtotal',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fb0-70d6-a9c2-a8231f894937',
                  'dropdown_list_id' => NULL,
                ),
                9 =>
                array(
                  'id' => '8afad3f0-e4f1-47e8-a3b0-e2fba73592a1',
                  'key' => 'invoices_description',
                  'name' => 'description',
                  'type' => 'longtext',
                  'label' => 'modules.invoices.fields.description',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fb0-70d6-a9c2-a8231f894937',
                  'dropdown_list_id' => NULL,
                ),
                10 =>
                array(
                  'id' => '9c12f45e-f43f-4522-b5d8-b1b02c713ee8',
                  'key' => 'invoices_contact_id',
                  'name' => 'contact_id',
                  'type' => 'relationship',
                  'label' => 'modules.invoices.fields.contact_id',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fb0-70d6-a9c2-a8231f894937',
                  'dropdown_list_id' => NULL,
                ),
                11 =>
                array(
                  'id' => 'a72b4464-c39d-44f6-b68b-9c43e39fde82',
                  'key' => 'invoices_name',
                  'name' => 'name',
                  'type' => 'textfield',
                  'label' => 'modules.invoices.fields.name',
                  'readonly' => false,
                  'required' => true,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fb0-70d6-a9c2-a8231f894937',
                  'dropdown_list_id' => NULL,
                ),
                12 =>
                array(
                  'id' => 'bfe0b3b6-32ba-4985-bef9-d34569d24b6e',
                  'key' => 'invoices_quote_id',
                  'name' => 'quote_id',
                  'type' => 'dropdown',
                  'label' => 'modules.invoices.fields.quote_id',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fb0-70d6-a9c2-a8231f894937',
                  'dropdown_list_id' => NULL,
                ),
                13 =>
                array(
                  'id' => 'c7ddaa43-76d6-4212-9c23-48de5d74d4c0',
                  'key' => 'invoices_notes',
                  'name' => 'notes',
                  'type' => 'longtext',
                  'label' => 'modules.invoices.fields.notes',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fb0-70d6-a9c2-a8231f894937',
                  'dropdown_list_id' => NULL,
                ),
                14 =>
                array(
                  'id' => 'ec2748e1-e22f-4fa8-9265-042d4f6fbad0',
                  'key' => 'invoices_issue_date',
                  'name' => 'issue_date',
                  'type' => 'date',
                  'label' => 'modules.invoices.fields.issue_date',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fb0-70d6-a9c2-a8231f894937',
                  'dropdown_list_id' => NULL,
                ),
                15 =>
                array(
                  'id' => 'f9f4c493-6df4-4c2c-85dd-709e1da0f776',
                  'key' => 'invoices_updated_at',
                  'name' => 'updated_at',
                  'type' => 'datetime',
                  'label' => 'modules.invoices.fields.updated_at',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fb0-70d6-a9c2-a8231f894937',
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
          array(
            'name' => 'accounts_cases',
            'type' => 'one-to-many',
            'label' => 'relationships.accounts_cases',
            'panelHeader' =>
            array(
              0 =>
              array(
                'name' => 'name',
                'type' => 'textfield',
                'label' => 'modules.cases.fields.name',
              ),
              1 =>
              array(
                'name' => 'subject',
                'type' => 'textfield',
                'label' => 'modules.cases.fields.subject',
              ),
              2 =>
              array(
                'name' => 'status',
                'type' => 'dropdown',
                'label' => 'modules.cases.fields.status',
              ),
              3 =>
              array(
                'name' => 'priority',
                'type' => 'dropdown',
                'label' => 'modules.cases.fields.priority',
              ),
              4 =>
              array(
                'name' => 'opened_at',
                'type' => 'datetime',
                'label' => 'modules.cases.fields.opened_at',
              ),
              5 =>
              array(
                'name' => 'closed_at',
                'type' => 'datetime',
                'label' => 'modules.cases.fields.closed_at',
              ),
            ),
            'relationship' =>
            array(
              'id' => '362a3b2b-0c20-443f-a8c1-a43a7a01e116',
              'name' => 'accounts_cases',
              'role' => 'parent',
              'side' => 'left',
              'label' => 'relationships.accounts_cases',
              'created_at' => '2026-02-26 12:25:18',
              'join_table' => 'relationship_links',
              'left_class' => 'App\\Models\\Modules\\Account',
              'other_side' => 'right',
              'updated_at' => '2026-02-26 12:25:18',
              'left_module' => 'accounts',
              'right_class' => 'App\\Models\\Modules\\SupportCase',
              'current_side' => 'left',
              'related_slug' => 'cases',
              'right_module' => 'cases',
              'related_class' => 'App\\Models\\Modules\\SupportCase',
              'other_id_field' => 'right_id',
              'related_fields' =>
              array(
                0 =>
                array(
                  'id' => '083b22c7-2924-4b65-8ab8-fe1b4adc5ecb',
                  'key' => 'cases_account_id',
                  'name' => 'account_id',
                  'type' => 'relationship',
                  'label' => 'modules.cases.fields.account_id',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fb3-7161-b93d-f6f9775ccbe1',
                  'dropdown_list_id' => NULL,
                ),
                1 =>
                array(
                  'id' => '3886b8fd-1313-46f8-a7ea-a72e65d6fda6',
                  'key' => 'cases_status',
                  'name' => 'status',
                  'type' => 'dropdown',
                  'label' => 'modules.cases.fields.status',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fb3-7161-b93d-f6f9775ccbe1',
                  'dropdown_list_id' => NULL,
                ),
                2 =>
                array(
                  'id' => '39c38472-ac39-4b56-8aff-2f48209ec166',
                  'key' => 'cases_priority',
                  'name' => 'priority',
                  'type' => 'dropdown',
                  'label' => 'modules.cases.fields.priority',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fb3-7161-b93d-f6f9775ccbe1',
                  'dropdown_list_id' => NULL,
                ),
                3 =>
                array(
                  'id' => '4628463d-a986-43d0-873b-80474b7405fd',
                  'key' => 'cases_updated_at',
                  'name' => 'updated_at',
                  'type' => 'datetime',
                  'label' => 'modules.cases.fields.updated_at',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fb3-7161-b93d-f6f9775ccbe1',
                  'dropdown_list_id' => NULL,
                ),
                4 =>
                array(
                  'id' => '4822ac68-daf0-4a79-94a7-3fec1de0ceb3',
                  'key' => 'cases_contact_id',
                  'name' => 'contact_id',
                  'type' => 'relationship',
                  'label' => 'modules.cases.fields.contact_id',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fb3-7161-b93d-f6f9775ccbe1',
                  'dropdown_list_id' => NULL,
                ),
                5 =>
                array(
                  'id' => '516f05dc-3237-4615-beb4-cc31c0565c5d',
                  'key' => 'cases_created_at',
                  'name' => 'created_at',
                  'type' => 'datetime',
                  'label' => 'modules.cases.fields.created_at',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fb3-7161-b93d-f6f9775ccbe1',
                  'dropdown_list_id' => NULL,
                ),
                6 =>
                array(
                  'id' => '59680d13-c342-4df4-ace4-af397d6f13f5',
                  'key' => 'cases_description',
                  'name' => 'description',
                  'type' => 'longtext',
                  'label' => 'modules.cases.fields.description',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fb3-7161-b93d-f6f9775ccbe1',
                  'dropdown_list_id' => NULL,
                ),
                7 =>
                array(
                  'id' => 'ac3c76e7-9222-4e02-986e-481398e7c963',
                  'key' => 'cases_closed_at',
                  'name' => 'closed_at',
                  'type' => 'datetime',
                  'label' => 'modules.cases.fields.closed_at',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fb3-7161-b93d-f6f9775ccbe1',
                  'dropdown_list_id' => NULL,
                ),
                8 =>
                array(
                  'id' => 'bd412a64-9afb-47ae-8111-daaf4f2798f7',
                  'key' => 'cases_opened_at',
                  'name' => 'opened_at',
                  'type' => 'datetime',
                  'label' => 'modules.cases.fields.opened_at',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fb3-7161-b93d-f6f9775ccbe1',
                  'dropdown_list_id' => NULL,
                ),
                9 =>
                array(
                  'id' => 'd62d97d2-3ad8-4002-9919-c1a9001c3530',
                  'key' => 'cases_name',
                  'name' => 'name',
                  'type' => 'textfield',
                  'label' => 'modules.cases.fields.name',
                  'readonly' => false,
                  'required' => true,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fb3-7161-b93d-f6f9775ccbe1',
                  'dropdown_list_id' => NULL,
                ),
                10 =>
                array(
                  'id' => 'e52972bc-9db0-4e54-a79a-8384159323e1',
                  'key' => 'cases_subject',
                  'name' => 'subject',
                  'type' => 'textfield',
                  'label' => 'modules.cases.fields.subject',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fb3-7161-b93d-f6f9775ccbe1',
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
          3 =>
          array(
            'name' => 'accounts_inquiries',
            'type' => 'one-to-many',
            'label' => 'relationships.accounts_inquiries',
            'panelHeader' =>
            array(
              0 =>
              array(
                'name' => 'name',
                'type' => 'textfield',
                'label' => 'modules.inquiries.fields.name',
              ),
              1 =>
              array(
                'name' => 'email',
                'type' => 'email',
                'label' => 'modules.inquiries.fields.email',
              ),
              2 =>
              array(
                'name' => 'phone',
                'type' => 'textfield',
                'label' => 'modules.inquiries.fields.phone',
              ),
              3 =>
              array(
                'name' => 'status',
                'type' => 'dropdown',
                'label' => 'modules.inquiries.fields.status',
              ),
            ),
            'relationship' =>
            array(
              'id' => 'cd16bae8-b04b-4c03-8688-f13fee371a15',
              'name' => 'accounts_inquiries',
              'role' => 'parent',
              'side' => 'left',
              'label' => 'relationships.accounts_inquiries',
              'created_at' => '2026-02-26 12:25:18',
              'join_table' => 'relationship_links',
              'left_class' => 'App\\Models\\Modules\\Account',
              'other_side' => 'right',
              'updated_at' => '2026-02-26 12:25:18',
              'left_module' => 'accounts',
              'right_class' => 'App\\Models\\Modules\\ContactMessage',
              'current_side' => 'left',
              'related_slug' => 'inquiries',
              'right_module' => 'inquiries',
              'related_class' => 'App\\Models\\Modules\\ContactMessage',
              'other_id_field' => 'right_id',
              'related_fields' =>
              array(
                0 =>
                array(
                  'id' => '0e81fea4-5680-43b3-a86c-598c2193f7f5',
                  'key' => 'inquiries_user_agent',
                  'name' => 'user_agent',
                  'type' => 'longtext',
                  'label' => 'modules.inquiries.fields.user_agent',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fb7-7310-bb52-d7bde59aa7be',
                  'dropdown_list_id' => NULL,
                ),
                1 =>
                array(
                  'id' => '2ba24f9d-936e-4f78-af0a-c37c8b9ab0fc',
                  'key' => 'inquiries_created_at',
                  'name' => 'created_at',
                  'type' => 'datetime',
                  'label' => 'modules.inquiries.fields.created_at',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fb7-7310-bb52-d7bde59aa7be',
                  'dropdown_list_id' => NULL,
                ),
                2 =>
                array(
                  'id' => '3f963ec2-d651-4a15-b128-29391acc5033',
                  'key' => 'inquiries_updated_at',
                  'name' => 'updated_at',
                  'type' => 'datetime',
                  'label' => 'modules.inquiries.fields.updated_at',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fb7-7310-bb52-d7bde59aa7be',
                  'dropdown_list_id' => NULL,
                ),
                3 =>
                array(
                  'id' => '5e536eb5-75e6-440c-8402-a65c746bbdbf',
                  'key' => 'inquiries_status',
                  'name' => 'status',
                  'type' => 'dropdown',
                  'label' => 'modules.inquiries.fields.status',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fb7-7310-bb52-d7bde59aa7be',
                  'dropdown_list_id' => NULL,
                ),
                4 =>
                array(
                  'id' => '7aeaa6d1-438a-4fb1-86f1-0af499dd088f',
                  'key' => 'inquiries_description',
                  'name' => 'description',
                  'type' => 'longtext',
                  'label' => 'modules.inquiries.fields.description',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fb7-7310-bb52-d7bde59aa7be',
                  'dropdown_list_id' => NULL,
                ),
                5 =>
                array(
                  'id' => '96ae6d78-61fa-42a0-b9c2-fe7d255ace87',
                  'key' => 'inquiries_name',
                  'name' => 'name',
                  'type' => 'textfield',
                  'label' => 'modules.inquiries.fields.name',
                  'readonly' => false,
                  'required' => true,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fb7-7310-bb52-d7bde59aa7be',
                  'dropdown_list_id' => NULL,
                ),
                6 =>
                array(
                  'id' => 'c9b33f95-758f-491c-b5cc-6d90972797a7',
                  'key' => 'inquiries_message',
                  'name' => 'message',
                  'type' => 'longtext',
                  'label' => 'modules.inquiries.fields.message',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fb7-7310-bb52-d7bde59aa7be',
                  'dropdown_list_id' => NULL,
                ),
                7 =>
                array(
                  'id' => 'e421cc7a-14a5-47ee-a5e4-07ca4e588e4e',
                  'key' => 'inquiries_email',
                  'name' => 'email',
                  'type' => 'email',
                  'label' => 'modules.inquiries.fields.email',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fb7-7310-bb52-d7bde59aa7be',
                  'dropdown_list_id' => NULL,
                ),
                8 =>
                array(
                  'id' => 'e83f89da-de09-47d0-b5ba-e292f1e5ca0b',
                  'key' => 'inquiries_ip',
                  'name' => 'ip',
                  'type' => 'textfield',
                  'label' => 'modules.inquiries.fields.ip',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fb7-7310-bb52-d7bde59aa7be',
                  'dropdown_list_id' => NULL,
                ),
                9 =>
                array(
                  'id' => 'f071b2cc-3757-440f-9ef0-5860156d7747',
                  'key' => 'inquiries_phone',
                  'name' => 'phone',
                  'type' => 'textfield',
                  'label' => 'modules.inquiries.fields.phone',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fb7-7310-bb52-d7bde59aa7be',
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
            'name' => 'accounts_opportunities',
            'type' => 'one-to-many',
            'label' => 'relationships.accounts_opportunities',
            'panelHeader' =>
            array(
              0 =>
              array(
                'name' => 'name',
                'type' => 'textfield',
                'label' => 'modules.opportunities.fields.name',
              ),
              1 =>
              array(
                'name' => 'sales_stage',
                'type' => 'dropdown',
                'label' => 'modules.opportunities.fields.sales_stage',
              ),
              2 =>
              array(
                'name' => 'amount',
                'type' => 'textfield',
                'label' => 'modules.opportunities.fields.amount',
              ),
              3 =>
              array(
                'name' => 'probability',
                'type' => 'number',
                'label' => 'modules.opportunities.fields.probability',
              ),
              4 =>
              array(
                'name' => 'type',
                'type' => 'dropdown',
                'label' => 'modules.opportunities.fields.type',
              ),
              5 =>
              array(
                'name' => 'expected_close_date',
                'type' => 'date',
                'label' => 'modules.opportunities.fields.expected_close_date',
              ),
            ),
            'relationship' =>
            array(
              'id' => '211b9136-2f98-45d8-a829-1b45a4a6a16a',
              'name' => 'accounts_opportunities',
              'role' => 'parent',
              'side' => 'left',
              'label' => 'relationships.accounts_opportunities',
              'created_at' => '2026-02-26 12:25:18',
              'join_table' => 'relationship_links',
              'left_class' => 'App\\Models\\Modules\\Account',
              'other_side' => 'right',
              'updated_at' => '2026-02-26 12:25:18',
              'left_module' => 'accounts',
              'right_class' => 'App\\Models\\Modules\\Opportunity',
              'current_side' => 'left',
              'related_slug' => 'opportunities',
              'right_module' => 'opportunities',
              'related_class' => 'App\\Models\\Modules\\Opportunity',
              'other_id_field' => 'right_id',
              'related_fields' =>
              array(
                0 =>
                array(
                  'id' => '025314ea-cee9-4d5e-80b5-e1f3498c1af6',
                  'key' => 'opportunities_account_id',
                  'name' => 'account_id',
                  'type' => 'relationship',
                  'label' => 'modules.opportunities.fields.account_id',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fab-73be-8cb0-10c211f3dddd',
                  'dropdown_list_id' => NULL,
                ),
                1 =>
                array(
                  'id' => '6c7026ee-231e-4e25-81ca-bb010030094a',
                  'key' => 'opportunities_expected_close_date',
                  'name' => 'expected_close_date',
                  'type' => 'date',
                  'label' => 'modules.opportunities.fields.expected_close_date',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fab-73be-8cb0-10c211f3dddd',
                  'dropdown_list_id' => NULL,
                ),
                2 =>
                array(
                  'id' => '6cfa2f8b-137a-4da0-bb8d-a4366abe299a',
                  'key' => 'opportunities_updated_at',
                  'name' => 'updated_at',
                  'type' => 'datetime',
                  'label' => 'modules.opportunities.fields.updated_at',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fab-73be-8cb0-10c211f3dddd',
                  'dropdown_list_id' => NULL,
                ),
                3 =>
                array(
                  'id' => '75d4e4c4-2abd-4cad-ab50-c9e43b0624c0',
                  'key' => 'opportunities_type',
                  'name' => 'type',
                  'type' => 'dropdown',
                  'label' => 'modules.opportunities.fields.type',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fab-73be-8cb0-10c211f3dddd',
                  'dropdown_list_id' => NULL,
                ),
                4 =>
                array(
                  'id' => '879a52ed-47ef-4849-a288-5ab6e8de9667',
                  'key' => 'opportunities_description',
                  'name' => 'description',
                  'type' => 'longtext',
                  'label' => 'modules.opportunities.fields.description',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fab-73be-8cb0-10c211f3dddd',
                  'dropdown_list_id' => NULL,
                ),
                5 =>
                array(
                  'id' => 'af0d4827-a598-433b-a297-415f377cbd26',
                  'key' => 'opportunities_assigned_user_id',
                  'name' => 'assigned_user_id',
                  'type' => 'relationship',
                  'label' => 'modules.opportunities.fields.assigned_user_id',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fab-73be-8cb0-10c211f3dddd',
                  'dropdown_list_id' => NULL,
                ),
                6 =>
                array(
                  'id' => 'b1cac621-4148-4e3c-9a71-456057ddd3ea',
                  'key' => 'opportunities_created_at',
                  'name' => 'created_at',
                  'type' => 'datetime',
                  'label' => 'modules.opportunities.fields.created_at',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fab-73be-8cb0-10c211f3dddd',
                  'dropdown_list_id' => NULL,
                ),
                7 =>
                array(
                  'id' => 'b8de286f-0d39-4fe2-9e92-c6db6f2e7ac0',
                  'key' => 'opportunities_sales_stage',
                  'name' => 'sales_stage',
                  'type' => 'dropdown',
                  'label' => 'modules.opportunities.fields.sales_stage',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fab-73be-8cb0-10c211f3dddd',
                  'dropdown_list_id' => NULL,
                ),
                8 =>
                array(
                  'id' => 'ba5f6419-a0b8-4388-967b-c673cc078c9d',
                  'key' => 'opportunities_name',
                  'name' => 'name',
                  'type' => 'textfield',
                  'label' => 'modules.opportunities.fields.name',
                  'readonly' => false,
                  'required' => true,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fab-73be-8cb0-10c211f3dddd',
                  'dropdown_list_id' => NULL,
                ),
                9 =>
                array(
                  'id' => 'c761ced5-3de8-4b81-b45f-ff420f060346',
                  'key' => 'opportunities_amount',
                  'name' => 'amount',
                  'type' => 'textfield',
                  'label' => 'modules.opportunities.fields.amount',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fab-73be-8cb0-10c211f3dddd',
                  'dropdown_list_id' => NULL,
                ),
                10 =>
                array(
                  'id' => 'c8c7d7a9-d5aa-4339-ae38-f6173d2f59ee',
                  'key' => 'opportunities_currency',
                  'name' => 'currency',
                  'type' => 'dropdown',
                  'label' => 'modules.opportunities.fields.currency',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fab-73be-8cb0-10c211f3dddd',
                  'dropdown_list_id' => NULL,
                ),
                11 =>
                array(
                  'id' => 'f28f0006-0667-4fe7-8638-52915e649b0b',
                  'key' => 'opportunities_probability',
                  'name' => 'probability',
                  'type' => 'number',
                  'label' => 'modules.opportunities.fields.probability',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fab-73be-8cb0-10c211f3dddd',
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
          1 =>
          array(
            'name' => 'accounts_quotes',
            'type' => 'one-to-many',
            'label' => 'relationships.accounts_quotes',
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
                'name' => 'created_at',
                'type' => 'datetime',
                'label' => 'modules.quotes.fields.created_at',
              ),
              2 =>
              array(
                'name' => 'updated_at',
                'type' => 'datetime',
                'label' => 'modules.quotes.fields.updated_at',
              ),
            ),
            'relationship' =>
            array(
              'id' => 'c44ceee8-5bd4-4035-a187-112b52575a32',
              'name' => 'accounts_quotes',
              'role' => 'parent',
              'side' => 'left',
              'label' => 'relationships.accounts_quotes',
              'created_at' => '2026-02-26 12:25:18',
              'join_table' => 'relationship_links',
              'left_class' => 'App\\Models\\Modules\\Account',
              'other_side' => 'right',
              'updated_at' => '2026-02-26 12:25:18',
              'left_module' => 'accounts',
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
                  'id' => '100cf1dc-6194-42c8-a2c6-2b9d3cc6c322',
                  'key' => 'quotes_contact_id',
                  'name' => 'contact_id',
                  'type' => 'relationship',
                  'label' => 'modules.quotes.fields.contact_id',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fac-73ce-9d9f-f5850b9f3a3b',
                  'dropdown_list_id' => NULL,
                ),
                1 =>
                array(
                  'id' => '288ade5f-a384-4530-9647-1a389c2c25b0',
                  'key' => 'quotes_created_at',
                  'name' => 'created_at',
                  'type' => 'datetime',
                  'label' => 'modules.quotes.fields.created_at',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fac-73ce-9d9f-f5850b9f3a3b',
                  'dropdown_list_id' => NULL,
                ),
                2 =>
                array(
                  'id' => '3b9bafcf-7e2d-4cb3-b850-84dac2efe28e',
                  'key' => 'quotes_subtotal',
                  'name' => 'subtotal',
                  'type' => 'number',
                  'label' => 'modules.quotes.fields.subtotal',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fac-73ce-9d9f-f5850b9f3a3b',
                  'dropdown_list_id' => NULL,
                ),
                3 =>
                array(
                  'id' => '3cff3af4-3f19-43f6-a64a-4c5e8e741bba',
                  'key' => 'quotes_name',
                  'name' => 'name',
                  'type' => 'textfield',
                  'label' => 'modules.quotes.fields.name',
                  'readonly' => false,
                  'required' => true,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fac-73ce-9d9f-f5850b9f3a3b',
                  'dropdown_list_id' => NULL,
                ),
                4 =>
                array(
                  'id' => '54ae0694-d109-4577-92b8-4e2db34d996a',
                  'key' => 'quotes_description',
                  'name' => 'description',
                  'type' => 'longtext',
                  'label' => 'modules.quotes.fields.description',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fac-73ce-9d9f-f5850b9f3a3b',
                  'dropdown_list_id' => NULL,
                ),
                5 =>
                array(
                  'id' => '613483fd-8d34-4770-a6a0-86f581c21233',
                  'key' => 'quotes_tax',
                  'name' => 'tax',
                  'type' => 'number',
                  'label' => 'modules.quotes.fields.tax',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fac-73ce-9d9f-f5850b9f3a3b',
                  'dropdown_list_id' => NULL,
                ),
                6 =>
                array(
                  'id' => '667297fe-6f03-4b6c-8d94-2f692193263d',
                  'key' => 'quotes_updated_at',
                  'name' => 'updated_at',
                  'type' => 'datetime',
                  'label' => 'modules.quotes.fields.updated_at',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fac-73ce-9d9f-f5850b9f3a3b',
                  'dropdown_list_id' => NULL,
                ),
                7 =>
                array(
                  'id' => '7cf79ef1-c6a9-479d-a33f-519a261112ea',
                  'key' => 'quotes_number',
                  'name' => 'number',
                  'type' => 'number',
                  'label' => 'modules.quotes.fields.number',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fac-73ce-9d9f-f5850b9f3a3b',
                  'dropdown_list_id' => NULL,
                ),
                8 =>
                array(
                  'id' => '8bc55a7c-4f10-40f2-81d4-a1cf609e7780',
                  'key' => 'quotes_status',
                  'name' => 'status',
                  'type' => 'dropdown',
                  'label' => 'modules.quotes.fields.status',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fac-73ce-9d9f-f5850b9f3a3b',
                  'dropdown_list_id' => NULL,
                ),
                9 =>
                array(
                  'id' => '995e50f3-5637-4e61-bf75-c4114eb8b0ec',
                  'key' => 'quotes_total',
                  'name' => 'total',
                  'type' => 'number',
                  'label' => 'modules.quotes.fields.total',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fac-73ce-9d9f-f5850b9f3a3b',
                  'dropdown_list_id' => NULL,
                ),
                10 =>
                array(
                  'id' => 'ac731f7f-4237-4bcd-908a-fa2e3c132b33',
                  'key' => 'quotes_account_id',
                  'name' => 'account_id',
                  'type' => 'relationship',
                  'label' => 'modules.quotes.fields.account_id',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fac-73ce-9d9f-f5850b9f3a3b',
                  'dropdown_list_id' => NULL,
                ),
                11 =>
                array(
                  'id' => 'b7a1a6ce-b6dc-4875-b557-6ed28beb2d44',
                  'key' => 'quotes_currency',
                  'name' => 'currency',
                  'type' => 'dropdown',
                  'label' => 'modules.quotes.fields.currency',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fac-73ce-9d9f-f5850b9f3a3b',
                  'dropdown_list_id' => NULL,
                ),
                12 =>
                array(
                  'id' => 'bf77203a-38c9-4e58-9ce4-8f0644507dd0',
                  'key' => 'quotes_notes',
                  'name' => 'notes',
                  'type' => 'longtext',
                  'label' => 'modules.quotes.fields.notes',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fac-73ce-9d9f-f5850b9f3a3b',
                  'dropdown_list_id' => NULL,
                ),
                13 =>
                array(
                  'id' => 'eb372203-172b-47a4-87e2-7312d0d6d4f6',
                  'key' => 'quotes_valid_until',
                  'name' => 'valid_until',
                  'type' => 'date',
                  'label' => 'modules.quotes.fields.valid_until',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fac-73ce-9d9f-f5850b9f3a3b',
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
          array(
            'name' => 'accounts_orders',
            'type' => 'one-to-many',
            'label' => 'relationships.accounts_orders',
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
                'name' => 'order_date',
                'type' => 'date',
                'label' => 'modules.orders.fields.order_date',
              ),
              2 =>
              array(
                'name' => 'status',
                'type' => 'dropdown',
                'label' => 'modules.orders.fields.status',
              ),
              3 =>
              array(
                'name' => 'total_amount',
                'type' => 'number',
                'label' => 'modules.orders.fields.total_amount',
              ),
              4 =>
              array(
                'name' => 'due_date',
                'type' => 'date',
                'label' => 'modules.orders.fields.due_date',
              ),
              5 =>
              array(
                'name' => 'created_at',
                'type' => 'datetime',
                'label' => 'modules.orders.fields.created_at',
              ),
            ),
            'relationship' =>
            array(
              'id' => 'c46d218d-b926-4fe4-bc4a-8f46383d13e6',
              'name' => 'accounts_orders',
              'role' => 'parent',
              'side' => 'left',
              'label' => 'relationships.accounts_orders',
              'created_at' => '2026-02-26 12:25:18',
              'join_table' => 'relationship_links',
              'left_class' => 'App\\Models\\Modules\\Account',
              'other_side' => 'right',
              'updated_at' => '2026-02-26 12:25:18',
              'left_module' => 'accounts',
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
                  'id' => '0c5b67e9-71e8-4ae7-a784-097468c0382a',
                  'key' => 'orders_account_id',
                  'name' => 'account_id',
                  'type' => 'relationship',
                  'label' => 'modules.orders.fields.account_id',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fae-7068-811e-add6da57eb89',
                  'dropdown_list_id' => NULL,
                ),
                1 =>
                array(
                  'id' => '1b9316bb-748a-49ff-9601-0fc0d1c3b31d',
                  'key' => 'orders_description',
                  'name' => 'description',
                  'type' => 'longtext',
                  'label' => 'modules.orders.fields.description',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fae-7068-811e-add6da57eb89',
                  'dropdown_list_id' => NULL,
                ),
                2 =>
                array(
                  'id' => '1bd18599-6609-47c1-bd9e-ff8fd58eec9c',
                  'key' => 'orders_order_date',
                  'name' => 'order_date',
                  'type' => 'date',
                  'label' => 'modules.orders.fields.order_date',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fae-7068-811e-add6da57eb89',
                  'dropdown_list_id' => NULL,
                ),
                3 =>
                array(
                  'id' => '63892e60-c893-4702-89ca-2ab15ec2abc3',
                  'key' => 'orders_currency',
                  'name' => 'currency',
                  'type' => 'dropdown',
                  'label' => 'modules.orders.fields.currency',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fae-7068-811e-add6da57eb89',
                  'dropdown_list_id' => NULL,
                ),
                4 =>
                array(
                  'id' => '83cee8f9-c758-46e5-bc83-dfb27818bef9',
                  'key' => 'orders_assigned_user_id',
                  'name' => 'assigned_user_id',
                  'type' => 'relationship',
                  'label' => 'modules.orders.fields.assigned_user_id',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fae-7068-811e-add6da57eb89',
                  'dropdown_list_id' => NULL,
                ),
                5 =>
                array(
                  'id' => '93c1fa0e-e265-4668-8897-f62e198c31fc',
                  'key' => 'orders_updated_at',
                  'name' => 'updated_at',
                  'type' => 'datetime',
                  'label' => 'modules.orders.fields.updated_at',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fae-7068-811e-add6da57eb89',
                  'dropdown_list_id' => NULL,
                ),
                6 =>
                array(
                  'id' => '977ef5c4-9557-4747-b3f7-c1f50e0ece34',
                  'key' => 'orders_status',
                  'name' => 'status',
                  'type' => 'dropdown',
                  'label' => 'modules.orders.fields.status',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fae-7068-811e-add6da57eb89',
                  'dropdown_list_id' => NULL,
                ),
                7 =>
                array(
                  'id' => '9bc4a3b3-8cb7-431a-a755-bc2bfc61de76',
                  'key' => 'orders_total_amount',
                  'name' => 'total_amount',
                  'type' => 'number',
                  'label' => 'modules.orders.fields.total_amount',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fae-7068-811e-add6da57eb89',
                  'dropdown_list_id' => NULL,
                ),
                8 =>
                array(
                  'id' => '9cd759e7-30b0-411d-af07-26c961896f87',
                  'key' => 'orders_order_number',
                  'name' => 'order_number',
                  'type' => 'textfield',
                  'label' => 'modules.orders.fields.order_number',
                  'readonly' => false,
                  'required' => true,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fae-7068-811e-add6da57eb89',
                  'dropdown_list_id' => NULL,
                ),
                9 =>
                array(
                  'id' => 'a25d241d-8e54-47bd-bf55-1ff97d97167d',
                  'key' => 'orders_due_date',
                  'name' => 'due_date',
                  'type' => 'date',
                  'label' => 'modules.orders.fields.due_date',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fae-7068-811e-add6da57eb89',
                  'dropdown_list_id' => NULL,
                ),
                10 =>
                array(
                  'id' => 'cee913ec-3728-4cd9-9b30-80dad5aec925',
                  'key' => 'orders_created_at',
                  'name' => 'created_at',
                  'type' => 'datetime',
                  'label' => 'modules.orders.fields.created_at',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fae-7068-811e-add6da57eb89',
                  'dropdown_list_id' => NULL,
                ),
                11 =>
                array(
                  'id' => 'd6964b39-7969-4c66-857a-2a10318d0566',
                  'key' => 'orders_opportunity_id',
                  'name' => 'opportunity_id',
                  'type' => 'relationship',
                  'label' => 'modules.orders.fields.opportunity_id',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fae-7068-811e-add6da57eb89',
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
          3 =>
          array(
            'name' => 'accounts_emails',
            'type' => 'one-to-many',
            'label' => 'relationships.accounts_emails',
            'panelHeader' =>
            array(
              0 =>
              array(
                'name' => 'subject',
                'type' => 'textfield',
                'label' => 'modules.emails.fields.subject',
              ),
              1 =>
              array(
                'name' => 'to',
                'type' => 'email',
                'label' => 'modules.emails.fields.to',
              ),
              2 =>
              array(
                'name' => 'status',
                'type' => 'dropdown',
                'label' => 'modules.emails.fields.status',
              ),
              3 =>
              array(
                'name' => 'updated_at',
                'type' => 'datetime',
                'label' => 'modules.emails.fields.updated_at',
              ),
              4 =>
              array(
                'name' => 'created_at',
                'type' => 'datetime',
                'label' => 'modules.emails.fields.created_at',
              ),
            ),
            'relationship' =>
            array(
              'id' => '8a736dca-2ea3-48a1-94bb-1fdababdf5fe',
              'name' => 'accounts_emails',
              'role' => 'parent',
              'side' => 'left',
              'label' => 'relationships.accounts_emails',
              'created_at' => '2026-02-26 12:25:18',
              'join_table' => 'relationship_links',
              'left_class' => 'App\\Models\\Modules\\Account',
              'other_side' => 'right',
              'updated_at' => '2026-02-26 12:25:18',
              'left_module' => 'accounts',
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
                  'id' => '194429e9-0bd8-4aec-a9ff-e66bae9094a0',
                  'key' => 'emails_related_id',
                  'name' => 'related_id',
                  'type' => 'textfield',
                  'label' => 'modules.emails.fields.related_id',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fb5-7067-969c-1102d4f0a419',
                  'dropdown_list_id' => NULL,
                ),
                1 =>
                array(
                  'id' => '5851e83f-8746-4647-b71b-a51bf9982a6a',
                  'key' => 'emails_updated_at',
                  'name' => 'updated_at',
                  'type' => 'datetime',
                  'label' => 'modules.emails.fields.updated_at',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fb5-7067-969c-1102d4f0a419',
                  'dropdown_list_id' => NULL,
                ),
                2 =>
                array(
                  'id' => '78534885-3cd3-435e-9934-cbb59bce23da',
                  'key' => 'emails_status',
                  'name' => 'status',
                  'type' => 'dropdown',
                  'label' => 'modules.emails.fields.status',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fb5-7067-969c-1102d4f0a419',
                  'dropdown_list_id' => NULL,
                ),
                3 =>
                array(
                  'id' => 'cf21102e-cd95-4b74-aabe-76dd6b67929c',
                  'key' => 'emails_subject',
                  'name' => 'subject',
                  'type' => 'textfield',
                  'label' => 'modules.emails.fields.subject',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fb5-7067-969c-1102d4f0a419',
                  'dropdown_list_id' => NULL,
                ),
                4 =>
                array(
                  'id' => 'd25641ab-2f3a-454f-aea4-c21376740be9',
                  'key' => 'emails_to',
                  'name' => 'to',
                  'type' => 'email',
                  'label' => 'modules.emails.fields.to',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fb5-7067-969c-1102d4f0a419',
                  'dropdown_list_id' => NULL,
                ),
                5 =>
                array(
                  'id' => 'd493599a-f86d-4582-9adb-e8260ff44914',
                  'key' => 'emails_created_at',
                  'name' => 'created_at',
                  'type' => 'datetime',
                  'label' => 'modules.emails.fields.created_at',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fb5-7067-969c-1102d4f0a419',
                  'dropdown_list_id' => NULL,
                ),
                6 =>
                array(
                  'id' => 'ee181d3a-9860-44d2-b623-67c035030221',
                  'key' => 'emails_description',
                  'name' => 'description',
                  'type' => 'longtext',
                  'label' => 'modules.emails.fields.description',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fb5-7067-969c-1102d4f0a419',
                  'dropdown_list_id' => NULL,
                ),
                7 =>
                array(
                  'id' => 'fba918c9-b5c2-403e-8b62-32c4f6d80e2c',
                  'key' => 'emails_mailable_class',
                  'name' => 'mailable_class',
                  'type' => 'textfield',
                  'label' => 'modules.emails.fields.mailable_class',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c99e9-1fb5-7067-969c-1102d4f0a419',
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
        'id' => '0f5ba6a1-b842-45e6-a864-dfdcaf92df06',
        'key' => 'accounts_website',
        'name' => 'website',
        'type' => 'textfield',
        'label' => 'modules.accounts.fields.website',
        'readonly' => false,
        'required' => false,
        'sortable' => false,
        'module_id' => '019c99e9-1fa7-711b-853d-329ab73ed199',
        'dropdown_list' => NULL,
        'dropdown_list_id' => NULL,
      ),
      2 =>
      array(
        'id' => '7776deb0-4eb0-493b-b7d8-faef91283174',
        'key' => 'accounts_email',
        'name' => 'email',
        'type' => 'email',
        'label' => 'modules.accounts.fields.email',
        'readonly' => false,
        'required' => false,
        'sortable' => false,
        'module_id' => '019c99e9-1fa7-711b-853d-329ab73ed199',
        'dropdown_list' => NULL,
        'dropdown_list_id' => NULL,
      ),
      3 =>
      array(
        'id' => 'b09fafa7-644f-49e8-bb48-ceb1b4a94daf',
        'key' => 'accounts_phone',
        'name' => 'phone',
        'type' => 'textfield',
        'label' => 'modules.accounts.fields.phone',
        'readonly' => false,
        'required' => false,
        'sortable' => false,
        'module_id' => '019c99e9-1fa7-711b-853d-329ab73ed199',
        'dropdown_list' => NULL,
        'dropdown_list_id' => NULL,
      ),
    ),
  ),
);
