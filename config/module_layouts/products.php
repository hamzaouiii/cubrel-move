<?php

return array (
  'list' => 
  array (
    'columns' => 
    array (
      0 => 
      array (
        'name' => 'name',
        'type' => 'text',
        'label' => 'modules.defaults.name',
      ),
      1 => 
      array (
        'name' => 'is_active',
        'type' => 'checkbox',
        'label' => 'modules.products.fields.is_active',
      ),
      2 => 
      array (
        'name' => 'price',
        'type' => 'number',
        'label' => 'modules.products.fields.price',
      ),
      3 => 
      array (
        'name' => 'sku',
        'type' => 'text',
        'label' => 'modules.products.fields.sku',
      ),
      4 => 
      array (
        'name' => 'category',
        'type' => 'select',
        'label' => 'modules.products.fields.category',
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
            'type' => 'text',
            'label' => 'modules.defaults.name',
          ),
          1 => 
          array (
            'name' => 'price',
            'type' => 'number',
            'label' => 'modules.products.fields.price',
          ),
          2 => 
          array (
            'name' => 'is_active',
            'type' => 'checkbox',
            'label' => 'modules.products.fields.is_active',
          ),
          3 => 
          array (
            'name' => 'currency',
            'type' => 'select',
            'label' => 'modules.products.fields.currency',
          ),
          4 => 
          array (
            'name' => 'category',
            'type' => 'select',
            'label' => 'modules.products.fields.category',
          ),
          5 => 
          array (
            'name' => 'sku',
            'type' => 'text',
            'label' => 'modules.products.fields.sku',
          ),
          6 => 
          array (
            'name' => 'description',
            'type' => 'longText',
            'label' => 'modules.defaults.description',
          ),
          7 => 
          array (
            'name' => 'created_at',
            'type' => 'datetime',
            'label' => 'modules.defaults.created_at',
          ),
          8 => 
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
            'name' => 'quotes_products',
            'type' => 'many-to-many',
            'label' => 'relationships.quotes_products',
            'fields' => 
            array (
              0 => 
              array (
                'name' => 'name',
                'type' => 'text',
                'label' => 'modules.quotes.fields.name',
              ),
              1 => 
              array (
                'name' => 'valid_until',
                'type' => 'date',
                'label' => 'modules.quotes.fields.valid_until',
              ),
              2 => 
              array (
                'name' => 'total',
                'type' => 'number',
                'label' => 'modules.quotes.fields.total',
              ),
              3 => 
              array (
                'name' => 'status',
                'type' => 'select',
                'label' => 'modules.quotes.fields.status',
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
            'name' => 'opportunities_products',
            'type' => 'many-to-many',
            'label' => 'relationships.opportunities_products',
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
                'name' => 'sales_stage',
                'type' => 'select',
                'label' => 'modules.opportunities.fields.sales_stage',
              ),
              2 => 
              array (
                'name' => 'type',
                'type' => 'select',
                'label' => 'modules.opportunities.fields.type',
              ),
              3 => 
              array (
                'name' => 'probability',
                'type' => 'number',
                'label' => 'modules.opportunities.fields.probability',
              ),
              4 => 
              array (
                'name' => 'expected_close_date',
                'type' => 'date',
                'label' => 'modules.opportunities.fields.expected_close_date',
              ),
            ),
          ),
        ),
      ),
      2 => 
      array (
        'layout' => 
        array (
          0 => 
          array (
            'name' => 'orders_products',
            'type' => 'many-to-many',
            'label' => 'relationships.orders_products',
            'fields' => 
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
                'name' => 'due_date',
                'type' => 'date',
                'label' => 'modules.orders.fields.due_date',
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
        'name' => 'name',
        'type' => 'text',
        'label' => 'modules.defaults.name',
      ),
      1 => 
      array (
        'name' => 'price',
        'type' => 'number',
        'label' => 'modules.products.fields.price',
      ),
      2 => 
      array (
        'name' => 'sku',
        'type' => 'text',
        'label' => 'modules.products.fields.sku',
      ),
    ),
  ),
);
