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
        'id' => '0f5ba6a1-b842-45e6-a864-dfdcaf92df06',
        'name' => 'website',
        'type' => 'text',
        'label' => 'modules.accounts.fields.website',
        'module_id' => '019c99e9-1fa7-711b-853d-329ab73ed199',
        'dropdown_list_id' => NULL,
      ),
      2 =>
      array(
        'id' => 'b09fafa7-644f-49e8-bb48-ceb1b4a94daf',
        'name' => 'phone',
        'type' => 'text',
        'label' => 'modules.accounts.fields.phone',
        'module_id' => '019c99e9-1fa7-711b-853d-329ab73ed199',
        'dropdown_list_id' => NULL,
      ),
      3 =>
      array(
        'id' => '7776deb0-4eb0-493b-b7d8-faef91283174',
        'name' => 'email',
        'type' => 'email',
        'label' => 'modules.accounts.fields.email',
        'module_id' => '019c99e9-1fa7-711b-853d-329ab73ed199',
        'dropdown_list_id' => NULL,
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
            'name' => 'website',
            'type' => 'text',
            'label' => 'modules.accounts.fields.website',
          ),
          2 =>
          array(
            'name' => 'email',
            'type' => 'email',
            'label' => 'modules.accounts.fields.email',
          ),
          3 =>
          array(
            'name' => 'phone',
            'type' => 'text',
            'label' => 'modules.accounts.fields.phone',
          ),
          4 =>
          array(
            'name' => 'description',
            'type' => 'longText',
            'label' => 'modules.defaults.description',
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
      1 =>
      array(
        'name' => 'Address',
        'layout' =>
        array(
          0 =>
          array(
            'name' => 'country',
            'type' => 'text',
            'label' => 'modules.accounts.fields.country',
          ),
          1 =>
          array(
            'name' => 'city',
            'type' => 'text',
            'label' => 'modules.accounts.fields.city',
          ),
          2 =>
          array(
            'name' => 'shipping_address',
            'type' => 'longtext',
            'label' => 'modules.accounts.fields.shipping_address',
          ),
          3 =>
          array(
            'name' => 'billing_address',
            'type' => 'longtext',
            'label' => 'modules.accounts.fields.billing_address',
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
                'type' => 'phone',
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
          ),
          1 =>
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
                'label' => 'modules.invoices.fields.name',
              ),
              1 =>
              array(
                'name' => 'status',
                'type' => 'select',
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
          ),
          2 =>
          array(
            'name' => 'accounts_cases',
            'type' => 'one-to-many',
            'label' => 'relationships.accounts_cases',
            'fields' =>
            array(
              0 =>
              array(
                'name' => 'name',
                'type' => 'text',
                'label' => 'modules.cases.fields.name',
              ),
              1 =>
              array(
                'name' => 'subject',
                'type' => 'text',
                'label' => 'modules.cases.fields.subject',
              ),
              2 =>
              array(
                'name' => 'status',
                'type' => 'select',
                'label' => 'modules.cases.fields.status',
              ),
              3 =>
              array(
                'name' => 'priority',
                'type' => 'select',
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
          ),
          3 =>
          array(
            'name' => 'accounts_inquiries',
            'type' => 'one-to-many',
            'label' => 'relationships.accounts_inquiries',
            'fields' =>
            array(
              0 =>
              array(
                'name' => 'name',
                'type' => 'text',
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
                'type' => 'phone',
                'label' => 'modules.inquiries.fields.phone',
              ),
              3 =>
              array(
                'name' => 'status',
                'type' => 'select',
                'label' => 'modules.inquiries.fields.status',
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
            'name' => 'accounts_opportunities',
            'type' => 'one-to-many',
            'label' => 'relationships.accounts_opportunities',
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
                'name' => 'sales_stage',
                'type' => 'select',
                'label' => 'modules.opportunities.fields.sales_stage',
              ),
              2 =>
              array(
                'name' => 'amount',
                'type' => 'text',
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
                'type' => 'select',
                'label' => 'modules.opportunities.fields.type',
              ),
              5 =>
              array(
                'name' => 'expected_close_date',
                'type' => 'date',
                'label' => 'modules.opportunities.fields.expected_close_date',
              ),
            ),
          ),
          1 =>
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
          ),
          2 =>
          array(
            'name' => 'accounts_orders',
            'type' => 'one-to-many',
            'label' => 'relationships.accounts_orders',
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
                'name' => 'order_date',
                'type' => 'date',
                'label' => 'modules.orders.fields.order_date',
              ),
              2 =>
              array(
                'name' => 'status',
                'type' => 'select',
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
          ),
          3 =>
          array(
            'name' => 'accounts_emails',
            'type' => 'one-to-many',
            'label' => 'relationships.accounts_emails',
            'fields' =>
            array(
              0 =>
              array(
                'name' => 'subject',
                'type' => 'text',
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
                'type' => 'select',
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
        'id' => '0f5ba6a1-b842-45e6-a864-dfdcaf92df06',
        'name' => 'website',
        'type' => 'text',
        'label' => 'modules.accounts.fields.website',
        'module_id' => '019c99e9-1fa7-711b-853d-329ab73ed199',
        'dropdown_list_id' => NULL,
      ),
      2 =>
      array(
        'id' => '7776deb0-4eb0-493b-b7d8-faef91283174',
        'name' => 'email',
        'type' => 'email',
        'label' => 'modules.accounts.fields.email',
        'module_id' => '019c99e9-1fa7-711b-853d-329ab73ed199',
        'dropdown_list_id' => NULL,
      ),
      3 =>
      array(
        'id' => 'b09fafa7-644f-49e8-bb48-ceb1b4a94daf',
        'name' => 'phone',
        'type' => 'text',
        'label' => 'modules.accounts.fields.phone',
        'module_id' => '019c99e9-1fa7-711b-853d-329ab73ed199',
        'dropdown_list_id' => NULL,
      ),
    ),
  ),
);
