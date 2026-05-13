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
        'name' => 'order_number',
        'type' => 'text',
        'label' => 'modules.orders.fields.order_number',
      ),
      2 =>
      array(
        'name' => 'status',
        'type' => 'select',
        'label' => 'modules.orders.fields.status',
      ),
      3 =>
      array(
        'name' => 'currency',
        'type' => 'select',
        'label' => 'modules.orders.fields.currency',
      ),
      4 =>
      array(
        'name' => 'due_date',
        'type' => 'date',
        'label' => 'modules.orders.fields.due_date',
      ),
      5 =>
      array(
        'name' => 'total_amount',
        'type' => 'number',
        'label' => 'modules.orders.fields.total_amount',
      ),
      6 =>
       array(
            'name' => 'owner_id',
            'type' => 'record',
            'label' => 'modules.defaults.owner_id',
          ),
      7 =>
      array(
        'name' => 'created_at',
        'type' => 'datetime',
        'label' => 'modules.defaults.created_at',
        'sortable' => true,
      ),
      8 =>
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
            'type' => 'text',
            'label' => 'modules.defaults.name',
            'readonly' => false,
            'required' => true,
            'sortable' => true,
          ),
          1 =>
          array(
            'name' => 'order_number',
            'type' => 'text',
            'label' => 'modules.orders.fields.order_number',
            'readonly' => false,
            'required' => true,
          ),
          2 =>
          array(
            'name' => 'status',
            'type' => 'select',
            'label' => 'modules.orders.fields.status',
            'readonly' => false,
            'required' => false,
          ),
          3 =>
          array(
            'name' => 'total_amount',
            'type' => 'number',
            'label' => 'modules.orders.fields.total_amount',
            'readonly' => false,
            'required' => false,
          ),
          4 =>
          array(
            'name' => 'currency',
            'type' => 'select',
            'label' => 'modules.orders.fields.currency',
            'readonly' => false,
            'required' => false,
          ),
          5 =>
          array(
            'name' => 'description',
            'type' => 'longText',
            'label' => 'modules.defaults.description',
            'readonly' => false,
            'required' => true,
            'sortable' => true,
          ),
          6=>  array(
            'name' => 'owner_id',
            'type' => 'record',
            'label' => 'modules.defaults.owner_id',
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
            'name' => 'due_date',
            'type' => 'date',
            'label' => 'modules.orders.fields.due_date',
            'readonly' => false,
            'required' => false,
          ),
          1 =>
          array(
            'name' => 'order_date',
            'type' => 'date',
            'label' => 'modules.orders.fields.order_date',
            'readonly' => false,
            'required' => false,
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
          3 =>
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
            'fields' =>
            array(
              0 =>
              array(
                'name' => 'name',
                'type' => 'text',
                'label' => 'modules.accounts.fields.name',
                'sortable' => false,
              ),
              1 =>
              array(
                'name' => 'country',
                'type' => 'text',
                'label' => 'modules.accounts.fields.country',
                'sortable' => false,
              ),
              2 =>
              array(
                'name' => 'city',
                'type' => 'text',
                'label' => 'modules.accounts.fields.city',
                'sortable' => false,
              ),
              3 =>
              array(
                'name' => 'website',
                'type' => 'url',
                'label' => 'modules.accounts.fields.website',
                'sortable' => false,
              ),
            ),
          ),
          1 =>
          array(
            'name' => 'orders_invoices',
            'type' => 'one-to-one',
            'label' => 'relationships.orders_invoices',
            'fields' =>
            array(
              0 =>
              array(
                'name' => 'name',
                'type' => 'text',
                'label' => 'modules.invoices.fields.name',
                'sortable' => false,
              ),
              1 =>
              array(
                'name' => 'number',
                'type' => 'number',
                'label' => 'modules.invoices.fields.number',
                'sortable' => false,
              ),
              2 =>
              array(
                'name' => 'status',
                'type' => 'select',
                'label' => 'modules.invoices.fields.status',
                'sortable' => false,
              ),
              3 =>
              array(
                'name' => 'total',
                'type' => 'number',
                'label' => 'modules.invoices.fields.total',
                'sortable' => false,
              ),
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
            'name' => 'deals_orders',
            'type' => 'one-to-many',
            'label' => 'relationships.deals_orders',
            'fields' =>
            array(
              0 =>
              array(
                'name' => 'name',
                'type' => 'text',
                'label' => 'modules.deals.fields.name',
                'sortable' => false,
              ),
              1 =>
              array(
                'name' => 'sales_stage',
                'type' => 'select',
                'label' => 'modules.deals.fields.sales_stage',
                'sortable' => false,
              ),
              2 =>
              array(
                'name' => 'amount',
                'type' => 'text',
                'label' => 'modules.deals.fields.amount',
                'sortable' => false,
              ),
              3 =>
              array(
                'name' => 'probability',
                'type' => 'percentage',
                'label' => 'modules.deals.fields.probability',
                'sortable' => false,
              ),
              4 =>
              array(
                'name' => 'currency',
                'type' => 'select',
                'label' => 'modules.deals.fields.currency',
                'sortable' => false,
              ),
              5 =>
              array(
                'name' => 'type',
                'type' => 'select',
                'label' => 'modules.deals.fields.type',
                'sortable' => false,
              ),
            ),
          ),
          1 =>
          array(
            'name' => 'orders_products',
            'type' => 'many-to-many',
            'label' => 'relationships.orders_products',
            'fields' =>
            array(
              0 =>
              array(
                'name' => 'name',
                'type' => 'text',
                'label' => 'modules.products.fields.name',
                'sortable' => false,
              ),
              1 =>
              array(
                'name' => 'category',
                'type' => 'select',
                'label' => 'modules.products.fields.category',
                'sortable' => false,
              ),
              2 =>
              array(
                'name' => 'is_active',
                'type' => 'checkbox',
                'label' => 'modules.products.fields.is_active',
                'sortable' => false,
              ),
              3 =>
              array(
                'name' => 'currency',
                'type' => 'select',
                'label' => 'modules.products.fields.currency',
                'sortable' => false,
              ),
              4 =>
              array(
                'name' => 'price',
                'type' => 'number',
                'label' => 'modules.products.fields.price',
                'sortable' => false,
              ),
              5 =>
              array(
                'name' => 'sku',
                'type' => 'text',
                'label' => 'modules.products.fields.sku',
                'sortable' => false,
              ),
            ),
          ),
        ),
      ),
    ),
  ),
  'linkingPanel' =>
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
        'name' => 'order_number',
        'type' => 'text',
        'label' => 'modules.orders.fields.order_number',
      ),
      2 =>
      array(
        'name' => 'total_amount',
        'type' => 'number',
        'label' => 'modules.orders.fields.total_amount',
      ),
      3 =>
      array(
        'name' => 'status',
        'type' => 'select',
        'label' => 'modules.orders.fields.status',
      ),
    ),
  ),
);
