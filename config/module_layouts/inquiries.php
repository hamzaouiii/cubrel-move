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
        'name' => 'email',
        'type' => 'email',
        'label' => 'modules.inquiries.fields.email',
      ),
      2 => 
      array (
        'name' => 'status',
        'type' => 'select',
        'label' => 'modules.inquiries.fields.status',
      ),
      3 => 
      array (
        'name' => 'phone',
        'type' => 'text',
        'label' => 'modules.inquiries.fields.phone',
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
            'name' => 'email',
            'type' => 'email',
            'label' => 'modules.inquiries.fields.email',
          ),
          2 => 
          array (
            'name' => 'phone',
            'type' => 'text',
            'label' => 'modules.inquiries.fields.phone',
          ),
          3 => 
          array (
            'name' => 'status',
            'type' => 'select',
            'label' => 'modules.inquiries.fields.status',
          ),
          4 => 
          array (
            'name' => 'message',
            'type' => 'longtext',
            'label' => 'modules.inquiries.fields.message',
          ),
          5 => 
          array (
            'name' => 'ip',
            'type' => 'text',
            'label' => 'modules.inquiries.fields.ip',
          ),
          6 => 
          array (
            'name' => 'user_agent',
            'type' => 'longtext',
            'label' => 'modules.inquiries.fields.user_agent',
          ),
          7 => 
          array (
            'name' => 'description',
            'type' => 'longText',
            'label' => 'modules.defaults.description',
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
            'name' => 'accounts_inquiries',
            'type' => 'one-to-many',
            'label' => 'relationships.accounts_inquiries',
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
                'name' => 'phone',
                'type' => 'text',
                'label' => 'modules.accounts.fields.phone',
              ),
              2 => 
              array (
                'name' => 'website',
                'type' => 'text',
                'label' => 'modules.accounts.fields.website',
              ),
              3 => 
              array (
                'name' => 'email',
                'type' => 'email',
                'label' => 'modules.accounts.fields.email',
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
            'name' => 'inquiries_emails',
            'type' => 'one-to-many',
            'label' => 'relationships.inquiries_emails',
            'fields' => 
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
                'name' => 'updated_at',
                'type' => 'datetime',
                'label' => 'modules.emails.fields.updated_at',
              ),
              4 => 
              array (
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
        'name' => 'status',
        'type' => 'select',
        'label' => 'modules.inquiries.fields.status',
      ),
      2 => 
      array (
        'name' => 'created_at',
        'type' => 'datetime',
        'label' => 'modules.defaults.created_at',
      ),
      3 => 
      array (
        'name' => 'updated_at',
        'type' => 'datetime',
        'label' => 'modules.defaults.updated_at',
      ),
    ),
  ),
);
