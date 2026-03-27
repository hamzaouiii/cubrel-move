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
            'name' => 'accounts_cases',
            'type' => 'one-to-many',
            'label' => 'relationships.accounts_cases',
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
                'name' => 'phone',
                'type' => 'phone',
                'label' => 'modules.accounts.fields.phone',
              ),
              3 =>
              array(
                'name' => 'website',
                'type' => 'url',
                'label' => 'modules.accounts.fields.website',
              ),
            ),
          ),
          1 =>
          array(
            'name' => 'cases_emails',
            'type' => 'one-to-many',
            'label' => 'relationships.cases_emails',
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
            'name' => 'contacts_cases',
            'type' => 'one-to-many',
            'label' => 'relationships.contacts_cases',
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
        'name' => 'opened_at',
        'type' => 'datetime',
        'label' => 'modules.cases.fields.opened_at',
      ),
      2 =>
      array(
        'name' => 'closed_at',
        'type' => 'datetime',
        'label' => 'modules.cases.fields.closed_at',
      ),
      3 =>
      array(
        'name' => 'status',
        'type' => 'select',
        'label' => 'modules.cases.fields.status',
      ),
      4 =>
      array(
        'name' => 'priority',
        'type' => 'select',
        'label' => 'modules.cases.fields.priority',
      ),
    ),
  ),
  'record' =>
  array(
    'sections' =>
    array(
      0 =>
      array(
        'name' => 'Details',
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
            'name' => 'description',
            'type' => 'longtext',
            'label' => 'modules.cases.fields.description',
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
            'name' => 'opened_at',
            'type' => 'datetime',
            'label' => 'modules.cases.fields.opened_at',
          ),
          1 =>
          array(
            'name' => 'closed_at',
            'type' => 'datetime',
            'label' => 'modules.cases.fields.closed_at',
          ),
          2 =>
          array(
            'name' => 'created_at',
            'type' => 'datetime',
            'label' => 'modules.cases.fields.created_at',
          ),
          3 =>
          array(
            'name' => 'updated_at',
            'type' => 'datetime',
            'label' => 'modules.cases.fields.updated_at',
          ),
        ),
      ),
    ),
  ),
);
