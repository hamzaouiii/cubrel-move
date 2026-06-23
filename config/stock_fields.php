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
        'company' => [
            'name' => 'company',
            'type' => 'text',
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
    'inquiries' => [
        'message' => [
            'name' => 'message',
            'type' => 'longtext',
            'searchable' => true,
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
        'status' => [
            'name' => 'status',
            'type' => 'status',
            'required' => true,
            'filterable' => true,
        ],
        'ip' => [
            'name' => 'ip',
            'type' => 'text',
            'searchable' => true,
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
        ],
        'status' => [
            'name' => 'status',
            'type' => 'status',
            'filterable' => true,
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
            'type' => 'url',
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
        ],
        'discount_amount' => [
            'name' => 'discount_amount',
            'type' => 'currency',
            'filterable' => true,
        ],
        'tax_amount' => [
            'name' => 'tax_amount',
            'type' => 'currency',
            'filterable' => true,
        ],
        'total' => [
            'name' => 'total',
            'type' => 'currency',
            'filterable' => true,
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