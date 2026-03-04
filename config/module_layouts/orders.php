<?php

return array (
  'list' => 
  array (
    'columns' => 
    array (
      0 => 
      array (
        'name' => 'order_number',
        'type' => 'text',
        'label' => 'modules.orders.fields.order_number',
      ),
      1 => 
      array (
        'name' => 'total_amount',
        'type' => 'number',
        'label' => 'modules.orders.fields.total_amount',
      ),
      2 => 
      array (
        'name' => 'status',
        'type' => 'select',
        'label' => 'modules.orders.fields.status',
      ),
      3 => 
      array (
        'name' => 'order_date',
        'type' => 'date',
        'label' => 'modules.orders.fields.order_date',
      ),
      4 => 
      array (
        'name' => 'due_date',
        'type' => 'date',
        'label' => 'modules.orders.fields.due_date',
      ),
      5 => 
      array (
        'name' => 'created_at',
        'type' => 'datetime',
        'label' => 'modules.defaults.created_at',
      ),
      6 => 
      array (
        'name' => 'updated_at',
        'type' => 'datetime',
        'label' => 'modules.defaults.updated_at',
      ),
    ),
  ),
  'record' => 
  array (
    'sections' => 
    array (
      0 => 
      array (
        'name' => 'General',
        'layout' => 
        array (
          0 => 
          array (
            'name' => 'order_number',
            'type' => 'text',
            'label' => 'modules.orders.fields.order_number',
          ),
          1 => 
          array (
            'name' => 'description',
            'type' => 'longtext',
            'label' => 'modules.orders.fields.description',
          ),
          2 => 
          array (
            'name' => 'total_amount',
            'type' => 'number',
            'label' => 'modules.orders.fields.total_amount',
          ),
          3 => 
          array (
            'name' => 'currency',
            'type' => 'text',
            'label' => 'modules.orders.fields.currency',
          ),
          4 => 
          array (
            'name' => 'status',
            'type' => 'select',
            'label' => 'modules.orders.fields.status',
          ),
          5 => 
          array (
            'name' => 'order_date',
            'type' => 'date',
            'label' => 'modules.orders.fields.order_date',
          ),
          6 => 
          array (
            'name' => 'due_date',
            'type' => 'date',
            'label' => 'modules.orders.fields.due_date',
          ),
          7 => 
          array (
            'name' => 'assigned_user_id',
            'type' => 'relationship',
            'label' => 'modules.orders.fields.assigned_user_id',
          ),
          8 => 
          array (
            'name' => 'created_at',
            'type' => 'datetime',
            'label' => 'modules.defaults.created_at',
          ),
          9 => 
          array (
            'name' => 'updated_at',
            'type' => 'datetime',
            'label' => 'modules.defaults.updated_at',
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
            'name' => 'accounts_orders',
            'type' => 'one-to-many',
            'label' => 'relationships.accounts_orders',
            'fields' => 
            array (
              0 => 
              array (
                'name' => 'name',
                'type' => 'text',
                'label' => 'modules.accounts.fields.name',
              ),
              1 => 
              array (
                'name' => 'email',
                'type' => 'email',
                'label' => 'modules.accounts.fields.email',
              ),
              2 => 
              array (
                'name' => 'phone',
                'type' => 'text',
                'label' => 'modules.accounts.fields.phone',
              ),
              3 => 
              array (
                'name' => 'website',
                'type' => 'text',
                'label' => 'modules.accounts.fields.website',
              ),
            ),
          ),
          1 => 
          array (
            'name' => 'orders_invoices',
            'type' => 'one-to-one',
            'label' => 'relationships.orders_invoices',
            'fields' => 
            array (
              0 => 
              array (
                'name' => 'name',
                'type' => 'text',
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
                'name' => 'status',
                'type' => 'select',
                'label' => 'modules.invoices.fields.status',
              ),
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
            'name' => 'opportunities_orders',
            'type' => 'one-to-many',
            'label' => 'relationships.opportunities_orders',
            'fields' => 
            array (
              0 => 
              array (
                'name' => 'name',
                'type' => 'text',
                'label' => 'modules.opportunities.fields.name',
              ),
              1 => 
              array (
                'name' => 'type',
                'type' => 'select',
                'label' => 'modules.opportunities.fields.type',
              ),
              2 => 
              array (
                'name' => 'probability',
                'type' => 'number',
                'label' => 'modules.opportunities.fields.probability',
              ),
              3 => 
              array (
                'name' => 'sales_stage',
                'type' => 'select',
                'label' => 'modules.opportunities.fields.sales_stage',
              ),
              4 => 
              array (
                'name' => 'amount',
                'type' => 'text',
                'label' => 'modules.opportunities.fields.amount',
              ),
              5 => 
              array (
                'name' => 'expected_close_date',
                'type' => 'date',
                'label' => 'modules.opportunities.fields.expected_close_date',
              ),
            ),
          ),
          1 => 
          array (
            'name' => 'orders_products',
            'type' => 'many-to-many',
            'label' => 'relationships.orders_products',
            'fields' => 
            array (
              0 => 
              array (
                'name' => 'name',
                'type' => 'text',
                'label' => 'modules.products.fields.name',
              ),
              1 => 
              array (
                'name' => 'sku',
                'type' => 'text',
                'label' => 'modules.products.fields.sku',
              ),
              2 => 
              array (
                'name' => 'price',
                'type' => 'number',
                'label' => 'modules.products.fields.price',
              ),
              3 => 
              array (
                'name' => 'category',
                'type' => 'text',
                'label' => 'modules.products.fields.category',
              ),
              4 => 
              array (
                'name' => 'is_active',
                'type' => 'checkbox',
                'label' => 'modules.products.fields.is_active',
              ),
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
        'name' => 'order_number',
        'type' => 'text',
        'label' => 'modules.orders.fields.order_number',
      ),
      1 => 
      array (
        'name' => 'status',
        'type' => 'select',
        'label' => 'modules.orders.fields.status',
      ),
      2 => 
      array (
        'name' => 'total_amount',
        'type' => 'number',
        'label' => 'modules.orders.fields.total_amount',
      ),
      3 => 
      array (
        'name' => 'order_date',
        'type' => 'date',
        'label' => 'modules.orders.fields.order_date',
      ),
      4 => 
      array (
        'name' => 'due_date',
        'type' => 'date',
        'label' => 'modules.orders.fields.due_date',
      ),
    ),
  ),
);
