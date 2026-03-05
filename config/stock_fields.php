<?php

return [

  'accounts' => [
    'name' => [
      'name' => 'name',
      'searchable' => true,
      'type' => 'text',
      'required' => true,
    ],
    'website' => [
      'name' => 'website',
      'type' => 'text',
    ],
    'email' => [
      'name' => 'email',
      'type' => 'email',
    ],
    'phone' => [
      'name' => 'phone',
      'type' => 'text',
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
      'type' => 'text',
    ],
    'country' => [
      'name' => 'country',
      'type' => 'text',
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
      'searchable' => true,
      'type' => 'text',
      'required' => true,
    ],
    'first_name' => [
      'name' => 'first_name',
      'type' => 'text',
    ],
    'last_name' => [
      'name' => 'last_name',
      'type' => 'text',
    ],
    'email' => [
      'name' => 'email',
      'type' => 'email',
    ],
    'phone' => [
      'name' => 'phone',
      'type' => 'text',
    ],
    'position' => [
      'name' => 'position',
      'type' => 'text',
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
      'searchable' => true,
      'type' => 'text',
      'required' => true,
    ],
    'first_name' => [
      'name' => 'first_name',
      'type' => 'text',
    ],
    'last_name' => [
      'name' => 'last_name',
      'type' => 'text',
    ],
    'email' => [
      'name' => 'email',
      'type' => 'email',
    ],
    'phone' => [
      'name' => 'phone',
      'type' => 'text',
    ],
    'company' => [
      'name' => 'company',
      'type' => 'text',
    ],
    'street' => [
      'name' => 'street',
      'type' => 'longtext',
    ],
    'city' => [
      'name' => 'city',
      'type' => 'text',
    ],
    'zip' => [
      'name' => 'zip',
      'type' => 'text',
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
      'searchable' => true,
      'type' => 'text',
      'required' => true,
    ],
    'number' => [
      'name' => 'number',
      'type' => 'number',
    ],
    'status' => [
      'name' => 'status',
      'type' => 'select',
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
      'type' => 'select',
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
      'searchable' => true,
      'type' => 'text',
      'required' => true,
    ],
    'number' => [
      'name' => 'number',
      'type' => 'number',
    ],
    'status' => [
      'name' => 'status',
      'type' => 'select',
    ],
    'valid_until' => [
      'name' => 'valid_until',
      'type' => 'date',
    ],
    'currency' => [
      'name' => 'currency',
      'type' => 'select',
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
      'searchable' => true,
      'type' => 'text',
      'required' => true,
    ],
    'subject' => [
      'name' => 'subject',
      'type' => 'text',
    ],
    'description' => [
      'name' => 'description',
      'type' => 'longtext',
    ],
    'status' => [
      'name' => 'status',
      'type' => 'select',
    ],
    'priority' => [
      'name' => 'priority',
      'type' => 'select',
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
      'type' => 'text',
    ],
    'mailable_class' => [
      'name' => 'mailable_class',
      'type' => 'text',
    ],
    'related_id' => [
      'name' => 'related_id',
      'type' => 'text',
    ],
    'status' => [
      'name' => 'status',
      'type' => 'select',
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
      'searchable' => true,
      'type' => 'text',
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
      'type' => 'text',
    ],
    'status' => [
      'name' => 'status',
      'type' => 'select',
    ],
    'ip' => [
      'name' => 'ip',
      'type' => 'text',
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
      'searchable' => true,
      'type' => 'text',
      'required' => true,
    ],


    'amount' => [
      'name' => 'amount',
      'type' => 'text',
    ],

    'currency' => [
      'name' => 'currency',
      'type' => 'select',

    ],

    'description' => [
      'name' => 'description',
      'type' => 'longtext',
    ],

    'sales_stage' => [
      'name' => 'sales_stage',
      'type' => 'select',
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
      'type' => 'select',
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
      'searchable' => true,
      'type' => 'text',
      'required' => true,
    ],

    'sku' => [
      'name' => 'sku',
      'type' => 'text',
    ],

    'description' => [
      'name' => 'description',
      'type' => 'longtext',
    ],

    'category' => [
      'name' => 'category',
      'type' => 'select',
    ],

    'price' => [
      'name' => 'price',
      'type' => 'number',
    ],

    'currency' => [
      'name' => 'currency',
      'type' => 'select',
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
    'name' => [
      'name' => 'name',
      'searchable' => true,
      'type' => 'text',
      'required' => true,
    ],
    'order_number' => [
      'name' => 'order_number',
      'type' => 'text',
      'required' => true,
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
      'type' => 'select',
    ],

    'status' => [
      'name' => 'status',
      'type' => 'select',
    ],

    'order_date' => [
      'name' => 'order_date',
      'type' => 'date',
    ],

    'due_date' => [
      'name' => 'due_date',
      'type' => 'date',
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
