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
      ),
      1 =>
      array(
        'name' => 'number',
        'type' => 'number',
        'label' => 'modules.quotes.fields.number',
      ),
      2 =>
      array(
        'name' => 'valid_until',
        'type' => 'date',
        'label' => 'modules.quotes.fields.valid_until',
      ),
      3 =>
      array(
        'name' => 'status',
        'type' => 'select',
        'label' => 'modules.quotes.fields.status',
      ),
      4 =>
      array(
        'name' => 'total',
        'type' => 'number',
        'label' => 'modules.quotes.fields.total',
      ),
      5 =>
      array(
        'name' => 'created_at',
        'type' => 'datetime',
        'label' => 'modules.defaults.created_at',
      ),
      6 =>
      array(
        'name' => 'updated_at',
        'type' => 'datetime',
        'label' => 'modules.defaults.updated_at',
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
            'name' => 'description',
            'type' => 'longtext',
            'label' => 'modules.quotes.fields.description',
          ),
          2 =>
          array(
            'name' => 'status',
            'type' => 'select',
            'label' => 'modules.quotes.fields.status',
          ),
          3 =>
          array(
            'name' => 'number',
            'type' => 'number',
            'label' => 'modules.quotes.fields.number',
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
            'label' => 'modules.quotes.fields.subtotal',
          ),
          1 =>
          array(
            'name' => 'tax',
            'type' => 'number',
            'label' => 'modules.quotes.fields.tax',
          ),
          2 =>
          array(
            'name' => 'currency',
            'type' => 'text',
            'label' => 'modules.quotes.fields.currency',
          ),
          3 =>
          array(
            'name' => 'total',
            'type' => 'number',
            'label' => 'modules.quotes.fields.total',
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
            'name' => 'valid_until',
            'type' => 'date',
            'label' => 'modules.quotes.fields.valid_until',
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
            'name' => 'accounts_quotes',
            'type' => 'one-to-many',
            'label' => 'relationships.accounts_quotes',
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
                'name' => 'email',
                'type' => 'email',
                'label' => 'modules.accounts.fields.email',
              ),
              2 =>
              array(
                'name' => 'website',
                'type' => 'url',
                'label' => 'modules.accounts.fields.website',
              ),
              3 =>
              array(
                'name' => 'phone',
                'type' => 'phone',
                'label' => 'modules.accounts.fields.phone',
              ),
            ),
          ),
          1 =>
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
                'name' => 'probability',
                'type' => 'number',
                'label' => 'modules.opportunities.fields.probability',
              ),
              4 =>
              array(
                'name' => 'sales_stage',
                'type' => 'select',
                'label' => 'modules.opportunities.fields.sales_stage',
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
            'name' => 'quotes_products',
            'type' => 'many-to-many',
            'label' => 'relationships.quotes_products',
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
                'name' => 'sku',
                'type' => 'text',
                'label' => 'modules.products.fields.sku',
              ),
              2 =>
              array(
                'name' => 'category',
                'type' => 'text',
                'label' => 'modules.products.fields.category',
              ),
              3 =>
              array(
                'name' => 'is_active',
                'type' => 'checkbox',
                'label' => 'modules.products.fields.is_active',
              ),
              4 =>
              array(
                'name' => 'price',
                'type' => 'number',
                'label' => 'modules.products.fields.price',
              ),
            ),
          ),
          1 =>
          array(
            'name' => 'quotes_invoices',
            'type' => 'one-to-one',
            'label' => 'relationships.quotes_invoices',
            'fields' =>
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
  ),
);
