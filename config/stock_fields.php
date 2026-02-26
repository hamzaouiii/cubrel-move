<?php

return [

  'accounts' => [
    'name' => [
      'name' => 'name',
      'type' => 'textfield',
      'required' => true,
    ],
    'website' => [
      'name' => 'website',
      'type' => 'textfield',
    ],
    'email' => [
      'name' => 'email',
      'type' => 'email',
    ],
    'phone' => [
      'name' => 'phone',
      'type' => 'textfield',
    ],
    'billing_address' => [
      'name' => 'billing_address',
      'type' => 'longtext',
    ],
    'shipping_address' => [
      'name' => 'shipping_address',
      'type' => 'longtext',
    ],
    'city' => [
      'name' => 'city',
      'type' => 'textfield',
    ],
    'country' => [
      'name' => 'country',
      'type' => 'textfield',
    ],
    'description' => [
      'name' => 'description',
      'type' => 'longtext',
    ],
    'created_at' => [
      'name' => 'created_at',
      'type' => 'datetime'
    ],
    'updated_at' => [
      'name' => 'updated_at',
      'type' => 'datetime'
    ]
  ],

  'contacts' => [
    'name' => [
      'name' => 'name',
      'type' => 'textfield',
      'required' => true,
    ],
    'account_id' => [
      'name' => 'account_id',
      'type' => 'relationship',
    ],
    'first_name' => [
      'name' => 'first_name',
      'type' => 'textfield',
    ],
    'last_name' => [
      'name' => 'last_name',
      'type' => 'textfield',
    ],
    'email' => [
      'name' => 'email',
      'type' => 'email',
    ],
    'phone' => [
      'name' => 'phone',
      'type' => 'textfield',
    ],
    'position' => [
      'name' => 'position',
      'type' => 'textfield',
    ],
    'notes' => [
      'name' => 'notes',
      'type' => 'longtext',
    ],
    'description' => [
      'name' => 'description',
      'type' => 'longtext',
    ],
    'created_at' => [
      'name' => 'created_at',
      'type' => 'datetime'
    ],
    'updated_at' => [
      'name' => 'updated_at',
      'type' => 'datetime'
    ]
  ],

  'leads' => [
    'name' => [
      'name' => 'name',
      'type' => 'textfield',
      'required' => true,
    ],
    'first_name' => [
      'name' => 'first_name',
      'type' => 'textfield',
    ],
    'last_name' => [
      'name' => 'last_name',
      'type' => 'textfield',
    ],
    'email' => [
      'name' => 'email',
      'type' => 'email',
    ],
    'phone' => [
      'name' => 'phone',
      'type' => 'textfield',
    ],
    'company' => [
      'name' => 'company',
      'type' => 'textfield',
    ],
    'street' => [
      'name' => 'street',
      'type' => 'longtext',
    ],
    'city' => [
      'name' => 'city',
      'type' => 'textfield',
    ],
    'zip' => [
      'name' => 'zip',
      'type' => 'textfield',
    ],
    'description' => [
      'name' => 'description',
      'type' => 'longtext',
    ],
    'created_at' => [
      'name' => 'created_at',
      'type' => 'datetime'
    ],
    'updated_at' => [
      'name' => 'updated_at',
      'type' => 'datetime'
    ]
  ],

  'invoices' => [
    'name' => [
      'name' => 'name',
      'type' => 'textfield',
      'required' => true,
    ],
    'account_id' => [
      'name' => 'account_id',
      'type' => 'relationship',
    ],
    'contact_id' => [
      'name' => 'contact_id',
      'type' => 'relationship',
    ],
    'quote_id' => [
      'name' => 'quote_id',
      'type' => 'dropdown',
    ],
    'number' => [
      'name' => 'number',
      'type' => 'number',
    ],
    'status' => [
      'name' => 'status',
      'type' => 'dropdown',
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
      'type' => 'textfield',
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
      'type' => 'longtext',
    ],
    'description' => [
      'name' => 'description',
      'type' => 'longtext',
    ],
    'created_at' => [
      'name' => 'created_at',
      'type' => 'datetime'
    ],
    'updated_at' => [
      'name' => 'updated_at',
      'type' => 'datetime'
    ]
  ],

  'quotes' => [
    'name' => [
      'name' => 'name',
      'type' => 'textfield',
      'required' => true,
    ],
    'account_id' => [
      'name' => 'account_id',
      'type' => 'relationship',
    ],
    'contact_id' => [
      'name' => 'contact_id',
      'type' => 'relationship',
    ],
    'number' => [
      'name' => 'number',
      'type' => 'number',
    ],
    'status' => [
      'name' => 'status',
      'type' => 'dropdown',
    ],
    'valid_until' => [
      'name' => 'valid_until',
      'type' => 'date',
    ],
    'currency' => [
      'name' => 'currency',
      'type' => 'textfield',
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
      'type' => 'longtext',
    ],
    'description' => [
      'name' => 'description',
      'type' => 'longtext',
    ],
    'created_at' => [
      'name' => 'created_at',
      'type' => 'datetime'
    ],
    'updated_at' => [
      'name' => 'updated_at',
      'type' => 'datetime'
    ]
  ],

  'cases' => [
    'name' => [
      'name' => 'name',
      'type' => 'textfield',
      'required' => true,
    ],
    'account_id' => [
      'name' => 'account_id',
      'type' => 'relationship',
    ],
    'contact_id' => [
      'name' => 'contact_id',
      'type' => 'relationship',
    ],
    'subject' => [
      'name' => 'subject',
      'type' => 'textfield',
    ],
    'description' => [
      'name' => 'description',
      'type' => 'longtext',
    ],
    'status' => [
      'name' => 'status',
      'type' => 'dropdown',
    ],
    'priority' => [
      'name' => 'priority',
      'type' => 'dropdown',
    ],
    'opened_at' => [
      'name' => 'opened_at',
      'type' => 'datetime',
    ],
    'closed_at' => [
      'name' => 'closed_at',
      'type' => 'datetime',
    ],
    'created_at' => [
      'name' => 'created_at',
      'type' => 'datetime'
    ],
    'updated_at' => [
      'name' => 'updated_at',
      'type' => 'datetime'
    ]
  ],

  'emails' => [
    'to' => [
      'name' => 'to',
      'type' => 'email',
    ],
    'subject' => [
      'name' => 'subject',
      'type' => 'textfield',
    ],
    'mailable_class' => [
      'name' => 'mailable_class',
      'type' => 'textfield',
    ],
    'related_id' => [
      'name' => 'related_id',
      'type' => 'textfield',
    ],
    'status' => [
      'name' => 'status',
      'type' => 'dropdown',
    ],
    'description' => [
      'name' => 'description',
      'type' => 'longtext',
    ],
    'created_at' => [
      'name' => 'created_at',
      'type' => 'datetime'
    ],
    'updated_at' => [
      'name' => 'updated_at',
      'type' => 'datetime'
    ]
  ],

  'inquiries' => [
    'name' => [
      'name' => 'name',
      'type' => 'textfield',
      'required' => true,
    ],
    'message' => [
      'name' => 'message',
      'type' => 'longtext',
    ],
    'email' => [
      'name' => 'email',
      'type' => 'email',
    ],
    'phone' => [
      'name' => 'phone',
      'type' => 'textfield',
    ],
    'status' => [
      'name' => 'status',
      'type' => 'dropdown',
    ],
    'ip' => [
      'name' => 'ip',
      'type' => 'textfield',
    ],
    'user_agent' => [
      'name' => 'user_agent',
      'type' => 'longtext',
    ],
    'description' => [
      'name' => 'description',
      'type' => 'longtext',
    ],
    'created_at' => [
      'name' => 'created_at',
      'type' => 'datetime'
    ],
    'updated_at' => [
      'name' => 'updated_at',
      'type' => 'datetime'
    ]
  ],
  'opportunities' => [

    'name' => [
      'name' => 'name',
      'type' => 'textfield',
      'required' => true,
    ],

    'account_id' => [
      'name' => 'account_id',
      'type' => 'relationship',
    ],

    'amount' => [
      'name' => 'amount',
      'type' => 'textfield',
    ],

    'currency' => [
      'name' => 'currency',
      'type' => 'textfield',
    ],

    'description' => [
      'name' => 'description',
      'type' => 'longtext',
    ],

    'sales_stage' => [
      'name' => 'sales_stage',
      'type' => 'dropdown',
    ],

    'probability' => [
      'name' => 'probability',
      'type' => 'number',
    ],

    'expected_close_date' => [
      'name' => 'expected_close_date',
      'type' => 'date',
    ],

    'type' => [
      'name' => 'type',
      'type' => 'dropdown',
    ],

    'assigned_user_id' => [
      'name' => 'assigned_user_id',
      'type' => 'relationship',
    ],

    'created_at' => [
      'name' => 'created_at',
      'type' => 'datetime',
    ],

    'updated_at' => [
      'name' => 'updated_at',
      'type' => 'datetime',
    ],

  ],
  'products' => [

    'name' => [
      'name' => 'name',
      'type' => 'textfield',
      'required' => true,
    ],

    'sku' => [
      'name' => 'sku',
      'type' => 'textfield',
    ],

    'description' => [
      'name' => 'description',
      'type' => 'longtext',
    ],

    'category' => [
      'name' => 'category',
      'type' => 'textfield',
    ],

    'price' => [
      'name' => 'price',
      'type' => 'number',
    ],

    'currency' => [
      'name' => 'currency',
      'type' => 'textfield',
    ],

    'is_active' => [
      'name' => 'is_active',
      'type' => 'checkbox',
    ],

    'created_at' => [
      'name' => 'created_at',
      'type' => 'datetime',
    ],

    'updated_at' => [
      'name' => 'updated_at',
      'type' => 'datetime',
    ],

  ],
  'orders' => [

    'order_number' => [
      'name' => 'order_number',
      'type' => 'textfield',
      'required' => true,
    ],

    'account_id' => [
      'name' => 'account_id',
      'type' => 'relationship',
    ],

    'opportunity_id' => [
      'name' => 'opportunity_id',
      'type' => 'relationship',
    ],

    'description' => [
      'name' => 'description',
      'type' => 'longtext',
    ],

    'total_amount' => [
      'name' => 'total_amount',
      'type' => 'number',
    ],

    'currency' => [
      'name' => 'currency',
      'type' => 'textfield',
    ],

    'status' => [
      'name' => 'status',
      'type' => 'dropdown',
    ],

    'order_date' => [
      'name' => 'order_date',
      'type' => 'date',
    ],

    'due_date' => [
      'name' => 'due_date',
      'type' => 'date',
    ],

    'assigned_user_id' => [
      'name' => 'assigned_user_id',
      'type' => 'relationship',
    ],

    'created_at' => [
      'name' => 'created_at',
      'type' => 'datetime',
    ],

    'updated_at' => [
      'name' => 'updated_at',
      'type' => 'datetime',
    ],

  ],

];
