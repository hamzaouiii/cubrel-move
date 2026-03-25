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
      2 =>
      array(
        'id' => 'ab103add-6343-4bb5-904c-04ec3447b202',
        'name' => 'position',
        'type' => 'text',
        'label' => 'modules.contacts.fields.position',
        'module_id' => '019c9a01-7d25-7136-a50d-5fa6fdc46656',
        'dropdown_list_id' => NULL,
      ),
      3 =>
      array(
        'id' => '2e94cba5-b0dd-4959-b4a9-3a3863d4c915',
        'name' => 'phone',
        'type' => 'text',
        'label' => 'modules.contacts.fields.phone',
        'module_id' => '019c9a01-7d25-7136-a50d-5fa6fdc46656',
        'dropdown_list_id' => NULL,
      ),
      4 =>
      array(
        'id' => '36a49d40-b37f-4340-9d78-397ee7d9a64b',
        'name' => 'email',
        'type' => 'email',
        'label' => 'modules.contacts.fields.email',
        'module_id' => '019c9a01-7d25-7136-a50d-5fa6fdc46656',
        'dropdown_list_id' => NULL,
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
          4 =>
          array(
            'name' => 'notes',
            'type' => 'longText',  // Changed to capital T
            'label' => 'modules.contacts.fields.notes',
          ),
          5 =>
          array(
            'name' => 'description',
            'type' => 'longText',  // Changed to capital T
            'label' => 'modules.defaults.description',
          ),
        ),
      ),
      1 =>
      array(
        'name' => 'System',
        'layout' =>
        array(
          0 =>
          array(
            'name' => 'created_at',
            'type' => 'datetime',
            'label' => 'modules.defaults.created_at',
          ),
          1 =>
          array(
            'name' => 'updated_at',
            'type' => 'datetime',
            'label' => 'modules.defaults.updated_at',
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
                'name' => 'description',
                'type' => 'longtext',
                'label' => 'modules.accounts.fields.description',
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
            'name' => 'contacts_cases',
            'type' => 'one-to-many',
            'label' => 'relationships.contacts_cases',
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
                'name' => 'status',
                'type' => 'select',
                'label' => 'modules.cases.fields.status',
              ),
              2 =>
              array(
                'name' => 'priority',
                'type' => 'select',
                'label' => 'modules.cases.fields.priority',
              ),
              3 =>
              array(
                'name' => 'opened_at',
                'type' => 'datetime',
                'label' => 'modules.cases.fields.opened_at',
              ),
              4 =>
              array(
                'name' => 'closed_at',
                'type' => 'datetime',
                'label' => 'modules.cases.fields.closed_at',
              ),
            ),
          ),
          2 =>
          array(
            'name' => 'contacts_emails',
            'type' => 'one-to-many',
            'label' => 'relationships.contacts_emails',
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
                'name' => 'status',
                'type' => 'select',
                'label' => 'modules.emails.fields.status',
              ),
              2 =>
              array(
                'name' => 'to',
                'type' => 'email',
                'label' => 'modules.emails.fields.to',
              ),
              3 =>
              array(
                'name' => 'created_at',
                'type' => 'datetime',
                'label' => 'modules.emails.fields.created_at',
              ),
              4 =>
              array(
                'name' => 'updated_at',
                'type' => 'datetime',
                'label' => 'modules.emails.fields.updated_at',
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
            'name' => 'contacts_leads',
            'type' => 'one-to-one',
            'label' => 'relationships.contacts_leads',
            'fields' =>
            array(
              0 =>
              array(
                'name' => 'name',
                'type' => 'text',
                'label' => 'modules.leads.fields.name',
              ),
              1 =>
              array(
                'name' => 'email',
                'type' => 'email',
                'label' => 'modules.leads.fields.email',
              ),
              2 =>
              array(
                'name' => 'phone',
                'type' => 'text',
                'label' => 'modules.leads.fields.phone',
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
                'name' => 'sales_stage',
                'type' => 'select',
                'label' => 'modules.opportunities.fields.sales_stage',
              ),
              3 =>
              array(
                'name' => 'expected_close_date',
                'type' => 'date',
                'label' => 'modules.opportunities.fields.expected_close_date',
              ),
            ),
          ),
          2 =>
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
              5 =>
              array(
                'name' => 'issue_date',
                'type' => 'date',
                'label' => 'modules.invoices.fields.issue_date',
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
      2 =>
      array(
        'id' => '2e94cba5-b0dd-4959-b4a9-3a3863d4c915',
        'name' => 'phone',
        'type' => 'text',
        'label' => 'modules.contacts.fields.phone',
        'module_id' => '019c9a01-7d25-7136-a50d-5fa6fdc46656',
        'dropdown_list_id' => NULL,
      ),
      3 =>
      array(
        'id' => '36a49d40-b37f-4340-9d78-397ee7d9a64b',
        'name' => 'email',
        'type' => 'email',
        'label' => 'modules.contacts.fields.email',
        'module_id' => '019c9a01-7d25-7136-a50d-5fa6fdc46656',
        'dropdown_list_id' => NULL,
      ),
      4 =>
      array(
        'id' => 'ab103add-6343-4bb5-904c-04ec3447b202',
        'name' => 'position',
        'type' => 'text',
        'label' => 'modules.contacts.fields.position',
        'module_id' => '019c9a01-7d25-7136-a50d-5fa6fdc46656',
        'dropdown_list_id' => NULL,
      ),
    ),
  ),
);
