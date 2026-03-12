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
      3 =>
      array(
        'name' => 'company',
        'type' => 'text',
        'label' => 'modules.leads.fields.company',
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
          3 =>
          array(
            'name' => 'company',
            'type' => 'text',
            'label' => 'modules.leads.fields.company',
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
            'name' => 'contacts_leads',
            'type' => 'one-to-one',
            'label' => 'relationships.contacts_leads',
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
                'type' => 'text',
                'label' => 'modules.contacts.fields.phone',
              ),
            ),
          ),
          1 =>
          array(
            'name' => 'leads_emails',
            'type' => 'one-to-many',
            'label' => 'relationships.leads_emails',
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
        'name' => 'email',
        'type' => 'email',
        'label' => 'modules.leads.fields.email',
      ),
      2 =>
      array(
        'name' => 'company',
        'type' => 'text',
        'label' => 'modules.leads.fields.company',
      ),
      3 =>
      array(
        'name' => 'phone',
        'type' => 'text',
        'label' => 'modules.leads.fields.phone',
      ),
    ),
  ),
);
