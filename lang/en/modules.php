<?php

return [
    'of' => 'of',
    'defaults' => [
      'name' => 'Name',
      'created_at' => 'Created At',
      'updated_at' => 'Updated At',

    ],
    'actions' => [
      'share'           => 'Share',
      'export'          => 'export',
      'placeholder'     => 'Something here...',
      'bulk_action'     => 'Bulk Actions',
      'delete'          => 'Delete',
      'create'          => 'Create',
      'search_placeholder'  => 'Search In This List',
      'cancel'          => 'Cancel',
      'edit'            => 'Edit',
      'save'            => 'Save',      
    ],
    'accounts' => [
        'label'  => 'Accounts',
        'fields' => [
            'id'               => 'ID',
            'name'             => 'Name',
            'website'          => 'Website',
            'email'            => 'Email',
            'phone'            => 'Phone',
            'billing_address'  => 'Billing Address',
            'shipping_address' => 'Shipping Address',
            'city'             => 'City',
            'country'          => 'Country',
            'created_at'       => 'Created At',
            'updated_at'       => 'Updated At',
        ],
    ],

    'contacts' => [
        'label'  => 'Contacts',
        'fields' => [
            'id'         => 'ID',
            'name'       => 'Name',
            'account_id' => 'Account',
            'first_name' => 'First Name',
            'last_name'  => 'Last Name',
            'email'      => 'Email',
            'phone'      => 'Phone',
            'position'   => 'Position',
            'notes'      => 'Notes',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ],
    ],

    // unchanged as requested
    'leads' => [
        'label' => 'Leads',
        'fields' => [
            'id'          => 'ID',
            'name'        => 'Name',
            'first_name'  => 'First Name',
            'last_name'   => 'Last Name',
            'email'       => 'Email',
            'phone'       => 'Phone',
            'company'     => 'Company',
            'street'      => 'Street',
            'city'        => 'City',
            'zip'         => 'ZIP Code',
            'description' => 'Description',
            'created_at'  => 'Created At',
            'updated_at'  => 'Updated At',
        ],

        'actions' => [
            'cancel'             => 'Cancel',
            'edit'               => 'Edit',
            'save'               => 'Save',
            'share'              => 'Share',
            'export'             => 'Export',
            'placeholder'        => 'Something else here',
            'bulk_action'        => 'Bulk Action',
            'delete'             => 'Delete',
            'create'             => 'Create',
            'search_placeholder' => 'Search In This List',
        ],
    ],

    'invoices' => [
        'label'  => 'Invoices',
        'fields' => [
            'id'          => 'ID',
            'name'        => 'Name',
            'account_id'  => 'Account',
            'contact_id'  => 'Contact',
            'quote_id'    => 'Quote',
            'number'      => 'Invoice Number',
            'status'      => 'Status',
            'issue_date'  => 'Issue Date',
            'due_date'    => 'Due Date',
            'currency'    => 'Currency',
            'subtotal'    => 'Subtotal',
            'tax'         => 'Tax',
            'total'       => 'Total',
            'notes'       => 'Notes',
            'created_at'  => 'Created At',
            'updated_at'  => 'Updated At',
        ],
    ],

    'quotes' => [
        'label'  => 'Quotes',
        'fields' => [
            'id'          => 'ID',
            'name'        => 'Name',
            'account_id'  => 'Account',
            'contact_id'  => 'Contact',
            'number'      => 'Quote Number',
            'status'      => 'Status',
            'valid_until' => 'Valid Until',
            'currency'    => 'Currency',
            'subtotal'    => 'Subtotal',
            'tax'         => 'Tax',
            'total'       => 'Total',
            'notes'       => 'Notes',
            'created_at'  => 'Created At',
            'updated_at'  => 'Updated At',
        ],
    ],

    'cases' => [
        'label'  => 'Cases',
        'fields' => [
            'id'          => 'ID',
            'name'        => 'Name',
            'account_id'  => 'Account',
            'contact_id'  => 'Contact',
            'subject'     => 'Subject',
            'description' => 'Description',
            'status'      => 'Status',
            'priority'    => 'Priority',
            'opened_at'   => 'Opened At',
            'closed_at'   => 'Closed At',
            'created_at'  => 'Created At',
            'updated_at'  => 'Updated At',
        ],
    ],

    'emails' => [
        'label'  => 'Emails',
        'fields' => [
            'id'             => 'ID',
            'name'           => 'Name',
            'to'             => 'To',
            'sent'           => 'Sent',
            'subject'        => 'Subject',
            'mailable_class' => 'Mailable Class',
            'related_id'     => 'Related ID',
            'status'         => 'Status',
            'error'          => 'Error',
            'created_at'     => 'Created At',
            'updated_at'     => 'Updated At',
        ],
    ],

    'inquiries' => [
        'label'  => 'Inquiries',
        'fields' => [
            'id'                 => 'ID',
            'name'               => 'Name',
            'email'              => 'Email',
            'email_confirmation' => 'Email Confirmation',
            'phone'              => 'Phone',
            'message'            => 'Message',
            'status'             => 'Status',
            'ip'                 => 'IP Address',
            'user_agent'         => 'User Agent',
            'created_at'         => 'Created At',
            'updated_at'         => 'Updated At',
        ],
    ],

    'money' => [
        'label'  => 'Money',
        'fields' => [
            'id'          => 'ID',
            'name'        => 'Name',
            'description' => 'Description',
            'data'        => 'Data',
            'created_at'  => 'Created At',
            'updated_at'  => 'Updated At',
            'deleted_at'  => 'Deleted At',
        ],
    ],

    // new module based on books_cstm table
    'books' => [
        'label'  => 'Books',
        'fields' => [
            'id'          => 'ID',
            'name'        => 'Name',
            'description' => 'Description',
            'data'        => 'Data',
            'created_at'  => 'Created At',
            'updated_at'  => 'Updated At',
            'deleted_at'  => 'Deleted At',
        ],
    ],

    'settings' => [
        'label' => 'Settings',
    ],
];
