<?php

return array (
  'list' => 
  array (
    'columns' => 
    array (
      0 => 
      array (
        'name' => 'subject',
        'type' => 'text',
        'label' => 'modules.emails.fields.subject',
      ),
      1 => 
      array (
        'name' => 'to',
        'type' => 'email',
        'label' => 'modules.emails.fields.to',
      ),
      2 => 
      array (
        'name' => 'status',
        'type' => 'select',
        'label' => 'modules.emails.fields.status',
      ),
      3 => 
      array (
        'name' => 'created_at',
        'type' => 'datetime',
        'label' => 'modules.defaults.created_at',
      ),
      4 => 
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
        'name' => 'Card',
        'layout' => 
        array (
          0 => 
          array (
            'name' => 'subject',
            'type' => 'text',
            'label' => 'modules.emails.fields.subject',
          ),
          1 => 
          array (
            'name' => 'to',
            'type' => 'email',
            'label' => 'modules.emails.fields.to',
          ),
          2 => 
          array (
            'name' => 'description',
            'type' => 'longText',
            'label' => 'modules.defaults.description',
          ),
          3 => 
          array (
            'name' => 'status',
            'type' => 'select',
            'label' => 'modules.emails.fields.status',
          ),
          4 => 
          array (
            'name' => 'created_at',
            'type' => 'datetime',
            'label' => 'modules.defaults.created_at',
          ),
          5 => 
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
            'name' => 'contacts_emails',
            'type' => 'one-to-many',
            'label' => 'relationships.contacts_emails',
            'fields' => 
            array (
              0 => 
              array (
                'name' => 'name',
                'type' => 'text',
                'label' => 'modules.contacts.fields.name',
              ),
              1 => 
              array (
                'name' => 'created_at',
                'type' => 'datetime',
                'label' => 'modules.contacts.fields.created_at',
              ),
              2 => 
              array (
                'name' => 'updated_at',
                'type' => 'datetime',
                'label' => 'modules.contacts.fields.updated_at',
              ),
            ),
          ),
          1 => 
          array (
            'name' => 'cases_emails',
            'type' => 'one-to-many',
            'label' => 'relationships.cases_emails',
            'fields' => 
            array (
              0 => 
              array (
                'name' => 'name',
                'type' => 'text',
                'label' => 'modules.cases.fields.name',
              ),
              1 => 
              array (
                'name' => 'created_at',
                'type' => 'datetime',
                'label' => 'modules.cases.fields.created_at',
              ),
              2 => 
              array (
                'name' => 'updated_at',
                'type' => 'datetime',
                'label' => 'modules.cases.fields.updated_at',
              ),
            ),
          ),
          2 => 
          array (
            'name' => 'inquiries_emails',
            'type' => 'one-to-many',
            'label' => 'relationships.inquiries_emails',
            'fields' => 
            array (
              0 => 
              array (
                'name' => 'name',
                'type' => 'text',
                'label' => 'modules.inquiries.fields.name',
              ),
              1 => 
              array (
                'name' => 'created_at',
                'type' => 'datetime',
                'label' => 'modules.inquiries.fields.created_at',
              ),
              2 => 
              array (
                'name' => 'updated_at',
                'type' => 'datetime',
                'label' => 'modules.inquiries.fields.updated_at',
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
            'name' => 'leads_emails',
            'type' => 'one-to-many',
            'label' => 'relationships.leads_emails',
            'fields' => 
            array (
              0 => 
              array (
                'name' => 'name',
                'type' => 'text',
                'label' => 'modules.leads.fields.name',
              ),
              1 => 
              array (
                'name' => 'created_at',
                'type' => 'datetime',
                'label' => 'modules.leads.fields.created_at',
              ),
              2 => 
              array (
                'name' => 'updated_at',
                'type' => 'datetime',
                'label' => 'modules.leads.fields.updated_at',
              ),
            ),
          ),
          1 => 
          array (
            'name' => 'accounts_emails',
            'type' => 'one-to-many',
            'label' => 'relationships.accounts_emails',
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
                'name' => 'created_at',
                'type' => 'datetime',
                'label' => 'modules.accounts.fields.created_at',
              ),
              2 => 
              array (
                'name' => 'updated_at',
                'type' => 'datetime',
                'label' => 'modules.accounts.fields.updated_at',
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
        'name' => 'subject',
        'type' => 'text',
        'label' => 'modules.emails.fields.subject',
      ),
      1 => 
      array (
        'name' => 'status',
        'type' => 'select',
        'label' => 'modules.emails.fields.status',
      ),
      2 => 
      array (
        'name' => 'to',
        'type' => 'email',
        'label' => 'modules.emails.fields.to',
      ),
      3 => 
      array (
        'name' => 'created_at',
        'type' => 'datetime',
        'label' => 'modules.defaults.created_at',
      ),
      4 => 
      array (
        'name' => 'updated_at',
        'type' => 'datetime',
        'label' => 'modules.defaults.updated_at',
      ),
    ),
  ),
);
