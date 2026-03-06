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
      4 =>
      array(
        'name' => 'created_at',
        'type' => 'datetime',
        'label' => 'modules.defaults.created_at',
      ),
      5 =>
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
        'name' => 'Card',
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
            'name' => 'number',
            'type' => 'number',
            'label' => 'modules.invoices.fields.number',
          ),
          2 =>
          array(
            'name' => 'status',
            'type' => 'select',
            'label' => 'modules.invoices.fields.status',
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
          ),
          1 =>
          array(
            'name' => 'tax',
            'type' => 'number',
            'label' => 'modules.invoices.fields.tax',
          ),
          2 =>
          array(
            'name' => 'total',
            'type' => 'number',
            'label' => 'modules.invoices.fields.total',
          ),
          3 =>
          array(
            'name' => 'currency',
            'type' => 'text',
            'label' => 'modules.invoices.fields.currency',
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
          ),
          1 =>
          array(
            'name' => 'created_at',
            'type' => 'datetime',
            'label' => 'modules.invoices.fields.created_at',
          ),
          2 =>
          array(
            'name' => 'updated_at',
            'type' => 'datetime',
            'label' => 'modules.invoices.fields.updated_at',
          ),
          3 =>
          array(
            'name' => 'issue_date',
            'type' => 'date',
            'label' => 'modules.invoices.fields.issue_date',
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
          ),
          1 =>
          array(
            'name' => 'notes',
            'type' => 'longtext',
            'label' => 'modules.invoices.fields.notes',
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
                'type' => 'text',
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
                'type' => 'text',
                'label' => 'modules.accounts.fields.website',
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
                'name' => 'status',
                'type' => 'select',
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
                'name' => 'position',
                'type' => 'text',
                'label' => 'modules.contacts.fields.position',
              ),
              2 =>
              array(
                'name' => 'phone',
                'type' => 'text',
                'label' => 'modules.contacts.fields.phone',
              ),
              3 =>
              array(
                'name' => 'email',
                'type' => 'email',
                'label' => 'modules.contacts.fields.email',
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
              4 =>
              array(
                'name' => 'number',
                'type' => 'number',
                'label' => 'modules.quotes.fields.number',
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
        'name' => 'due_date',
        'type' => 'date',
        'label' => 'modules.invoices.fields.due_date',
      ),
      4 =>
      array(
        'name' => 'status',
        'type' => 'select',
        'label' => 'modules.invoices.fields.status',
      ),
    ),
  ),
);
