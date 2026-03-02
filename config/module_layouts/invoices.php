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
        'key' => 'invoices_number',
        'name' => 'number',
        'type' => 'number',
        'label' => 'modules.invoices.fields.number',
        'readonly' => false,
        'required' => false,
        'sortable' => false,
        'dropdown_list' => NULL,
      ),
      2 =>
      array(
        'key' => 'invoices_total',
        'name' => 'total',
        'type' => 'number',
        'label' => 'modules.invoices.fields.total',
        'readonly' => false,
        'required' => false,
        'sortable' => false,
        'dropdown_list' => NULL,
      ),
      3 =>
      array(
        'key' => 'invoices_status',
        'name' => 'status',
        'type' => 'dropdown',
        'label' => 'modules.invoices.fields.status',
        'readonly' => false,
        'required' => false,
        'sortable' => false,
        'dropdown_list' => NULL,
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
            'name' => 'number',
            'type' => 'number',
            'label' => 'modules.invoices.fields.number',
            'readonly' => false,
            'required' => true,
          ),
          2 =>
          array(
            'name' => 'status',
            'type' => 'dropdown',
            'label' => 'modules.invoices.fields.status',
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
            'name' => 'subtotal',
            'type' => 'number',
            'label' => 'modules.invoices.fields.subtotal',
            'readonly' => false,
            'required' => true,
          ),
          1 =>
          array(
            'name' => 'tax',
            'type' => 'number',
            'label' => 'modules.invoices.fields.tax',
            'readonly' => false,
            'required' => true,
          ),
          2 =>
          array(
            'name' => 'total',
            'type' => 'number',
            'label' => 'modules.invoices.fields.total',
            'readonly' => false,
            'required' => true,
          ),
          3 =>
          array(
            'name' => 'currency',
            'type' => 'textfield',
            'label' => 'modules.invoices.fields.currency',
            'readonly' => false,
            'required' => true,
          ),
        ),
      ),
      2 =>
      array(
        'name' => 'Dates',
        'layout' =>
        array(
          0 =>
          array(
            'name' => 'due_date',
            'type' => 'date',
            'label' => 'modules.invoices.fields.due_date',
            'readonly' => false,
            'required' => false,
          ),
          1 =>
          array(
            'name' => 'created_at',
            'type' => 'datetime',
            'label' => 'modules.invoices.fields.created_at',
            'readonly' => false,
            'required' => false,
          ),
          2 =>
          array(
            'name' => 'updated_at',
            'type' => 'datetime',
            'label' => 'modules.invoices.fields.updated_at',
            'readonly' => false,
            'required' => false,
          ),
          3 =>
          array(
            'name' => 'issue_date',
            'type' => 'date',
            'label' => 'modules.invoices.fields.issue_date',
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
            'type' => 'longtext',
            'label' => 'modules.invoices.fields.description',
            'readonly' => false,
            'required' => false,
          ),
          1 =>
          array(
            'name' => 'notes',
            'type' => 'longtext',
            'label' => 'modules.invoices.fields.notes',
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
            'name' => 'accounts_invoices',
            'type' => 'one-to-many',
            'label' => 'relationships.accounts_invoices',
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
                'name' => 'email',
                'type' => 'email',
                'label' => 'modules.accounts.fields.email',
              ),
              3 =>
              array(
                'name' => 'website',
                'type' => 'textfield',
                'label' => 'modules.accounts.fields.website',
              ),
            ),
            'relationship' =>
            array(
              'id' => 'dbd413c7-6507-4def-9ad8-a11b712b514e',
              'name' => 'accounts_invoices',
              'role' => 'child',
              'side' => 'right',
              'label' => 'relationships.accounts_invoices',
              'created_at' => '2026-02-26 12:51:55',
              'join_table' => 'relationship_links',
              'left_class' => 'App\\Models\\Modules\\Account',
              'other_side' => 'left',
              'updated_at' => '2026-02-26 12:51:55',
              'left_module' => 'accounts',
              'right_class' => 'App\\Models\\Modules\\Invoice',
              'current_side' => 'right',
              'related_slug' => 'accounts',
              'right_module' => 'invoices',
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
            'name' => 'orders_invoices',
            'type' => 'one-to-one',
            'label' => 'relationships.orders_invoices',
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
                'name' => 'total_amount',
                'type' => 'number',
                'label' => 'modules.orders.fields.total_amount',
              ),
              2 =>
              array(
                'name' => 'status',
                'type' => 'dropdown',
                'label' => 'modules.orders.fields.status',
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
              'id' => '0e6d026f-e7f8-4dc3-9839-64f101bfcacd',
              'name' => 'orders_invoices',
              'role' => 'sibling',
              'side' => 'right',
              'label' => 'relationships.orders_invoices',
              'created_at' => '2026-02-26 12:51:55',
              'join_table' => 'relationship_links',
              'left_class' => 'App\\Models\\Modules\\Order',
              'other_side' => 'left',
              'updated_at' => '2026-02-26 12:51:55',
              'left_module' => 'orders',
              'right_class' => 'App\\Models\\Modules\\Invoice',
              'current_side' => 'right',
              'related_slug' => 'orders',
              'right_module' => 'invoices',
              'related_class' => 'App\\Models\\Modules\\Order',
              'other_id_field' => 'left_id',
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
              'current_id_field' => 'right_id',
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
            'name' => 'contacts_invoices',
            'type' => 'one-to-many',
            'label' => 'relationships.contacts_invoices',
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
            ),
            'relationship' =>
            array(
              'id' => '5e088541-e79b-4da5-b987-6e25e296d099',
              'name' => 'contacts_invoices',
              'role' => 'child',
              'side' => 'right',
              'label' => 'relationships.contacts_invoices',
              'created_at' => '2026-02-26 12:51:55',
              'join_table' => 'relationship_links',
              'left_class' => 'App\\Models\\Modules\\Contact',
              'other_side' => 'left',
              'updated_at' => '2026-02-26 12:51:55',
              'left_module' => 'contacts',
              'right_class' => 'App\\Models\\Modules\\Invoice',
              'current_side' => 'right',
              'related_slug' => 'contacts',
              'right_module' => 'invoices',
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
              'current_id_field' => 'right_id',
              'right_module_key' => NULL,
              'current_module_id' => NULL,
              'relationship_type' => 'one-to-many',
            ),
          ),
          1 =>
          array(
            'name' => 'quotes_invoices',
            'type' => 'one-to-one',
            'label' => 'relationships.quotes_invoices',
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
                'name' => 'total',
                'type' => 'number',
                'label' => 'modules.quotes.fields.total',
              ),
              3 =>
              array(
                'name' => 'status',
                'type' => 'dropdown',
                'label' => 'modules.quotes.fields.status',
              ),
              4 =>
              array(
                'name' => 'number',
                'type' => 'number',
                'label' => 'modules.quotes.fields.number',
              ),
            ),
            'relationship' =>
            array(
              'id' => '9c9fddf4-b60f-4037-beef-8aec36b68b8c',
              'name' => 'quotes_invoices',
              'role' => 'sibling',
              'side' => 'right',
              'label' => 'relationships.quotes_invoices',
              'created_at' => '2026-02-26 12:51:55',
              'join_table' => 'relationship_links',
              'left_class' => 'App\\Models\\Modules\\Quote',
              'other_side' => 'left',
              'updated_at' => '2026-02-26 12:51:55',
              'left_module' => 'quotes',
              'right_class' => 'App\\Models\\Modules\\Invoice',
              'current_side' => 'right',
              'related_slug' => 'quotes',
              'right_module' => 'invoices',
              'related_class' => 'App\\Models\\Modules\\Quote',
              'other_id_field' => 'left_id',
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
              'current_id_field' => 'right_id',
              'right_module_key' => NULL,
              'current_module_id' => NULL,
              'relationship_type' => 'one-to-one',
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
        'key' => 'invoices_number',
        'name' => 'number',
        'type' => 'number',
        'label' => 'modules.invoices.fields.number',
        'readonly' => false,
        'required' => false,
        'sortable' => false,
        'dropdown_list' => NULL,
      ),
      2 =>
      array(
        'key' => 'invoices_total',
        'name' => 'total',
        'type' => 'number',
        'label' => 'modules.invoices.fields.total',
        'readonly' => false,
        'required' => false,
        'sortable' => false,
        'dropdown_list' => NULL,
      ),
      3 =>
      array(
        'key' => 'invoices_due_date',
        'name' => 'due_date',
        'type' => 'date',
        'label' => 'modules.invoices.fields.due_date',
        'readonly' => false,
        'required' => false,
        'sortable' => false,
        'dropdown_list' => NULL,
      ),
      4 =>
      array(
        'key' => 'invoices_status',
        'name' => 'status',
        'type' => 'dropdown',
        'label' => 'modules.invoices.fields.status',
        'readonly' => false,
        'required' => false,
        'sortable' => false,
        'dropdown_list' => NULL,
      ),
    ),
  ),
);
