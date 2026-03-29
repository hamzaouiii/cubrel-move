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
        'label' => 'modules.opportunities.fields.name',
      ),
      1 =>
      array(
        'name' => 'expected_close_date',
        'type' => 'date',
        'label' => 'modules.opportunities.fields.expected_close_date',
      ),
      2 =>
      array(
        'name' => 'type',
        'type' => 'select',
        'label' => 'modules.opportunities.fields.type',
      ),
      3 =>
      array(
        'name' => 'amount',
        'type' => 'text',
        'label' => 'modules.opportunities.fields.amount',
      ),
      4 =>
      array(
        'name' => 'sales_stage',
        'type' => 'select',
        'label' => 'modules.opportunities.fields.sales_stage',
      ),
      5 =>
      array(
        'name' => 'probability',
        'type' => 'percentage',
        'label' => 'modules.opportunities.fields.probability',
      ),
      6 =>
      array(
        'name' => 'updated_at',
        'type' => 'datetime',
        'label' => 'modules.opportunities.fields.updated_at',
      ),
      7 =>
      array(
        'name' => 'created_at',
        'type' => 'datetime',
        'label' => 'modules.opportunities.fields.created_at',
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
            'type' => 'text',
            'label' => 'modules.defaults.name',
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
            'type' => 'text',
            'label' => 'modules.opportunities.fields.amount',
          ),
          1 =>
          array(
            'name' => 'currency',
            'type' => 'text',
            'label' => 'modules.opportunities.fields.currency',
          ),
          2 =>
          array(
            'name' => 'probability',
            'type' => 'percentage',
            'label' => 'modules.opportunities.fields.probability',
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
          ),
          1 =>
          array(
            'name' => 'updated_at',
            'type' => 'datetime',
            'label' => 'modules.defaults.updated_at',
          ),
          2 =>
          array(
            'name' => 'created_at',
            'type' => 'datetime',
            'label' => 'modules.defaults.created_at',
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
            'fields' =>
            array(
              0 =>
              array(
                'name' => 'name',
                'type' => 'text',
                'label' => 'modules.accounts.fields.name',
              ),
              1 =>
              array(
                'name' => 'phone',
                'type' => 'phone',
                'label' => 'modules.accounts.fields.phone',
              ),
              2 =>
              array(
                'name' => 'website',
                'type' => 'url',
                'label' => 'modules.accounts.fields.website',
              ),
              3 =>
              array(
                'name' => 'email',
                'type' => 'email',
                'label' => 'modules.accounts.fields.email',
              ),
            ),
          ),
          1 =>
          array(
            'name' => 'opportunities_contacts',
            'type' => 'many-to-many',
            'label' => 'relationships.opportunities_contacts',
            'fields' =>
            array(
              0 =>
              array(
                'name' => 'name',
                'type' => 'text',
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
                'type' => 'phone',
                'label' => 'modules.contacts.fields.phone',
              ),
              3 =>
              array(
                'name' => 'position',
                'type' => 'text',
                'label' => 'modules.contacts.fields.position',
              ),
              4 =>
              array(
                'name' => 'created_at',
                'type' => 'datetime',
                'label' => 'modules.contacts.fields.created_at',
              ),
            ),
          ),
          2 =>
          array(
            'name' => 'opportunities_quotes',
            'type' => 'one-to-many',
            'label' => 'relationships.opportunities_quotes',
            'fields' =>
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
                'type' => 'select',
                'label' => 'modules.quotes.fields.status',
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
            'name' => 'opportunities_products',
            'type' => 'many-to-many',
            'label' => 'relationships.opportunities_products',
            'fields' =>
            array(
              0 =>
              array(
                'name' => 'name',
                'type' => 'text',
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
                'type' => 'select',
                'label' => 'modules.products.fields.category',
              ),
              4 =>
              array(
                'name' => 'sku',
                'type' => 'text',
                'label' => 'modules.products.fields.sku',
              ),
            ),
          ),
          1 =>
          array(
            'name' => 'opportunities_orders',
            'type' => 'one-to-many',
            'label' => 'relationships.opportunities_orders',
            'fields' =>
            array(
              0 =>
              array(
                'name' => 'order_number',
                'type' => 'text',
                'label' => 'modules.orders.fields.order_number',
              ),
              1 =>
              array(
                'name' => 'status',
                'type' => 'select',
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
      ),
      1 =>
      array(
        'name' => 'expected_close_date',
        'type' => 'date',
        'label' => 'modules.opportunities.fields.expected_close_date',
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
        'type' => 'percentage',
        'label' => 'modules.opportunities.fields.probability',
      ),
      4 =>
      array(
        'name' => 'amount',
        'type' => 'text',
        'label' => 'modules.opportunities.fields.amount',
      ),
      5 =>
      array(
        'name' => 'sales_stage',
        'type' => 'select',
        'label' => 'modules.opportunities.fields.sales_stage',
      ),
      6 =>
      array(
        'name' => 'currency',
        'type' => 'text',
        'label' => 'modules.opportunities.fields.currency',
      ),
    ),
  ),
);
