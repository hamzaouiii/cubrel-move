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
        'key' => 'products_price',
        'name' => 'price',
        'type' => 'number',
        'label' => 'modules.products.fields.price',
        'readonly' => false,
        'required' => false,
        'sortable' => false,
        'dropdown_list' => NULL,
      ),
      2 =>
      array(
        'key' => 'products_sku',
        'name' => 'sku',
        'type' => 'text',
        'label' => 'modules.products.fields.sku',
        'readonly' => false,
        'required' => false,
        'sortable' => false,
        'dropdown_list' => NULL,
      ),
      3 =>
      array(
        'key' => 'products_category',
        'name' => 'category',
        'type' => 'select',
        'label' => 'modules.products.fields.category',
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
        'name' => 'Card',
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
            'name' => 'price',
            'type' => 'number',
            'label' => 'modules.products.fields.price',
            'readonly' => false,
            'required' => false,
          ),
          2 =>
          array(
            'name' => 'is_active',
            'type' => 'checkbox',
            'label' => 'modules.products.fields.is_active',
            'readonly' => false,
            'required' => false,
          ),
          3 =>
          array(
            'name' => 'currency',
            'type' => 'select',
            'label' => 'modules.products.fields.currency',
            'readonly' => false,
            'required' => false,
          ),
          4 =>
          array(
            'name' => 'category',
            'type' => 'select',
            'label' => 'modules.products.fields.category',
            'readonly' => false,
            'required' => false,
          ),
          5 =>
          array(
            'name' => 'sku',
            'type' => 'text',
            'label' => 'modules.products.fields.sku',
            'readonly' => false,
            'required' => false,
          ),
          6 =>
          array(
            'name' => 'description',
            'type' => 'longText',
            'label' => 'modules.defaults.description',
            'readonly' => false,
            'required' => true,
            'sortable' => true,
          ),
          7 =>
          array(
            'name' => 'created_at',
            'type' => 'datetime',
            'label' => 'modules.defaults.created_at',
            'readonly' => true,
            'required' => true,
            'sortable' => true,
          ),
          8 =>
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
            'name' => 'quotes_products',
            'type' => 'many-to-many',
            'label' => 'relationships.quotes_products',
            'panelHeader' =>
            array(
              0 =>
              array(
                'name' => 'name',
                'type' => 'text',
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
                'type' => 'select',
                'label' => 'modules.quotes.fields.status',
              ),
            ),
            'relationship' =>
            array(
              'id' => '47bd1cc8-3f20-4d24-a50a-4f137451ae4b',
              'name' => 'quotes_products',
              'role' => 'related',
              'side' => 'right',
              'label' => 'relationships.quotes_products',
              'created_at' => '2026-02-26 12:51:55',
              'join_table' => 'relationship_links',
              'left_class' => 'App\\Models\\Modules\\Quote',
              'other_side' => 'left',
              'updated_at' => '2026-02-26 12:51:55',
              'left_module' => 'quotes',
              'right_class' => 'App\\Models\\Modules\\Product',
              'current_side' => 'right',
              'related_slug' => 'quotes',
              'right_module' => 'products',
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
                  'type' => 'select',
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
                  'type' => 'text',
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
                  'type' => 'select',
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
              'relationship_type' => 'many-to-many',
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
                'type' => 'text',
                'label' => 'modules.opportunities.fields.name',
              ),
              1 =>
              array(
                'name' => 'sales_stage',
                'type' => 'select',
                'label' => 'modules.opportunities.fields.sales_stage',
              ),
              2 =>
              array(
                'name' => 'type',
                'type' => 'select',
                'label' => 'modules.opportunities.fields.type',
              ),
              3 =>
              array(
                'name' => 'probability',
                'type' => 'number',
                'label' => 'modules.opportunities.fields.probability',
              ),
              4 =>
              array(
                'name' => 'expected_close_date',
                'type' => 'date',
                'label' => 'modules.opportunities.fields.expected_close_date',
              ),
            ),
            'relationship' =>
            array(
              'id' => '76a15616-9c0c-4725-8964-9a2ece25ca60',
              'name' => 'opportunities_products',
              'role' => 'related',
              'side' => 'right',
              'label' => 'relationships.opportunities_products',
              'created_at' => '2026-02-26 12:51:55',
              'join_table' => 'relationship_links',
              'left_class' => 'App\\Models\\Modules\\Opportunity',
              'other_side' => 'left',
              'updated_at' => '2026-02-26 12:51:55',
              'left_module' => 'opportunities',
              'right_class' => 'App\\Models\\Modules\\Product',
              'current_side' => 'right',
              'related_slug' => 'opportunities',
              'right_module' => 'products',
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
              'relationship_type' => 'many-to-many',
            ),
          ),
        ),
      ),
      2 =>
      array(
        'layout' =>
        array(
          0 =>
          array(
            'name' => 'orders_products',
            'type' => 'many-to-many',
            'label' => 'relationships.orders_products',
            'panelHeader' =>
            array(
              0 =>
              array(
                'name' => 'order_number',
                'type' => 'text',
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
                'name' => 'due_date',
                'type' => 'date',
                'label' => 'modules.orders.fields.due_date',
              ),
            ),
            'relationship' =>
            array(
              'id' => '93f88383-aae3-44a1-9476-3525b27e902c',
              'name' => 'orders_products',
              'role' => 'related',
              'side' => 'right',
              'label' => 'relationships.orders_products',
              'created_at' => '2026-02-26 12:51:55',
              'join_table' => 'relationship_links',
              'left_class' => 'App\\Models\\Modules\\Order',
              'other_side' => 'left',
              'updated_at' => '2026-02-26 12:51:55',
              'left_module' => 'orders',
              'right_class' => 'App\\Models\\Modules\\Product',
              'current_side' => 'right',
              'related_slug' => 'orders',
              'right_module' => 'products',
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
                  'type' => 'select',
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
                  'type' => 'text',
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
                  'type' => 'select',
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
        'name' => 'name',
        'type' => 'text',
        'label' => 'modules.defaults.name',
        'sortable' => true,
      ),
      1 =>
      array(
        'key' => 'products_price',
        'name' => 'price',
        'type' => 'number',
        'label' => 'modules.products.fields.price',
        'readonly' => false,
        'required' => false,
        'sortable' => false,
        'dropdown_list' => NULL,
      ),
      2 =>
      array(
        'key' => 'products_sku',
        'name' => 'sku',
        'type' => 'text',
        'label' => 'modules.products.fields.sku',
        'readonly' => false,
        'required' => false,
        'sortable' => false,
        'dropdown_list' => NULL,
      ),
    ),
  ),
);
