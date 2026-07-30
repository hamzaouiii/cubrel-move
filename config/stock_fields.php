<?php

return [
    'accounts' => [
        'website' => [
            'name' => 'website',
            'type' => 'url',
            'searchable' => true,
            'filterable' => true,
        ],
        'email' => [
            'name' => 'email',
            'type' => 'email',
            'searchable' => true,
            'filterable' => true,
        ],
        'phone' => [
            'name' => 'phone',
            'type' => 'phone',
            'searchable' => true,
            'filterable' => true,
        ],
        'billing_address' => [
            'name' => 'billing_address',
            'type' => 'address',
            'searchable' => true,
            'filterable' => true,
        ],
        'shipping_address' => [
            'name' => 'shipping_address',
            'type' => 'address',
            'searchable' => true,
            'filterable' => true,
        ],
    ],
    'contacts' => [
        'first_name' => [
            'name' => 'first_name',
            'type' => 'text',
            'searchable' => true,
            'filterable' => true,
        ],
        'last_name' => [
            'name' => 'last_name',
            'type' => 'text',
            'searchable' => true,
            'filterable' => true,
        ],
        'email' => [
            'name' => 'email',
            'type' => 'email',
            'searchable' => true,
            'filterable' => true,
        ],
        'phone' => [
            'name' => 'phone',
            'type' => 'phone',
            'searchable' => true,
            'filterable' => true,
        ],
        'position' => [
            'name' => 'position',
            'type' => 'text',
            'searchable' => true,
            'filterable' => true,
        ],
        'notes' => [
            'name' => 'notes',
            'type' => 'longtext',
            'searchable' => true,
        ],
    ],
    'leads' => [
        'first_name' => [
            'name' => 'first_name',
            'type' => 'text',
            'searchable' => true,
            'filterable' => true,
        ],
        'last_name' => [
            'name' => 'last_name',
            'type' => 'text',
            'searchable' => true,
            'filterable' => true,
        ],
        'email' => [
            'name' => 'email',
            'type' => 'email',
            'searchable' => true,
            'filterable' => true,
        ],
        'phone' => [
            'name' => 'phone',
            'type' => 'phone',
            'searchable' => true,
            'filterable' => true,
        ],
        'address' => [
            'name' => 'address',
            'type' => 'address',
            'searchable' => true,
            'filterable' => true,
        ],
    ],
    'invoices' => [
        'number' => [
            'name' => 'number',
            'type' => 'number',
            'searchable' => true,
            'filterable' => true,
        ],
        'status' => [
            'name' => 'status',
            'type' => 'status',
            'filterable' => true,
        ],
        'issue_date' => [
            'name' => 'issue_date',
            'type' => 'date',
            'filterable' => true,
        ],
        'due_date' => [
            'name' => 'due_date',
            'type' => 'date',
            'filterable' => true,
        ],
        'notes' => [
            'name' => 'notes',
            'type' => 'longtext',
            'searchable' => true,
        ],
    ],
    'quotes' => [
        'number' => [
            'name' => 'number',
            'type' => 'number',
            'searchable' => true,
            'filterable' => true,
        ],
        'status' => [
            'name' => 'status',
            'type' => 'status',
            'required' => true,
            'filterable' => true,
        ],
        'valid_until' => [
            'name' => 'valid_until',
            'type' => 'date',
            'filterable' => true,
        ],

        'notes' => [
            'name' => 'notes',
            'type' => 'longtext',
            'searchable' => true,
        ],
    ],
    'cases' => [
        'subject' => [
            'name' => 'subject',
            'type' => 'text',
            'searchable' => true,
            'filterable' => true,
        ],
        'status' => [
            'name' => 'status',
            'type' => 'status',
            'filterable' => true,
        ],
        'priority' => [
            'name' => 'priority',
            'type' => 'status',
            'filterable' => true,
        ],
        'opened_at' => [
            'name' => 'opened_at',
            'type' => 'datetime',
            'filterable' => true,
        ],
        'closed_at' => [
            'name' => 'closed_at',
            'type' => 'datetime',
            'filterable' => true,
        ],
    ],
    'deals' => [
        'amount' => [
            'name' => 'amount',
            'type' => 'currency',
            'filterable' => true,
        ],
        'sales_stage' => [
            'name' => 'sales_stage',
            'type' => 'status',
            'filterable' => true,
        ],
        'probability' => [
            'name' => 'probability',
            'type' => 'percentage',
            'filterable' => true,
        ],
        'expected_close_date' => [
            'name' => 'expected_close_date',
            'type' => 'date',
            'filterable' => true,
        ],
        'type' => [
            'name' => 'type',
            'type' => 'select',
            'filterable' => true,
        ],
    ],
    'products' => [
        'sku' => [
            'name' => 'sku',
            'type' => 'text',
            'searchable' => true,
            'filterable' => true,
        ],
        'category' => [
            'name' => 'category',
            'type' => 'select',
            'filterable' => true,
        ],
        'price' => [
            'name' => 'price',
            'type' => 'currency',
            'filterable' => true,
        ],

        'unit' => [
            'name' => 'unit',
            'type' => 'select',
            'required' => true,
            'filterable' => true,
        ],
        'tax_rate' => [
            'name' => 'tax_rate',
            'type' => 'percentage',
            'filterable' => true,
        ],
        'is_active' => [
            'name' => 'is_active',
            'type' => 'checkbox',
            'filterable' => true,
        ],
    ],
    'orders' => [
        'order_number' => [
            'name' => 'order_number',
            'type' => 'text',
            'required' => true,
            'searchable' => true,
            'filterable' => true,
        ],
        'status' => [
            'name' => 'status',
            'type' => 'status',
            'filterable' => true,
        ],
        'order_date' => [
            'name' => 'order_date',
            'type' => 'date',
            'filterable' => true,
        ],
        'due_date' => [
            'name' => 'due_date',
            'type' => 'date',
            'filterable' => true,
        ],
    ],
    'users' => [
        'username' => [
            'name' => 'username',
            'type' => 'text',
            'searchable' => true,
            'sortable' => true,
            'filterable' => true,
        ],
        'first_name' => [
            'name' => 'first_name',
            'type' => 'text',
            'searchable' => true,
            'filterable' => true,
        ],
        'last_name' => [
            'name' => 'last_name',
            'type' => 'text',
            'searchable' => true,
            'filterable' => true,
        ],
        'description' => [
            'name' => 'description',
            'type' => 'longtext',
            'searchable' => true,
        ],
        'email' => [
            'name' => 'email',
            'type' => 'email',
            'searchable' => true,
            'filterable' => true,
            'required'  => true,
        ],
        'status' => [
            'name' => 'status',
            'type' => 'status',
            'filterable' => true,
            'required'  => true,
        ],
        'is_admin' => [
            'name' => 'is_admin',
            'type' => 'checkbox',
            'filterable' => true,
        ],
        'title' => [
            'name' => 'title',
            'type' => 'select',
            'filterable' => true,
        ],
        'phone' => [
            'name' => 'phone',
            'type' => 'phone',
            'searchable' => true,
            'filterable' => true,
        ],
        'mobile' => [
            'name' => 'mobile',
            'type' => 'phone',
            'searchable' => true,
            'filterable' => true,
        ],
        'avatar' => [
            'name' => 'avatar',
            'type' => 'image',
        ],
        'locale' => [
            'name' => 'locale',
            'type' => 'select',
            'filterable' => true,
        ],
        'timezone' => [
            'name' => 'timezone',
            'type' => 'select',
            'filterable' => true,
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
            'filterable' => true,
        ],
    ],
    'userinvites' => [
        'invited_by' => [
            'name' => 'invited_by',
            'type' => 'record',
            'related_module' => 'users',
            'filterable' => true,
        ],
        'is_admin' => [
            'name' => 'is_admin',
            'type' => 'checkbox',
            'filterable' => true,
        ],
        'email' => [
            'name' => 'email',
            'type' => 'email',
            'searchable' => true,
            'filterable' => true,
        ],
        'status' => [
            'name' => 'status',
            'type' => 'status',
            'filterable' => true,
        ],
        'expires_at' => [
            'name' => 'expires_at',
            'type' => 'date',
            'filterable' => true,
        ],
    ],
    'tasks' => [
        'due_at' => [
            'name' => 'due_at',
            'type' => 'datetime',
            'required' => true,
            'filterable' => true,
        ],
        'status' => [
            'name' => 'status',
            'type' => 'status',
            'filterable' => true,
        ],
        'priority' => [
            'name' => 'priority',
            'type' => 'status',
            'filterable' => true,
        ],
        'completed_at' => [
            'name' => 'completed_at',
            'type' => 'datetime',
            'readonly' => true,
            'filterable' => true,
        ],
    ],
    'calls' => [
        'direction' => [
            'name' => 'direction',
            'type' => 'select',
            'filterable' => true,
        ],
        'call_at' => [
            'name' => 'call_at',
            'type' => 'datetime',
            'filterable' => true,
        ],
        'duration_minutes' => [
            'name' => 'duration_minutes',
            'type' => 'integer',
            'filterable' => true,
        ],
        'status' => [
            'name' => 'status',
            'type' => 'status',
            'filterable' => true,
        ],
        'outcome' => [
            'name' => 'outcome',
            'type' => 'select',
            'filterable' => true,
        ],
    ],
    'meetings' => [
        'location' => [
            'name' => 'location',
            'type' => 'address',
            'searchable' => true,
            'filterable' => true,
        ],
        'start_at' => [
            'name' => 'start_at',
            'type' => 'datetime',
            'required' => true,
            'filterable' => true,
        ],
        'end_at' => [
            'name' => 'end_at',
            'type' => 'datetime',
            'required' => true,
            'filterable' => true,
        ],
        'duration' => [
            'name' => 'duration',
            'type' => 'duration',
            'readonly' => true,
            'is_calculated' => true,
            'filterable' => true,
            'sortable' => true,
        ],
        'status' => [
            'name' => 'status',
            'type' => 'status',
            'filterable' => true,
        ],
    ],
    'emails' => [
        'body' => [
            'name' => 'body',
            'type' => 'longtext',
            'searchable' => true,
        ],
        'from_address' => [
            'name' => 'from_address',
            'type' => 'text',
            'searchable' => true,
            'filterable' => true,
        ],
        'from_name' => [
            'name' => 'from_name',
            'type' => 'text',
            'searchable' => true,
            'filterable' => true,
        ],
        'to_addresses' => [
            'name' => 'to_addresses',
            'type' => 'json',
        ],
        'cc_addresses' => [
            'name' => 'cc_addresses',
            'type' => 'json',
        ],
        'sent_at' => [
            'name' => 'sent_at',
            'type' => 'datetime',
            'filterable' => true,
            'sortable' => true,
        ],
        'direction' => [
            'name' => 'direction',
            'type' => 'select',
            'filterable' => true,
        ],
        'mailbox' => [
            'name' => 'mailbox',
            'type' => 'text',
            'filterable' => true,
        ],
    ],
    'line_items' => [
        'parent_type' => [
            'name' => 'parent_type',
            'type' => 'text',
            'filterable' => true,
        ],
        'parent_id' => [
            'name' => 'parent_id',
            'type' => 'record',
            'required' => true,
            'filterable' => true,
        ],
        'product_id' => [
            'name' => 'product_id',
            'type' => 'record',
            'filterable' => true,
        ],
        'name' => [
            'name' => 'name',
            'type' => 'text',
            'searchable' => true,
            'filterable' => true,
        ],
        'unit' => [
            'name' => 'unit',
            'type' => 'select',
            'required' => true,
            'filterable' => true,
        ],
        'unit_price' => [
            'name' => 'unit_price',
            'type' => 'currency',
            'filterable' => true,
        ],
        'quantity' => [
            'name' => 'quantity',
            'type' => 'decimal',
            'filterable' => true,
        ],
        'discount' => [
            'name' => 'discount',
            'type' => 'percentage',
            'filterable' => true,
        ],
        'tax_rate' => [
            'name' => 'tax_rate',
            'type' => 'percentage',
            'filterable' => true,
        ],
        'subtotal' => [
            'name' => 'subtotal',
            'type' => 'currency',
            'filterable' => true,
            'is_calculated' => true,
        ],
        'discount_amount' => [
            'name' => 'discount_amount',
            'type' => 'currency',
            'filterable' => true,
            'is_calculated' => true,
        ],
        'tax_amount' => [
            'name' => 'tax_amount',
            'type' => 'currency',
            'filterable' => true,
            'is_calculated' => true,
        ],
        'total' => [
            'name' => 'total',
            'type' => 'currency',
            'filterable' => true,
            'is_calculated' => true,
        ],
        'sort_order' => [
            'name' => 'sort_order',
            'type' => 'integer',
        ],
        'note' => [
            'name' => 'note',
            'type' => 'text',
            'searchable' => true,
        ],
    ],
];