<?php

return [
    'accounts' => [
        'label' => 'Accounts',
    ],

    'contacts' => [
        'label' => 'Contacts',
    ],

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
            'cancel'          => 'Cancel',
            'edit'            => 'Edit',
            'save'            => 'Save',
            'share'           => 'Share',
            'export'          => 'Export',
            'placeholder'     => 'Something else here',
            'bulk_action'     => 'Bulk Action',
            'delete'          => 'Delete',
            'create'          => 'Create',
            'search_placeholder' => 'Search In This List'
        ],
    ],

    'invoices' => [
        'label' => 'Invoices',
    ],

    'quotes' => [
        'label' => 'Quotes',
    ],

    'cases' => [
        'label' => 'Tickets',
    ],

    'emails' => [
        'label' => 'Emails',
    ],

    'inquiries' => [
        'label' => 'Inquiries',
    ],

    'money' => [
        'label' => 'Money',
    ],
    'settings' => ['label' => 'Settings']

];
