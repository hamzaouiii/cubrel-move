<?php

return array(
  'list' =>
  array(
    'columns' =>
    array(
      0 =>
      array(
        'key' => 'orders_order_number',
        'name' => 'order_number',
        'type' => 'text',
        'label' => 'modules.orders.fields.order_number',
        'readonly' => false,
        'required' => true,
        'sortable' => false,
        'dropdown_list' => NULL,
      ),
      1 =>
      array(
        'key' => 'orders_total_amount',
        'name' => 'total_amount',
        'type' => 'number',
        'label' => 'modules.orders.fields.total_amount',
        'readonly' => false,
        'required' => false,
        'sortable' => false,
        'dropdown_list' => NULL,
      ),
      2 =>
      array(
        'key' => 'orders_status',
        'name' => 'status',
        'type' => 'select',
        'label' => 'modules.orders.fields.status',
        'readonly' => false,
        'required' => false,
        'sortable' => false,
        'dropdown_list' => NULL,
      ),
      3 =>
      array(
        'key' => 'orders_order_date',
        'name' => 'order_date',
        'type' => 'date',
        'label' => 'modules.orders.fields.order_date',
        'readonly' => false,
        'required' => false,
        'sortable' => false,
        'dropdown_list' => NULL,
      ),
      4 =>
      array(
        'key' => 'orders_due_date',
        'name' => 'due_date',
        'type' => 'date',
        'label' => 'modules.orders.fields.due_date',
        'readonly' => false,
        'required' => false,
        'sortable' => false,
        'dropdown_list' => NULL,
      ),
      5 =>
      array(
        'name' => 'created_at',
        'type' => 'datetime',
        'label' => 'modules.defaults.created_at',
        'sortable' => true,
      ),
      6 =>
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
        'name' => 'General',
        'layout' =>
        array(
          0 =>
          array(
            'name' => 'order_number',
            'type' => 'text',
            'label' => 'modules.orders.fields.order_number',
            'readonly' => false,
            'required' => true,
          ),
          1 =>
          array(
            'name' => 'description',
            'type' => 'longtext',
            'label' => 'modules.orders.fields.description',
            'readonly' => false,
            'required' => false,
          ),
          2 =>
          array(
            'name' => 'total_amount',
            'type' => 'number',
            'label' => 'modules.orders.fields.total_amount',
            'readonly' => false,
            'required' => false,
          ),
          3 =>
          array(
            'name' => 'currency',
            'type' => 'text',
            'label' => 'modules.orders.fields.currency',
            'readonly' => false,
            'required' => false,
          ),
          4 =>
          array(
            'name' => 'status',
            'type' => 'select',
            'label' => 'modules.orders.fields.status',
            'readonly' => false,
            'required' => false,
          ),
          5 =>
          array(
            'name' => 'order_date',
            'type' => 'date',
            'label' => 'modules.orders.fields.order_date',
            'readonly' => false,
            'required' => false,
          ),
          6 =>
          array(
            'name' => 'due_date',
            'type' => 'date',
            'label' => 'modules.orders.fields.due_date',
            'readonly' => false,
            'required' => false,
          ),
          7 =>
          array(
            'name' => 'assigned_user_id',
            'type' => 'relationship',
            'label' => 'modules.orders.fields.assigned_user_id',
            'readonly' => false,
            'required' => false,
          ),
          8 =>
          array(
            'name' => 'created_at',
            'type' => 'datetime',
            'label' => 'modules.defaults.created_at',
            'readonly' => true,
            'required' => true,
            'sortable' => true,
          ),
          9 =>
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
            'name' => 'accounts_orders',
            'type' => 'one-to-many',
            'label' => 'relationships.accounts_orders',
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
              'id' => 'cef9c14e-d79a-4853-95fa-04452f7a6e3d',
              'name' => 'accounts_orders',
              'role' => 'child',
              'side' => 'right',
              'label' => 'relationships.accounts_orders',
              'created_at' => '2026-02-26 12:51:55',
              'join_table' => 'relationship_links',
              'left_class' => 'App\\Models\\Modules\\Account',
              'other_side' => 'left',
              'updated_at' => '2026-02-26 12:51:55',
              'left_module' => 'accounts',
              'right_class' => 'App\\Models\\Modules\\Order',
              'current_side' => 'right',
              'related_slug' => 'accounts',
              'right_module' => 'orders',
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
            'name' => 'orders_invoices',
            'type' => 'one-to-one',
            'label' => 'relationships.orders_invoices',
            'panelHeader' =>
            array(
              0 =>
              array(
                'name' => 'name',
                'type' => 'text',
                'label' => 'modules.invoices.fields.name',
              ),
              1 =>
              array(
                'name' => 'number',
                'type' => 'number',
                'label' => 'modules.invoices.fields.number',
              ),
              2 =>
              array(
                'name' => 'total',
                'type' => 'number',
                'label' => 'modules.invoices.fields.total',
              ),
              3 =>
              array(
                'name' => 'status',
                'type' => 'select',
                'label' => 'modules.invoices.fields.status',
              ),
            ),
            'relationship' =>
            array(
              'id' => '0e6d026f-e7f8-4dc3-9839-64f101bfcacd',
              'name' => 'orders_invoices',
              'role' => 'sibling',
              'side' => 'left',
              'label' => 'relationships.orders_invoices',
              'created_at' => '2026-02-26 12:51:55',
              'join_table' => 'relationship_links',
              'left_class' => 'App\\Models\\Modules\\Order',
              'other_side' => 'right',
              'updated_at' => '2026-02-26 12:51:55',
              'left_module' => 'orders',
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
                array(
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
                array(
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
                array(
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
                array(
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
                array(
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
                array(
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
                array(
                  'id' => '67f3ba7f-45c9-4e1d-b98f-bd9d75297a7c',
                  'key' => 'invoices_quote_id',
                  'name' => 'quote_id',
                  'type' => 'select',
                  'label' => 'modules.invoices.fields.quote_id',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d2d-7006-ad73-f2ae19efe0ec',
                  'dropdown_list_id' => NULL,
                ),
                8 =>
                array(
                  'id' => '755025a7-6522-4d65-a975-3e25b92a13c9',
                  'key' => 'invoices_status',
                  'name' => 'status',
                  'type' => 'select',
                  'label' => 'modules.invoices.fields.status',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d2d-7006-ad73-f2ae19efe0ec',
                  'dropdown_list_id' => NULL,
                ),
                9 =>
                array(
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
                array(
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
                array(
                  'id' => 'c9036f17-ab41-45bf-965b-2a1d56d0e648',
                  'key' => 'invoices_currency',
                  'name' => 'currency',
                  'type' => 'select',
                  'label' => 'modules.invoices.fields.currency',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d2d-7006-ad73-f2ae19efe0ec',
                  'dropdown_list_id' => NULL,
                ),
                12 =>
                array(
                  'id' => 'd8c0ad44-3a99-44f2-99b4-68862cdccc80',
                  'key' => 'invoices_name',
                  'name' => 'name',
                  'type' => 'text',
                  'label' => 'modules.invoices.fields.name',
                  'readonly' => false,
                  'required' => true,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d2d-7006-ad73-f2ae19efe0ec',
                  'dropdown_list_id' => NULL,
                ),
                13 =>
                array(
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
                array(
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
                array(
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
              'relationship_type' => 'one-to-one',
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
            'name' => 'opportunities_orders',
            'type' => 'one-to-many',
            'label' => 'relationships.opportunities_orders',
            'panelHeader' =>
            array(
              0 =>
              array(
                'name' => 'name',
                'type' => 'text',
                'label' => 'modules.opportunities.fields.name',
              ),
              1 =>
              array(
                'name' => 'type',
                'type' => 'select',
                'label' => 'modules.opportunities.fields.type',
              ),
              2 =>
              array(
                'name' => 'probability',
                'type' => 'number',
                'label' => 'modules.opportunities.fields.probability',
              ),
              3 =>
              array(
                'name' => 'sales_stage',
                'type' => 'select',
                'label' => 'modules.opportunities.fields.sales_stage',
              ),
              4 =>
              array(
                'name' => 'amount',
                'type' => 'text',
                'label' => 'modules.opportunities.fields.amount',
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
              'id' => 'b152ec23-5394-4f5e-b115-97c08e1fd307',
              'name' => 'opportunities_orders',
              'role' => 'child',
              'side' => 'right',
              'label' => 'relationships.opportunities_orders',
              'created_at' => '2026-02-26 12:51:55',
              'join_table' => 'relationship_links',
              'left_class' => 'App\\Models\\Modules\\Opportunity',
              'other_side' => 'left',
              'updated_at' => '2026-02-26 12:51:55',
              'left_module' => 'opportunities',
              'right_class' => 'App\\Models\\Modules\\Order',
              'current_side' => 'right',
              'related_slug' => 'opportunities',
              'right_module' => 'orders',
              'related_class' => 'App\\Models\\Modules\\Opportunity',
              'other_id_field' => 'left_id',
              'related_fields' =>
              array(
                0 =>
                array(
                  'id' => '19c0a630-f246-4f21-9d9e-46b28d3b7fd3',
                  'key' => 'opportunities_name',
                  'name' => 'name',
                  'type' => 'text',
                  'label' => 'modules.opportunities.fields.name',
                  'readonly' => false,
                  'required' => true,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d27-7102-ad87-fcb10376ce88',
                  'dropdown_list_id' => NULL,
                ),
                1 =>
                array(
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
                array(
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
                array(
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
                array(
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
                array(
                  'id' => '8d918237-d5cf-4408-9025-d02e7a8e98dc',
                  'key' => 'opportunities_type',
                  'name' => 'type',
                  'type' => 'select',
                  'label' => 'modules.opportunities.fields.type',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d27-7102-ad87-fcb10376ce88',
                  'dropdown_list_id' => NULL,
                ),
                6 =>
                array(
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
                array(
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
                array(
                  'id' => 'b8c0af5e-e42f-4938-bfa5-2ad2c52316dc',
                  'key' => 'opportunities_amount',
                  'name' => 'amount',
                  'type' => 'text',
                  'label' => 'modules.opportunities.fields.amount',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d27-7102-ad87-fcb10376ce88',
                  'dropdown_list_id' => NULL,
                ),
                9 =>
                array(
                  'id' => 'eb12e7d3-5033-42c4-a8c9-eb0f3ab2dc06',
                  'key' => 'opportunities_currency',
                  'name' => 'currency',
                  'type' => 'select',
                  'label' => 'modules.opportunities.fields.currency',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d27-7102-ad87-fcb10376ce88',
                  'dropdown_list_id' => NULL,
                ),
                10 =>
                array(
                  'id' => 'ecda194e-44e9-440e-baf3-199be52e011a',
                  'key' => 'opportunities_sales_stage',
                  'name' => 'sales_stage',
                  'type' => 'select',
                  'label' => 'modules.opportunities.fields.sales_stage',
                  'readonly' => false,
                  'required' => false,
                  'sortable' => false,
                  'module_id' => '019c9a01-7d27-7102-ad87-fcb10376ce88',
                  'dropdown_list_id' => NULL,
                ),
                11 =>
                array(
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
              'relationship_type' => 'one-to-many',
            ),
          ),
          1 =>
          array(
            'name' => 'orders_products',
            'type' => 'many-to-many',
            'label' => 'relationships.orders_products',
            'panelHeader' =>
            array(
              0 =>
              array(
                'name' => 'name',
                'type' => 'text',
                'label' => 'modules.products.fields.name',
              ),
              1 =>
              array(
                'name' => 'sku',
                'type' => 'text',
                'label' => 'modules.products.fields.sku',
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
                'type' => 'text',
                'label' => 'modules.products.fields.category',
              ),
              4 =>
              array(
                'name' => 'is_active',
                'type' => 'checkbox',
                'label' => 'modules.products.fields.is_active',
              ),
            ),
            'relationship' =>
            array(
              'id' => '93f88383-aae3-44a1-9476-3525b27e902c',
              'name' => 'orders_products',
              'role' => 'related',
              'side' => 'left',
              'label' => 'relationships.orders_products',
              'created_at' => '2026-02-26 12:51:55',
              'join_table' => 'relationship_links',
              'left_class' => 'App\\Models\\Modules\\Order',
              'other_side' => 'right',
              'updated_at' => '2026-02-26 12:51:55',
              'left_module' => 'orders',
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
                  'type' => 'text',
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
                  'type' => 'text',
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
                  'type' => 'select',
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
                  'type' => 'text',
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
        'key' => 'orders_order_number',
        'name' => 'order_number',
        'type' => 'text',
        'label' => 'modules.orders.fields.order_number',
        'readonly' => false,
        'required' => true,
        'sortable' => false,
        'dropdown_list' => NULL,
      ),
      1 =>
      array(
        'key' => 'orders_status',
        'name' => 'status',
        'type' => 'select',
        'label' => 'modules.orders.fields.status',
        'readonly' => false,
        'required' => false,
        'sortable' => false,
        'dropdown_list' => NULL,
      ),
      2 =>
      array(
        'key' => 'orders_total_amount',
        'name' => 'total_amount',
        'type' => 'number',
        'label' => 'modules.orders.fields.total_amount',
        'readonly' => false,
        'required' => false,
        'sortable' => false,
        'dropdown_list' => NULL,
      ),
      3 =>
      array(
        'key' => 'orders_order_date',
        'name' => 'order_date',
        'type' => 'date',
        'label' => 'modules.orders.fields.order_date',
        'readonly' => false,
        'required' => false,
        'sortable' => false,
        'dropdown_list' => NULL,
      ),
      4 =>
      array(
        'key' => 'orders_due_date',
        'name' => 'due_date',
        'type' => 'date',
        'label' => 'modules.orders.fields.due_date',
        'readonly' => false,
        'required' => false,
        'sortable' => false,
        'dropdown_list' => NULL,
      ),
    ),
  ),
);
