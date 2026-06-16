<?php

return [
    'accounts' => [
        'website' => [
            'name' => 'website',
            'type' => 'url',
        ],
        'email' => [
            'name' => 'email',
            'type' => 'email',
        ],
        'phone' => [
            'name' => 'phone',
            'type' => 'phone',
        ],
        'billing_address' => [
            'name' => 'billing_address',
            'type' => 'address',
        ],
        'shipping_address' => [
            'name' => 'shipping_address',
            'type' => 'address',
        ],
    ],
    'contacts' => [
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
            'type' => 'phone',
        ],
        'position' => [
            'name' => 'position',
            'type' => 'text',
        ],
        'notes' => [
            'name' => 'notes',
            'type' => 'longtext',
        ],
    ],
    'leads' => [
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
            'type' => 'phone',
        ],
        'company' => [
            'name' => 'company',
            'type' => 'text',
        ],
        'address' => [
            'name' => 'address',
            'type' => 'address',
        ],
    ],
    'invoices' => [
        'number' => [
            'name' => 'number',
            'type' => 'number',
        ],
        'status' => [
            'name' => 'status',
            'type' => 'status',
        ],
        'issue_date' => [
            'name' => 'issue_date',
            'type' => 'date',
        ],
        'due_date' => [
            'name' => 'due_date',
            'type' => 'date',
        ],
        'notes' => [
            'name' => 'notes',
            'type' => 'longtext',
        ],
    ],
    'quotes' => [
        'number' => [
            'name' => 'number',
            'type' => 'number',
        ],
        'status' => [
            'name' => 'status',
            'type' => 'status',
            'required' => true,
        ],
        'valid_until' => [
            'name' => 'valid_until',
            'type' => 'date',
        ],
  
        'notes' => [
            'name' => 'notes',
            'type' => 'longtext',
        ],
    ],
    'cases' => [
        'subject' => [
            'name' => 'subject',
            'type' => 'text',
        ],
        'status' => [
            'name' => 'status',
            'type' => 'status',
        ],
        'priority' => [
            'name' => 'priority',
            'type' => 'status',
        ],
        'opened_at' => [
            'name' => 'opened_at',
            'type' => 'datetime',
        ],
        'closed_at' => [
            'name' => 'closed_at',
            'type' => 'datetime',
        ],
    ],
    'inquiries' => [
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
            'type' => 'phone',
        ],
        'status' => [
            'name' => 'status',
            'type' => 'status',
            'required' => true,
        ],
        'ip' => [
            'name' => 'ip',
            'type' => 'text',
        ],
        'user_agent' => [
            'name' => 'user_agent',
            'type' => 'longtext',
        ],
    ],
    'deals' => [
        'amount' => [
            'name' => 'amount',
            'type' => 'currency',
        ],
        'sales_stage' => [
            'name' => 'sales_stage',
            'type' => 'status',
        ],
        'probability' => [
            'name' => 'probability',
            'type' => 'percentage',
        ],
        'expected_close_date' => [
            'name' => 'expected_close_date',
            'type' => 'date',
        ],
        'type' => [
            'name' => 'type',
            'type' => 'select',
        ],
    ],
    'products' => [
        'sku' => [
            'name' => 'sku',
            'type' => 'text',
        ],
        'category' => [
            'name' => 'category',
            'type' => 'select',
        ],
        'price' => [
            'name' => 'price',
            'type' => 'currency',
        ],

        'unit' => [
            'name' => 'unit',
            'type' => 'select',
            'required' => true,
        ],
        'tax_rate' => [
            'name' => 'tax_rate',
            'type' => 'percentage',
        ],
        'is_active' => [
            'name' => 'is_active',
            'type' => 'checkbox',
        ],
    ],
    'orders' => [
        'order_number' => [
            'name' => 'order_number',
            'type' => 'text',
            'required' => true,
        ],
        'status' => [
            'name' => 'status',
            'type' => 'status',
        ],
        'order_date' => [
            'name' => 'order_date',
            'type' => 'date',
        ],
        'due_date' => [
            'name' => 'due_date',
            'type' => 'date',
        ],
    ],
    'users' => [
        'username' => [
            'name' => 'username',
            'type' => 'text',
            'searchable' => true,
            'sortable' => true,
        ],
        'first_name' => [
            'name' => 'first_name',
            'type' => 'text',
        ],
        'last_name' => [
            'name' => 'last_name',
            'type' => 'text',
        ],
        'description' => [
            'name' => 'description',
            'type' => 'longtext',
        ],
        'email' => [
            'name' => 'email',
            'type' => 'email',
        ],
        'status' => [
            'name' => 'status',
            'type' => 'status',
        ],
        'is_admin' => [
            'name' => 'is_admin',
            'type' => 'checkbox',
        ],
        'title' => [
            'name' => 'title',
            'type' => 'select',
        ],
        'phone' => [
            'name' => 'phone',
            'type' => 'phone',
        ],
        'mobile' => [
            'name' => 'mobile',
            'type' => 'phone',
        ],
        'avatar' => [
            'name' => 'avatar',
            'type' => 'url',
        ],
        'locale' => [
            'name' => 'locale',
            'type' => 'select',
        ],
        'timezone' => [
            'name' => 'timezone',
            'type' => 'select',
        ],
        'date_format' => [
            'name' => 'date_format',
            'type' => 'select',
        ],
        'time_format' => [
            'name' => 'time_format',
            'type' => 'select',
        ],
        'type' => [
            'name' => 'type',
            'type' => 'select',
        ],
    ],
    'userinvites' => [
        'invited_by' => [
            'name' => 'invited_by',
            'type' => 'record',
            'related_module' => 'users',
        ],
        'is_admin' => [
            'name' => 'is_admin',
            'type' => 'checkbox',
        ],
        'email' => [
            'name' => 'email',
            'type' => 'email',
        ],
        'status' => [
            'name' => 'status',
            'type' => 'status',
        ],
        'expires_at' => [
            'name' => 'expires_at',
            'type' => 'date',
        ],
    ],
    'line_items' => [
        'parent_type' => [
            'name' => 'parent_type',
            'type' => 'text',
        ],
        'parent_id' => [
            'name' => 'parent_id',
            'type' => 'record',
            'required' => true,
        ],
        'product_id' => [
            'name' => 'product_id',
            'type' => 'record',
        ],
        'name' => [
            'name' => 'name',
            'type' => 'text',
        ],
        'unit' => [
            'name' => 'unit',
            'type' => 'select',
            'required' => true,
        ],
        'unit_price' => [
            'name' => 'unit_price',
            'type' => 'currency',
        ],
        'quantity' => [
            'name' => 'quantity',
            'type' => 'decimal',
        ],
        'discount' => [
            'name' => 'discount',
            'type' => 'percentage',
        ],
        'tax_rate' => [
            'name' => 'tax_rate',
            'type' => 'percentage',
        ],
        'subtotal' => [
            'name' => 'subtotal',
            'type' => 'currency',
        ],
        'discount_amount' => [
            'name' => 'discount_amount',
            'type' => 'currency',
        ],
        'tax_amount' => [
            'name' => 'tax_amount',
            'type' => 'currency',

        ],
        'total' => [
            'name' => 'total',
            'type' => 'currency',
        ],
        'sort_order' => [
            'name' => 'sort_order',
            'type' => 'integer',
        ],
        'note' => [
            'name' => 'note',
            'type' => 'text',
        ],
    ],
];
