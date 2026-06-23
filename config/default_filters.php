<?php
/**
 * System-defined list filters, seeded by Database\Seeders\DefaultFiltersSeeder.
 *
 * Entries without a 'module_slug' are seeded as global filters (`is_global` on
 * `list_filters`), applying across all modules. Entries with a 'module_slug' are
 * scoped to that module only. Either way, a filter is only ever shown/applicable
 * on a module if all of its condition fields exist on that module (checked at
 * read-time by FilterQueryBuilder::isApplicable), so e.g. 'my_records' simply
 * won't appear on modules with no owner_id field.
 */
return [
  'my_records' => [
    'name' => 'My Records',
    'label' => 'modules.filters.my_records',
    'is_shared' => true,
    'is_system' => true,
    'match_type' => 'all',
    'conditions' => [
      ['field' => 'owner_id', 'operator' => 'equals', 'value' => '@current_user'],
    ],
  ],

  'orders_open' => [
    'module_slug' => 'orders',
    'name' => 'Open Orders',
    'label' => 'modules.filters.orders_open',
    'is_shared' => true,
    'is_system' => true,
    'match_type' => 'all',
    'conditions' => [
      ['field' => 'status', 'operator' => 'in', 'value' => ['pending', 'confirmed', 'processing']],
    ],
  ],
  'orders_completed' => [
    'module_slug' => 'orders',
    'name' => 'Completed Orders',
    'label' => 'modules.filters.orders_completed',
    'is_shared' => true,
    'is_system' => true,
    'match_type' => 'all',
    'conditions' => [
      ['field' => 'status', 'operator' => 'equals', 'value' => 'completed'],
    ],
  ],

  'invoices_unpaid' => [
    'module_slug' => 'invoices',
    'name' => 'Unpaid Invoices',
    'label' => 'modules.filters.invoices_unpaid',
    'is_shared' => true,
    'is_system' => true,
    'match_type' => 'all',
    'conditions' => [
      ['field' => 'status', 'operator' => 'in', 'value' => ['sent', 'viewed', 'partial', 'overdue']],
    ],
  ],
  'invoices_overdue' => [
    'module_slug' => 'invoices',
    'name' => 'Overdue Invoices',
    'label' => 'modules.filters.invoices_overdue',
    'is_shared' => true,
    'is_system' => true,
    'match_type' => 'all',
    'conditions' => [
      ['field' => 'status', 'operator' => 'equals', 'value' => 'overdue'],
    ],
  ],

  'quotes_pending' => [
    'module_slug' => 'quotes',
    'name' => 'Pending Quotes',
    'label' => 'modules.filters.quotes_pending',
    'is_shared' => true,
    'is_system' => true,
    'match_type' => 'all',
    'conditions' => [
      ['field' => 'status', 'operator' => 'in', 'value' => ['draft', 'sent', 'viewed']],
    ],
  ],
  'quotes_accepted' => [
    'module_slug' => 'quotes',
    'name' => 'Accepted Quotes',
    'label' => 'modules.filters.quotes_accepted',
    'is_shared' => true,
    'is_system' => true,
    'match_type' => 'all',
    'conditions' => [
      ['field' => 'status', 'operator' => 'equals', 'value' => 'accepted'],
    ],
  ],

  'deals_open' => [
    'module_slug' => 'deals',
    'name' => 'Open Deals',
    'label' => 'modules.filters.deals_open',
    'is_shared' => true,
    'is_system' => true,
    'match_type' => 'all',
    'conditions' => [
      ['field' => 'sales_stage', 'operator' => 'not_equals', 'value' => 'closed_won'],
      ['field' => 'sales_stage', 'operator' => 'not_equals', 'value' => 'closed_lost'],
    ],
  ],
  'deals_won' => [
    'module_slug' => 'deals',
    'name' => 'Won Deals',
    'label' => 'modules.filters.deals_won',
    'is_shared' => true,
    'is_system' => true,
    'match_type' => 'all',
    'conditions' => [
      ['field' => 'sales_stage', 'operator' => 'equals', 'value' => 'closed_won'],
    ],
  ],

  'cases_open' => [
    'module_slug' => 'cases',
    'name' => 'Open Cases',
    'label' => 'modules.filters.cases_open',
    'is_shared' => true,
    'is_system' => true,
    'match_type' => 'all',
    'conditions' => [
      ['field' => 'status', 'operator' => 'in', 'value' => ['open', 'in_progress', 'pending_input']],
    ],
  ],
  'cases_urgent' => [
    'module_slug' => 'cases',
    'name' => 'Urgent Cases',
    'label' => 'modules.filters.cases_urgent',
    'is_shared' => true,
    'is_system' => true,
    'match_type' => 'all',
    'conditions' => [
      ['field' => 'priority', 'operator' => 'equals', 'value' => 'urgent'],
    ],
  ],

  'inquiries_unresolved' => [
    'module_slug' => 'inquiries',
    'name' => 'Unresolved Inquiries',
    'label' => 'modules.filters.inquiries_unresolved',
    'is_shared' => true,
    'is_system' => true,
    'match_type' => 'all',
    'conditions' => [
      ['field' => 'status', 'operator' => 'in', 'value' => ['new', 'acknowledged', 'in_progress', 'waiting_response']],
    ],
  ],

  'products_active' => [
    'module_slug' => 'products',
    'name' => 'Active Products',
    'label' => 'modules.filters.products_active',
    'is_shared' => true,
    'is_system' => true,
    'match_type' => 'all',
    'conditions' => [
      ['field' => 'is_active', 'operator' => 'equals', 'value' => true],
    ],
  ],

  'users_active' => [
    'module_slug' => 'users',
    'name' => 'Active Users',
    'label' => 'modules.filters.users_active',
    'is_shared' => true,
    'is_system' => true,
    'match_type' => 'all',
    'conditions' => [
      ['field' => 'status', 'operator' => 'equals', 'value' => 'active'],
    ],
  ],

  'userinvites_pending' => [
    'module_slug' => 'userinvites',
    'name' => 'Pending Invites',
    'label' => 'modules.filters.userinvites_pending',
    'is_shared' => true,
    'is_system' => true,
    'match_type' => 'all',
    'conditions' => [
      ['field' => 'status', 'operator' => 'equals', 'value' => 'pending'],
    ],
  ],
];
