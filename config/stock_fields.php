<?php

return [
  'accounts' =>
  [
    'website' =>
    [
      'name' => 'website',
      'type' => 'url',
    ],
    'email' =>
    [
      'name' => 'email',
      'type' => 'email',
    ],
    'phone' =>
    [
      'name' => 'phone',
      'type' => 'phone',
    ],
    'billing_address' =>
    [
      'name' => 'billing_address',
      'type' => 'longtext',
    ],
    'shipping_address' =>
    [
      'name' => 'shipping_address',
      'type' => 'longtext',
    ],
    'city' =>
    [
      'name' => 'city',
      'type' => 'text',
    ],
    'country' =>
    [
      'name' => 'country',
      'type' => 'text',
    ],
  ],
  'contacts' =>
  [
    'first_name' =>
    [
      'name' => 'first_name',
      'type' => 'text',
    ],
    'last_name' =>
    [
      'name' => 'last_name',
      'type' => 'text',
    ],
    'email' =>
    [
      'name' => 'email',
      'type' => 'email',
    ],
    'phone' =>
    [
      'name' => 'phone',
      'type' => 'phone',
    ],
    'position' =>
    [
      'name' => 'position',
      'type' => 'text',
    ],
    'notes' =>
    [
      'name' => 'notes',
      'type' => 'longtext',
    ],
  ],
  'leads' =>
  [
    'first_name' =>
    [
      'name' => 'first_name',
      'type' => 'text',
    ],
    'last_name' =>
    [
      'name' => 'last_name',
      'type' => 'text',
    ],
    'email' =>
    [
      'name' => 'email',
      'type' => 'email',
    ],
    'phone' =>
    [
      'name' => 'phone',
      'type' => 'phone',
    ],
    'company' =>
    [
      'name' => 'company',
      'type' => 'text',
    ],
    'street' =>
    [
      'name' => 'street',
      'type' => 'longtext',
    ],
    'city' =>
    [
      'name' => 'city',
      'type' => 'text',
    ],
    'zip' =>
    [
      'name' => 'zip',
      'type' => 'text',
    ],
  ],
  'invoices' =>
  [
    'number' =>
    [
      'name' => 'number',
      'type' => 'number',
    ],
    'status' =>
    [
      'name' => 'status',
      'type' => 'select',
    ],
    'issue_date' =>
    [
      'name' => 'issue_date',
      'type' => 'date',
    ],
    'due_date' =>
    [
      'name' => 'due_date',
      'type' => 'date',
    ],
    'currency' =>
    [
      'name' => 'currency',
      'type' => 'select',
    ],
    'subtotal' =>
    [
      'name' => 'subtotal',
      'type' => 'number',
    ],
    'tax' =>
    [
      'name' => 'tax',
      'type' => 'number',
    ],
    'total' =>
    [
      'name' => 'total',
      'type' => 'number',
    ],
    'notes' =>
    [
      'name' => 'notes',
      'type' => 'longtext',
    ],
  ],
  'quotes' =>
  [
    'number' =>
    [
      'name' => 'number',
      'type' => 'number',
    ],
    'status' =>
    [
      'name' => 'status',
      'type' => 'select',
    ],
    'valid_until' =>
    [
      'name' => 'valid_until',
      'type' => 'date',
    ],
    'currency' =>
    [
      'name' => 'currency',
      'type' => 'select',
    ],
    'subtotal' =>
    [
      'name' => 'subtotal',
      'type' => 'number',
    ],
    'tax' =>
    [
      'name' => 'tax',
      'type' => 'number',
    ],
    'total' =>
    [
      'name' => 'total',
      'type' => 'number',
    ],
    'notes' =>
    [
      'name' => 'notes',
      'type' => 'longtext',
    ],
  ],
  'cases' =>
  [
    'subject' =>
    [
      'name' => 'subject',
      'type' => 'text',
    ],
    'status' =>
    [
      'name' => 'status',
      'type' => 'select',
    ],
    'priority' =>
    [
      'name' => 'priority',
      'type' => 'select',
    ],
    'opened_at' =>
    [
      'name' => 'opened_at',
      'type' => 'datetime',
    ],
    'closed_at' =>
    [
      'name' => 'closed_at',
      'type' => 'datetime',
    ],
  ],
  'emails' =>
  [
    'to' =>
    [
      'name' => 'to',
      'type' => 'email',
    ],
    'subject' =>
    [
      'name' => 'subject',
      'type' => 'text',
    ],
    'mailable_class' =>
    [
      'name' => 'mailable_class',
      'type' => 'text',
    ],
    'related_id' =>
    [
      'name' => 'related_id',
      'type' => 'text',
    ],
    'status' =>
    [
      'name' => 'status',
      'type' => 'select',
    ],
  ],
  'inquiries' =>
  [
    'message' =>
    [
      'name' => 'message',
      'type' => 'longtext',
    ],
    'email' =>
    [
      'name' => 'email',
      'type' => 'email',
    ],
    'phone' =>
    [
      'name' => 'phone',
      'type' => 'phone',
    ],
    'status' =>
    [
      'name' => 'status',
      'type' => 'select',
    ],
    'ip' =>
    [
      'name' => 'ip',
      'type' => 'text',
    ],
    'user_agent' =>
    [
      'name' => 'user_agent',
      'type' => 'longtext',
    ],
  ],
  'opportunities' =>
  [
    'amount' =>
    [
      'name' => 'amount',
      'type' => 'text',
    ],
    'currency' =>
    [
      'name' => 'currency',
      'type' => 'select',
    ],
    'sales_stage' =>
    [
      'name' => 'sales_stage',
      'type' => 'select',
    ],
    'probability' =>
    [
      'name' => 'probability',
      'type' => 'number',
    ],
    'expected_close_date' =>
    [
      'name' => 'expected_close_date',
      'type' => 'date',
    ],
    'type' =>
    [
      'name' => 'type',
      'type' => 'select',
    ],
  ],
  'products' =>
  [
    'sku' =>
    [
      'name' => 'sku',
      'type' => 'text',
    ],
    'category' =>
    [
      'name' => 'category',
      'type' => 'select',
    ],
    'price' =>
    [
      'name' => 'price',
      'type' => 'number',
    ],
    'currency' =>
    [
      'name' => 'currency',
      'type' => 'select',
    ],
    'is_active' =>
    [
      'name' => 'is_active',
      'type' => 'checkbox',
    ],
  ],
  'orders' =>
  [
    'order_number' =>
    [
      'name' => 'order_number',
      'type' => 'text',
      'required' => true,
    ],
    'total_amount' =>
    [
      'name' => 'total_amount',
      'type' => 'number',
    ],
    'currency' =>
    [
      'name' => 'currency',
      'type' => 'select',
    ],
    'status' =>
    [
      'name' => 'status',
      'type' => 'select',
    ],
    'order_date' =>
    [
      'name' => 'order_date',
      'type' => 'date',
    ],
    'due_date' =>
    [
      'name' => 'due_date',
      'type' => 'date',
    ],
  ],
];
