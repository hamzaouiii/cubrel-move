<?php

return [

  /*
    |--------------------------------------------------------------------------
    | ACCOUNTS CORE
    |--------------------------------------------------------------------------
    */

  [
    'name' => 'accounts_contacts',
    'label' => 'relationships.accounts_contacts',
    'left_module' => 'accounts',
    'left_class'  => App\Models\Modules\Account::class,
    'right_module' => 'contacts',
    'right_class'  => App\Models\Modules\Contact::class,
    'relationship_type' => 'one-to-many',
  ],

  [
    'name' => 'accounts_leads',
    'label' => 'relationships.accounts_leads',
    'left_module' => 'accounts',
    'left_class'  => App\Models\Modules\Account::class,
    'right_module' => 'leads',
    'right_class'  => App\Models\Modules\Lead::class,
    'relationship_type' => 'one-to-many',
  ],

  [
    'name' => 'accounts_opportunities',
    'label' => 'relationships.accounts_opportunities',
    'left_module' => 'accounts',
    'left_class'  => App\Models\Modules\Account::class,
    'right_module' => 'opportunities',
    'right_class'  => App\Models\Modules\Opportunity::class,
    'relationship_type' => 'one-to-many',
  ],

  [
    'name' => 'accounts_quotes',
    'label' => 'relationships.accounts_quotes',
    'left_module' => 'accounts',
    'left_class'  => App\Models\Modules\Account::class,
    'right_module' => 'quotes',
    'right_class'  => App\Models\Modules\Quote::class,
    'relationship_type' => 'one-to-many',
  ],

  [
    'name' => 'accounts_orders',
    'label' => 'relationships.accounts_orders',
    'left_module' => 'accounts',
    'left_class'  => App\Models\Modules\Account::class,
    'right_module' => 'orders',
    'right_class'  => App\Models\Modules\Order::class,
    'relationship_type' => 'one-to-many',
  ],

  [
    'name' => 'accounts_invoices',
    'label' => 'relationships.accounts_invoices',
    'left_module' => 'accounts',
    'left_class'  => App\Models\Modules\Account::class,
    'right_module' => 'invoices',
    'right_class'  => App\Models\Modules\Invoice::class,
    'relationship_type' => 'one-to-many',
  ],

  [
    'name' => 'accounts_cases',
    'label' => 'relationships.accounts_cases',
    'left_module' => 'accounts',
    'left_class'  => App\Models\Modules\Account::class,
    'right_module' => 'cases',
    'right_class'  => App\Models\Modules\SupportCase::class,
    'relationship_type' => 'one-to-many',
  ],

  [
    'name' => 'accounts_emails',
    'label' => 'relationships.accounts_emails',
    'left_module' => 'accounts',
    'left_class'  => App\Models\Modules\Account::class,
    'right_module' => 'emails',
    'right_class'  => App\Models\Modules\Email::class,
    'relationship_type' => 'one-to-many',
  ],

  [
    'name' => 'accounts_inquiries',
    'label' => 'relationships.accounts_inquiries',
    'left_module' => 'accounts',
    'left_class'  => App\Models\Modules\Account::class,
    'right_module' => 'inquiries',
    'right_class'  => App\Models\Modules\ContactMessage::class,
    'relationship_type' => 'one-to-many',
  ],

  /*
    |--------------------------------------------------------------------------
    | OPPORTUNITIES
    |--------------------------------------------------------------------------
    */

  [
    'name' => 'opportunities_contacts',
    'label' => 'relationships.opportunities_contacts',
    'left_module' => 'opportunities',
    'left_class'  => App\Models\Modules\Opportunity::class,
    'right_module' => 'contacts',
    'right_class'  => App\Models\Modules\Contact::class,
    'relationship_type' => 'many-to-many',
  ],

  [
    'name' => 'opportunities_quotes',
    'label' => 'relationships.opportunities_quotes',
    'left_module' => 'opportunities',
    'left_class'  => App\Models\Modules\Opportunity::class,
    'right_module' => 'quotes',
    'right_class'  => App\Models\Modules\Quote::class,
    'relationship_type' => 'one-to-many',
  ],

  [
    'name' => 'opportunities_orders',
    'label' => 'relationships.opportunities_orders',
    'left_module' => 'opportunities',
    'left_class'  => App\Models\Modules\Opportunity::class,
    'right_module' => 'orders',
    'right_class'  => App\Models\Modules\Order::class,
    'relationship_type' => 'one-to-many',
  ],

  [
    'name' => 'opportunities_products',
    'label' => 'relationships.opportunities_products',
    'left_module' => 'opportunities',
    'left_class'  => App\Models\Modules\Opportunity::class,
    'right_module' => 'products',
    'right_class'  => App\Models\Modules\Product::class,
    'relationship_type' => 'many-to-many',
  ],

  /*
    |--------------------------------------------------------------------------
    | QUOTES
    |--------------------------------------------------------------------------
    */

  [
    'name' => 'quotes_products',
    'label' => 'relationships.quotes_products',
    'left_module' => 'quotes',
    'left_class'  => App\Models\Modules\Quote::class,
    'right_module' => 'products',
    'right_class'  => App\Models\Modules\Product::class,
    'relationship_type' => 'many-to-many',
  ],

  [
    'name' => 'quotes_invoices',
    'label' => 'relationships.quotes_invoices',
    'left_module' => 'quotes',
    'left_class'  => App\Models\Modules\Quote::class,
    'right_module' => 'invoices',
    'right_class'  => App\Models\Modules\Invoice::class,
    'relationship_type' => 'one-to-one',
  ],

  /*
    |--------------------------------------------------------------------------
    | ORDERS
    |--------------------------------------------------------------------------
    */

  [
    'name' => 'orders_products',
    'label' => 'relationships.orders_products',
    'left_module' => 'orders',
    'left_class'  => App\Models\Modules\Order::class,
    'right_module' => 'products',
    'right_class'  => App\Models\Modules\Product::class,
    'relationship_type' => 'many-to-many',
  ],

  [
    'name' => 'orders_invoices',
    'label' => 'relationships.orders_invoices',
    'left_module' => 'orders',
    'left_class'  => App\Models\Modules\Order::class,
    'right_module' => 'invoices',
    'right_class'  => App\Models\Modules\Invoice::class,
    'relationship_type' => 'one-to-one',
  ],

  /*
    |--------------------------------------------------------------------------
    | CONTACTS
    |--------------------------------------------------------------------------
    */

  [
    'name' => 'contacts_leads',
    'label' => 'relationships.contacts_leads',
    'left_module' => 'contacts',
    'left_class'  => App\Models\Modules\Contact::class,
    'right_module' => 'leads',
    'right_class'  => App\Models\Modules\Lead::class,
    'relationship_type' => 'one-to-one',
  ],

  [
    'name' => 'contacts_invoices',
    'label' => 'relationships.contacts_invoices',
    'left_module' => 'contacts',
    'left_class'  => App\Models\Modules\Contact::class,
    'right_module' => 'invoices',
    'right_class'  => App\Models\Modules\Invoice::class,
    'relationship_type' => 'one-to-many',
  ],

  [
    'name' => 'contacts_cases',
    'label' => 'relationships.contacts_cases',
    'left_module' => 'contacts',
    'left_class'  => App\Models\Modules\Contact::class,
    'right_module' => 'cases',
    'right_class'  => App\Models\Modules\SupportCase::class,
    'relationship_type' => 'one-to-many',
  ],

  /*
    |--------------------------------------------------------------------------
    | COMMUNICATION
    |--------------------------------------------------------------------------
    */

  [
    'name' => 'leads_emails',
    'label' => 'relationships.leads_emails',
    'left_module' => 'leads',
    'left_class'  => App\Models\Modules\Lead::class,
    'right_module' => 'emails',
    'right_class'  => App\Models\Modules\Email::class,
    'relationship_type' => 'one-to-many',
  ],

  [
    'name' => 'contacts_emails',
    'label' => 'relationships.contacts_emails',
    'left_module' => 'contacts',
    'left_class'  => App\Models\Modules\Contact::class,
    'right_module' => 'emails',
    'right_class'  => App\Models\Modules\Email::class,
    'relationship_type' => 'one-to-many',
  ],

  [
    'name' => 'cases_emails',
    'label' => 'relationships.cases_emails',
    'left_module' => 'cases',
    'left_class'  => App\Models\Modules\SupportCase::class,
    'right_module' => 'emails',
    'right_class'  => App\Models\Modules\Email::class,
    'relationship_type' => 'one-to-many',
  ],

  [
    'name' => 'inquiries_emails',
    'label' => 'relationships.inquiries_emails',
    'left_module' => 'inquiries',
    'left_class'  => App\Models\Modules\ContactMessage::class,
    'right_module' => 'emails',
    'right_class'  => App\Models\Modules\Email::class,
    'relationship_type' => 'one-to-many',
  ],

];
