<?php

return array (
  'accounts' => 
  array (
    'name' => 
    array (
      'name' => 'name',
      'searchable' => true,
      'type' => 'text',
      'required' => true,
    ),
    'website' => 
    array (
      'name' => 'website',
      'type' => 'text',
    ),
    'email' => 
    array (
      'name' => 'email',
      'type' => 'email',
    ),
    'phone' => 
    array (
      'name' => 'phone',
      'type' => 'text',
    ),
    'billing_address' => 
    array (
      'name' => 'billing_address',
      'type' => 'longtext',
    ),
    'shipping_address' => 
    array (
      'name' => 'shipping_address',
      'type' => 'longtext',
    ),
    'city' => 
    array (
      'name' => 'city',
      'type' => 'text',
    ),
    'country' => 
    array (
      'name' => 'country',
      'type' => 'text',
    ),
    'description' => 
    array (
      'name' => 'description',
      'type' => 'longtext',
    ),
    'created_at' => 
    array (
      'name' => 'created_at',
      'type' => 'datetime',
      'readonly' => true,
    ),
    'updated_at' => 
    array (
      'name' => 'updated_at',
      'type' => 'datetime',
      'readonly' => true,
    ),
  ),
  'contacts' => 
  array (
    'name' => 
    array (
      'name' => 'name',
      'searchable' => true,
      'type' => 'text',
      'required' => true,
    ),
    'first_name' => 
    array (
      'name' => 'first_name',
      'type' => 'text',
    ),
    'last_name' => 
    array (
      'name' => 'last_name',
      'type' => 'text',
    ),
    'email' => 
    array (
      'name' => 'email',
      'type' => 'email',
    ),
    'phone' => 
    array (
      'name' => 'phone',
      'type' => 'text',
    ),
    'position' => 
    array (
      'name' => 'position',
      'type' => 'text',
    ),
    'notes' => 
    array (
      'name' => 'notes',
      'type' => 'longtext',
    ),
    'description' => 
    array (
      'name' => 'description',
      'type' => 'longtext',
    ),
    'created_at' => 
    array (
      'name' => 'created_at',
      'type' => 'datetime',
      'readonly' => true,
    ),
    'updated_at' => 
    array (
      'name' => 'updated_at',
      'type' => 'datetime',
      'readonly' => true,
    ),
  ),
  'leads' => 
  array (
    'name' => 
    array (
      'name' => 'name',
      'searchable' => true,
      'type' => 'text',
      'required' => true,
    ),
    'first_name' => 
    array (
      'name' => 'first_name',
      'type' => 'text',
    ),
    'last_name' => 
    array (
      'name' => 'last_name',
      'type' => 'text',
    ),
    'email' => 
    array (
      'name' => 'email',
      'type' => 'email',
    ),
    'phone' => 
    array (
      'name' => 'phone',
      'type' => 'text',
    ),
    'company' => 
    array (
      'name' => 'company',
      'type' => 'text',
    ),
    'street' => 
    array (
      'name' => 'street',
      'type' => 'longtext',
    ),
    'city' => 
    array (
      'name' => 'city',
      'type' => 'text',
    ),
    'zip' => 
    array (
      'name' => 'zip',
      'type' => 'text',
    ),
    'description' => 
    array (
      'name' => 'description',
      'type' => 'longtext',
    ),
    'created_at' => 
    array (
      'name' => 'created_at',
      'type' => 'datetime',
      'readonly' => true,
    ),
    'updated_at' => 
    array (
      'name' => 'updated_at',
      'type' => 'datetime',
      'readonly' => true,
    ),
  ),
  'invoices' => 
  array (
    'name' => 
    array (
      'name' => 'name',
      'searchable' => true,
      'type' => 'text',
      'required' => true,
    ),
    'number' => 
    array (
      'name' => 'number',
      'type' => 'number',
    ),
    'status' => 
    array (
      'name' => 'status',
      'type' => 'select',
    ),
    'issue_date' => 
    array (
      'name' => 'issue_date',
      'type' => 'date',
    ),
    'due_date' => 
    array (
      'name' => 'due_date',
      'type' => 'date',
    ),
    'currency' => 
    array (
      'name' => 'currency',
      'type' => 'select',
    ),
    'subtotal' => 
    array (
      'name' => 'subtotal',
      'type' => 'number',
    ),
    'tax' => 
    array (
      'name' => 'tax',
      'type' => 'number',
    ),
    'total' => 
    array (
      'name' => 'total',
      'type' => 'number',
    ),
    'notes' => 
    array (
      'name' => 'notes',
      'type' => 'longtext',
    ),
    'description' => 
    array (
      'name' => 'description',
      'type' => 'longtext',
    ),
    'created_at' => 
    array (
      'name' => 'created_at',
      'type' => 'datetime',
      'readonly' => true,
    ),
    'updated_at' => 
    array (
      'name' => 'updated_at',
      'type' => 'datetime',
      'readonly' => true,
    ),
  ),
  'quotes' => 
  array (
    'name' => 
    array (
      'name' => 'name',
      'searchable' => true,
      'type' => 'text',
      'required' => true,
    ),
    'number' => 
    array (
      'name' => 'number',
      'type' => 'number',
    ),
    'status' => 
    array (
      'name' => 'status',
      'type' => 'select',
    ),
    'valid_until' => 
    array (
      'name' => 'valid_until',
      'type' => 'date',
    ),
    'currency' => 
    array (
      'name' => 'currency',
      'type' => 'select',
    ),
    'subtotal' => 
    array (
      'name' => 'subtotal',
      'type' => 'number',
    ),
    'tax' => 
    array (
      'name' => 'tax',
      'type' => 'number',
    ),
    'total' => 
    array (
      'name' => 'total',
      'type' => 'number',
    ),
    'notes' => 
    array (
      'name' => 'notes',
      'type' => 'longtext',
    ),
    'description' => 
    array (
      'name' => 'description',
      'type' => 'longtext',
    ),
    'created_at' => 
    array (
      'name' => 'created_at',
      'type' => 'datetime',
      'readonly' => true,
    ),
    'updated_at' => 
    array (
      'name' => 'updated_at',
      'type' => 'datetime',
      'readonly' => true,
    ),
  ),
  'cases' => 
  array (
    'name' => 
    array (
      'name' => 'name',
      'searchable' => true,
      'type' => 'text',
      'required' => true,
    ),
    'subject' => 
    array (
      'name' => 'subject',
      'type' => 'text',
    ),
    'description' => 
    array (
      'name' => 'description',
      'type' => 'longtext',
    ),
    'status' => 
    array (
      'name' => 'status',
      'type' => 'select',
    ),
    'priority' => 
    array (
      'name' => 'priority',
      'type' => 'select',
    ),
    'opened_at' => 
    array (
      'name' => 'opened_at',
      'type' => 'datetime',
    ),
    'closed_at' => 
    array (
      'name' => 'closed_at',
      'type' => 'datetime',
    ),
    'created_at' => 
    array (
      'name' => 'created_at',
      'type' => 'datetime',
      'readonly' => true,
    ),
    'updated_at' => 
    array (
      'name' => 'updated_at',
      'type' => 'datetime',
      'readonly' => true,
    ),
  ),
  'emails' => 
  array (
    'to' => 
    array (
      'name' => 'to',
      'type' => 'email',
    ),
    'subject' => 
    array (
      'name' => 'subject',
      'type' => 'text',
    ),
    'mailable_class' => 
    array (
      'name' => 'mailable_class',
      'type' => 'text',
    ),
    'related_id' => 
    array (
      'name' => 'related_id',
      'type' => 'text',
    ),
    'status' => 
    array (
      'name' => 'status',
      'type' => 'select',
    ),
    'description' => 
    array (
      'name' => 'description',
      'type' => 'longtext',
    ),
    'created_at' => 
    array (
      'name' => 'created_at',
      'type' => 'datetime',
      'readonly' => true,
    ),
    'updated_at' => 
    array (
      'name' => 'updated_at',
      'type' => 'datetime',
      'readonly' => true,
    ),
  ),
  'inquiries' => 
  array (
    'name' => 
    array (
      'name' => 'name',
      'searchable' => true,
      'type' => 'text',
      'required' => true,
    ),
    'message' => 
    array (
      'name' => 'message',
      'type' => 'longtext',
    ),
    'email' => 
    array (
      'name' => 'email',
      'type' => 'email',
    ),
    'phone' => 
    array (
      'name' => 'phone',
      'type' => 'text',
    ),
    'status' => 
    array (
      'name' => 'status',
      'type' => 'select',
    ),
    'ip' => 
    array (
      'name' => 'ip',
      'type' => 'text',
    ),
    'user_agent' => 
    array (
      'name' => 'user_agent',
      'type' => 'longtext',
    ),
    'description' => 
    array (
      'name' => 'description',
      'type' => 'longtext',
    ),
    'created_at' => 
    array (
      'name' => 'created_at',
      'type' => 'datetime',
      'readonly' => true,
    ),
    'updated_at' => 
    array (
      'name' => 'updated_at',
      'type' => 'datetime',
      'readonly' => true,
    ),
  ),
  'opportunities' => 
  array (
    'name' => 
    array (
      'name' => 'name',
      'searchable' => true,
      'type' => 'text',
      'required' => true,
    ),
    'amount' => 
    array (
      'name' => 'amount',
      'type' => 'text',
    ),
    'currency' => 
    array (
      'name' => 'currency',
      'type' => 'select',
    ),
    'description' => 
    array (
      'name' => 'description',
      'type' => 'longtext',
    ),
    'sales_stage' => 
    array (
      'name' => 'sales_stage',
      'type' => 'select',
    ),
    'probability' => 
    array (
      'name' => 'probability',
      'type' => 'number',
    ),
    'expected_close_date' => 
    array (
      'name' => 'expected_close_date',
      'type' => 'date',
    ),
    'type' => 
    array (
      'name' => 'type',
      'type' => 'select',
    ),
    'created_at' => 
    array (
      'name' => 'created_at',
      'type' => 'datetime',
      'readonly' => true,
    ),
    'updated_at' => 
    array (
      'name' => 'updated_at',
      'type' => 'datetime',
      'readonly' => true,
    ),
  ),
  'products' => 
  array (
    'name' => 
    array (
      'name' => 'name',
      'searchable' => true,
      'type' => 'text',
      'required' => true,
    ),
    'sku' => 
    array (
      'name' => 'sku',
      'type' => 'text',
    ),
    'description' => 
    array (
      'name' => 'description',
      'type' => 'longtext',
    ),
    'category' => 
    array (
      'name' => 'category',
      'type' => 'select',
    ),
    'price' => 
    array (
      'name' => 'price',
      'type' => 'number',
    ),
    'currency' => 
    array (
      'name' => 'currency',
      'type' => 'select',
    ),
    'is_active' => 
    array (
      'name' => 'is_active',
      'type' => 'checkbox',
    ),
    'created_at' => 
    array (
      'name' => 'created_at',
      'type' => 'datetime',
      'readonly' => true,
    ),
    'updated_at' => 
    array (
      'name' => 'updated_at',
      'type' => 'datetime',
      'readonly' => true,
    ),
  ),
  'orders' => 
  array (
    'name' => 
    array (
      'name' => 'name',
      'searchable' => true,
      'type' => 'text',
      'required' => true,
    ),
    'order_number' => 
    array (
      'name' => 'order_number',
      'type' => 'text',
      'required' => true,
    ),
    'description' => 
    array (
      'name' => 'description',
      'type' => 'longtext',
    ),
    'total_amount' => 
    array (
      'name' => 'total_amount',
      'type' => 'number',
    ),
    'currency' => 
    array (
      'name' => 'currency',
      'type' => 'select',
    ),
    'status' => 
    array (
      'name' => 'status',
      'type' => 'select',
    ),
    'order_date' => 
    array (
      'name' => 'order_date',
      'type' => 'date',
    ),
    'due_date' => 
    array (
      'name' => 'due_date',
      'type' => 'date',
    ),
    'created_at' => 
    array (
      'name' => 'created_at',
      'type' => 'datetime',
      'readonly' => true,
    ),
    'updated_at' => 
    array (
      'name' => 'updated_at',
      'type' => 'datetime',
      'readonly' => true,
    ),
  ),
);
