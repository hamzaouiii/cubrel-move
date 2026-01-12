<?php

return [

  'accounts' => [
    'name' => [
      'name' => 'name',
      'type' => 'textField',
      'required' => true,

    ],
    'website' => [
      'name' => 'website',
      'type' => 'textField',

    ],
    'email' => [
      'name' => 'email',
      'type' => 'email',

    ],
    'phone' => [
      'name' => 'phone',
      'type' => 'textField',

    ],
    'billing_address' => [
      'name' => 'billing_address',
      'type' => 'longText',
    ],
    'shipping_address' => [
      'name' => 'shipping_address',
      'type' => 'longText',
    ],
    'city' => [
      'name' => 'city',
      'type' => 'textField',

    ],
    'country' => [
      'name' => 'country',
      'type' => 'textField',

    ],
  ],

  'contacts' => [
    'name' => [
      'name' => 'name',
      'type' => 'textField',
      'required' => true,

    ],
    'account_id' => [
      'name' => 'account_id',
      'type' => 'dropDownField',

    ],
    'first_name' => [
      'name' => 'first_name',
      'type' => 'textField',

    ],
    'last_name' => [
      'name' => 'last_name',
      'type' => 'textField',

    ],
    'email' => [
      'name' => 'email',
      'type' => 'email',

    ],
    'phone' => [
      'name' => 'phone',
      'type' => 'textField',

    ],
    'position' => [
      'name' => 'position',
      'type' => 'textField',

    ],
    'notes' => [
      'name' => 'notes',
      'type' => 'longText',
    ],
  ],

  'leads' => [
    'name' => [
      'name' => 'name',
      'type' => 'textField',
      'required' => true,

    ],
    'first_name' => [
      'name' => 'first_name',
      'type' => 'textField',

    ],
    'last_name' => [
      'name' => 'last_name',
      'type' => 'textField',

    ],
    'email' => [
      'name' => 'email',
      'type' => 'email',

    ],
    'phone' => [
      'name' => 'phone',
      'type' => 'textField',

    ],
    'company' => [
      'name' => 'company',
      'type' => 'textField',

    ],
    'street' => [
      'name' => 'street',
      'type' => 'longText',
    ],
    'city' => [
      'name' => 'city',
      'type' => 'textField',

    ],
    'zip' => [
      'name' => 'zip',
      'type' => 'textField',

    ],
    'description' => [
      'name' => 'description',
      'type' => 'longText',
    ],
  ],

  'invoices' => [
    'name' => [
      'name' => 'name',
      'type' => 'textField',
      'required' => true,

    ],
    'account_id' => [
      'name' => 'account_id',
      'type' => 'dropDownField',

    ],
    'contact_id' => [
      'name' => 'contact_id',
      'type' => 'dropDownField',

    ],
    'quote_id' => [
      'name' => 'quote_id',
      'type' => 'dropDownField',

    ],
    'number' => [
      'name' => 'number',
      'type' => 'number',
    ],
    'status' => [
      'name' => 'status',
      'type' => 'dropDownField',

    ],
    'issue_date' => [
      'name' => 'issue_date',
      'type' => 'date',
    ],
    'due_date' => [
      'name' => 'due_date',
      'type' => 'date',
    ],
    'currency' => [
      'name' => 'currency',
      'type' => 'textField',

    ],
    'subtotal' => [
      'name' => 'subtotal',
      'type' => 'number',
    ],
    'tax' => [
      'name' => 'tax',
      'type' => 'number',
    ],
    'total' => [
      'name' => 'total',
      'type' => 'number',
    ],
    'notes' => [
      'name' => 'notes',
      'type' => 'longText',
    ],
  ],

  'quotes' => [
    'name' => [
      'name' => 'name',
      'type' => 'textField',
      'required' => true,

    ],
    'account_id' => [
      'name' => 'account_id',
      'type' => 'dropDownField',

    ],
    'contact_id' => [
      'name' => 'contact_id',
      'type' => 'dropDownField',

    ],
    'number' => [
      'name' => 'number',
      'type' => 'number',
    ],
    'status' => [
      'name' => 'status',
      'type' => 'dropDownField',

    ],
    'valid_until' => [
      'name' => 'valid_until',
      'type' => 'date',
    ],
    'currency' => [
      'name' => 'currency',
      'type' => 'textField',

    ],
    'subtotal' => [
      'name' => 'subtotal',
      'type' => 'number',
    ],
    'tax' => [
      'name' => 'tax',
      'type' => 'number',
    ],
    'total' => [
      'name' => 'total',
      'type' => 'number',
    ],
    'notes' => [
      'name' => 'notes',
      'type' => 'longText',
    ],
  ],

  'cases' => [
    'name' => [
      'name' => 'name',
      'type' => 'textField',
      'required' => true,

    ],
    'account_id' => [
      'name' => 'account_id',
      'type' => 'dropDownField',

    ],
    'contact_id' => [
      'name' => 'contact_id',
      'type' => 'dropDownField',

    ],
    'subject' => [
      'name' => 'subject',
      'type' => 'textField',

    ],
    'description' => [
      'name' => 'description',
      'type' => 'longText',
    ],
    'status' => [
      'name' => 'status',
      'type' => 'dropDownField',

    ],
    'priority' => [
      'name' => 'priority',
      'type' => 'dropDownField',

    ],
    'opened_at' => [
      'name' => 'opened_at',
      'type' => 'dateTime',
    ],
    'closed_at' => [
      'name' => 'closed_at',
      'type' => 'dateTime',
    ],
  ],

  'emails' => [
    'to' => [
      'name' => 'to',
      'type' => 'email',

    ],
    'subject' => [
      'name' => 'subject',
      'type' => 'textField',

    ],
    'mailable_class' => [
      'name' => 'mailable_class',
      'type' => 'textField',

    ],
    'related_id' => [
      'name' => 'related_id',
      'type' => 'textField',

    ],
    'status' => [
      'name' => 'status',
      'type' => 'dropDownField',

    ],
  ],

  'inquiries' => [
    'name' => [
      'name' => 'name',
      'type' => 'textField',
      'required' => true,

    ],
    'message' => [
      'name' => 'message',
      'type' => 'longText',
    ],
    'email' => [
      'name' => 'email',
      'type' => 'email',

    ],
    'phone' => [
      'name' => 'phone',
      'type' => 'textField',

    ],
    'status' => [
      'name' => 'status',
      'type' => 'dropDownField',

    ],
    'ip' => [
      'name' => 'ip',
      'type' => 'textField',

    ],
    'user_agent' => [
      'name' => 'user_agent',
      'type' => 'longText',
    ],
  ],

];
