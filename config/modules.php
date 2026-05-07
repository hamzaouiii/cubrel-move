<?php

return [

  /*
    |--------------------------------------------------------------------------
    | SALES
    |--------------------------------------------------------------------------
    */

  [
    'name' => 'Leads',
    'slug' => 'leads',
    'label' => 'modules.leads.label',
    'single_label' => 'modules.leads.single_label',
    'icon' => 'fa-solid fa-user-plus',
    'color' => '#9d8f4d',
    'path' => '/leads',
    'sort_order' => 10,
    'category' => 'sales',
    'is_active' => true,
    'show_in_sidebar' => 1,
    'handler_class' => 'App\\Handlers\\Modules\\LeadsModuleHandler',
    'description' => 'Prospective customers with interest.',

    'model_class' => 'App\\Models\\Modules\\Lead',
    'table_name' => 'leads',
    'is_custom' => 0,
  ],

  [
    'name' => 'Accounts',
    'slug' => 'accounts',
    'label' => 'modules.accounts.label',
    'single_label' => 'modules.accounts.single_label',
    'icon' => 'fa-solid fa-building',
    'color' => '#0d6efd',
    'path' => '/accounts',
    'sort_order' => 20,
    'category' => 'sales',
    'is_active' => true,
    'show_in_sidebar' => 1,
    'handler_class' => 'App\\Handlers\\Modules\\AccountsModuleHandler',
    'description' => 'Company records and organizations.',

    'model_class' => 'App\\Models\\Modules\\Account',
    'table_name' => 'accounts',
    'is_custom' => 0,
  ],

  [
    'name' => 'Contacts',
    'slug' => 'contacts',
    'label' => 'modules.contacts.label',
    'single_label' => 'modules.contacts.single_label',
    'icon' => 'fa-solid fa-arrows-down-to-people',
    'color' => '#669b6c',
    'path' => '/contacts',
    'sort_order' => 30,
    'category' => 'sales',
    'is_active' => true,
    'show_in_sidebar' => 1,
    'handler_class' => 'App\\Handlers\\Modules\\ContactsModuleHandler',
    'description' => 'People assigned to accounts or leads.',

    'model_class' => 'App\\Models\\Modules\\Contact',
    'table_name' => 'contacts',
    'is_custom' => 0,
  ],

  [
    'name' => 'Opportunities',
    'slug' => 'opportunities',
    'label' => 'modules.opportunities.label',
    'single_label' => 'modules.opportunities.single_label',
    'icon' => 'fa-solid fa-money-bills',
    'color' => '#8080c0',
    'path' => '/opportunities',
    'sort_order' => 40,
    'category' => 'sales',
    'is_active' => true,
    'show_in_sidebar' => 1,
    'handler_class' => 'App\\Handlers\\Modules\\OpportunitiesModuleHandler',
    'description' => 'Sales deals and revenue opportunities.',

    'model_class' => 'App\\Models\\Modules\\Opportunity',
    'table_name' => 'opportunities',
    'is_custom' => 0,
  ],

  /*
    |--------------------------------------------------------------------------
    | REVENUE
    |--------------------------------------------------------------------------
    */

  [
    'name' => 'Quotes',
    'slug' => 'quotes',
    'label' => 'modules.quotes.label',
    'single_label' => 'modules.quotes.single_label',
    'icon' => 'fa-solid fa-file-invoice-dollar',
    'color' => '#ffc107',
    'path' => '/quotes',
    'sort_order' => 50,
    'category' => 'revenue',
    'is_active' => true,
    'show_in_sidebar' => 1,
    'handler_class' => 'App\\Handlers\\Modules\\QuotesModuleHandler',
    'description' => 'Sales proposals and quotes.',

    'model_class' => 'App\\Models\\Modules\\Quote',
    'table_name' => 'quotes',
    'is_custom' => 0,
  ],

  [
    'name' => 'Orders',
    'slug' => 'orders',
    'label' => 'modules.orders.label',
    'single_label' => 'modules.orders.single_label',
    'icon' => 'fa-solid fa-shopping-basket',
    'color' => '#800000',
    'path' => '/orders',
    'sort_order' => 60,
    'category' => 'revenue',
    'is_active' => true,
    'show_in_sidebar' => 1,
    'handler_class' => 'App\\Handlers\\Modules\\OrdersModuleHandler',
    'description' => 'Customer orders and confirmations.',

    'model_class' => 'App\\Models\\Modules\\Order',
    'table_name' => 'orders',
    'is_custom' => 0,
  ],

  [
    'name' => 'Invoices',
    'slug' => 'invoices',
    'label' => 'modules.invoices.label',
    'single_label' => 'modules.invoices.single_label',
    'icon' => 'fa-solid fa-file-invoice',
    'color' => '#fd7e14',
    'path' => '/invoices',
    'sort_order' => 70,
    'category' => 'revenue',
    'is_active' => true,
    'show_in_sidebar' => 1,
    'handler_class' => 'App\\Handlers\\Modules\\InvoicesModuleHandler',
    'description' => 'Customer invoices and billing.',

    'model_class' => 'App\\Models\\Modules\\Invoice',
    'table_name' => 'invoices',
    'is_custom' => 0,
  ],

  [
    'name' => 'Products',
    'slug' => 'products',
    'label' => 'modules.products.label',
    'single_label' => 'modules.products.single_label',
    'icon' => 'fa-solid fa-expand',
    'color' => '#b68354',
    'path' => '/products',
    'sort_order' => 80,
    'category' => 'revenue',
    'is_active' => true,
    'show_in_sidebar' => 1,
    'handler_class' => 'App\\Handlers\\Modules\\ProductsModuleHandler',
    'description' => 'Product and service catalog.',

    'model_class' => 'App\\Models\\Modules\\Product',
    'table_name' => 'products',
    'is_custom' => 0,
  ],

  /*
    |--------------------------------------------------------------------------
    | SUPPORT
    |--------------------------------------------------------------------------
    */

  [
    'name' => 'Cases',
    'slug' => 'cases',
    'label' => 'modules.cases.label',
    'single_label' => 'modules.cases.single_label',
    'icon' => 'fa-solid fa-life-ring',
    'color' => '#dc3545',
    'path' => '/cases',
    'sort_order' => 90,
    'category' => 'support',
    'is_active' => true,
    'show_in_sidebar' => 1,
    'handler_class' => 'App\\Handlers\\Modules\\CasesModuleHandler',
    'description' => 'Support requests, bugs, tickets.',

    'model_class' => 'App\\Models\\Modules\\SupportCase',
    'table_name' => 'cases',
    'is_custom' => 0,
  ],

  /*
    |--------------------------------------------------------------------------
    | COMMUNICATION
    |--------------------------------------------------------------------------
    */

  [
    'name' => 'Emails',
    'slug' => 'emails',
    'label' => 'modules.emails.label',
    'single_label' => 'modules.emails.single_label',
    'icon' => 'fa-solid fa-envelope',
    'color' => '#0dcaf0',
    'path' => '/emails',
    'sort_order' => 100,
    'category' => 'communication',
    'is_active' => true,
    'show_in_sidebar' => 1,
    'handler_class' => 'App\\Handlers\\Modules\\EmailModuleHandler',
    'description' => 'Email communication module.',
    'model_class' => 'App\\Models\\Modules\\Email',
    'table_name' => null,
    'is_custom' => 0,
  ],

  [
    'name' => 'Inquiries',
    'slug' => 'inquiries',
    'label' => 'modules.inquiries.label',
    'single_label' => 'modules.inquiries.single_label',
    'icon' => 'fa-solid fa-mail-bulk',
    'color' => '#cceb34',
    'path' => '/inquiries',
    'sort_order' => 110,
    'category' => 'communication',
    'is_active' => true,
    'show_in_sidebar' => 1,
    'handler_class' => 'App\\Handlers\\Modules\\InquiriesModuleHandler',
    'description' => 'Manage messages from the contact form.',

    'model_class' => 'App\\Models\\Modules\\ContactMessage',
    'table_name' => 'contact_messages',
    'is_custom' => 0,
  ],

  /*
    |--------------------------------------------------------------------------
    | SYSTEM
    |--------------------------------------------------------------------------
    */

  [
    'name' => 'Settings',
    'slug' => 'settings',
    'label' => 'modules.settings.label',
    'single_label' => 'modules.settings.single_label',
    'icon' => 'fa-gears',
    'color' => '#0F172A',
    'path' => '/settings',
    'sort_order' => 999,
    'category' => 'system',
    'is_active' => false,
    'show_in_sidebar' => 1,
    'handler_class' => 'App\\Handlers\\Modules\\SettingsModuleHandler',
    'description' => 'Application settings.',

    'model_class' => 'App\\Models\\Modules\\Settings',
    'table_name' => 'settings',
    'is_custom' => 0,
  ],
  [
    'name' => 'Users',
    'slug' => 'users',
    'label' => 'modules.users.label',
    'single_label' => 'modules.settings.user',
    'icon' => 'fa-solid fa-users-gear',
    'color' => '#78909C',
    'path' => '/users',
    'sort_order' => 998,
    'category' => 'system',
    'is_active' => true,
    'show_in_sidebar' => 1,
    'handler_class' => 'App\\Handlers\\Modules\\UserModuleHandler',
    'description' => 'Users',

    'model_class' => 'App\\Models\\User',
    'table_name' => 'users',
    'is_custom' => 0,
  ],
  [
    'name' => 'User Invites',
    'slug' => 'userinvites',
    'label' => 'modules.userinvites.label',
    'single_label' => 'modules.userinvites.single_label',
    'icon' => 'fa-paper-plane',
    'color' => '#032b3f',
    'path' => '/users/invites',
    'sort_order' => 998,
    'category' => 'system',
    'is_active' => true,
    'show_in_sidebar' => 0,
    'handler_class' => 'App\\Handlers\\Modules\\UserInviteModuleHandler',
    'description' => 'Users Invites',
    'model_class' => 'App\\Models\\UserInvite',
    'table_name' => 'user_invites',
    'is_custom' => 0,
  ],


];
