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
        'right_module' => 'contacts',
        'type' => 'one-to-many',
    ],

    [
        'name' => 'accounts_deals',
        'label' => 'relationships.accounts_deals',
        'left_module' => 'accounts',
        'right_module' => 'deals',
        'type' => 'one-to-many',
    ],

    [
        'name' => 'accounts_quotes',
        'label' => 'relationships.accounts_quotes',
        'left_module' => 'accounts',
        'right_module' => 'quotes',
        'type' => 'one-to-many',
    ],

    [
        'name' => 'accounts_orders',
        'label' => 'relationships.accounts_orders',
        'left_module' => 'accounts',
        'right_module' => 'orders',
        'type' => 'one-to-many',
    ],

    [
        'name' => 'accounts_invoices',
        'label' => 'relationships.accounts_invoices',
        'left_module' => 'accounts',
        'right_module' => 'invoices',
        'type' => 'one-to-many',
    ],

    [
        'name' => 'accounts_cases',
        'label' => 'relationships.accounts_cases',
        'left_module' => 'accounts',
        'right_module' => 'cases',
        'type' => 'one-to-many',
    ],


    /*
    |--------------------------------------------------------------------------
    | OPPORTUNITIES
    |--------------------------------------------------------------------------
    */

    [
        'name' => 'deals_contacts',
        'label' => 'relationships.deals_contacts',
        'left_module' => 'deals',
        'right_module' => 'contacts',
        'type' => 'many-to-many',
    ],

    [
        'name' => 'deals_quotes',
        'label' => 'relationships.deals_quotes',
        'left_module' => 'deals',
        'right_module' => 'quotes',
        'type' => 'one-to-many',
    ],

    [
        'name' => 'deals_orders',
        'label' => 'relationships.deals_orders',
        'left_module' => 'deals',
        'right_module' => 'orders',
        'type' => 'one-to-many',
    ],

    [
        'name' => 'deals_products',
        'label' => 'relationships.deals_products',
        'left_module' => 'deals',
        'right_module' => 'products',
        'type' => 'many-to-many',
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
        'right_module' => 'products',
        'type' => 'many-to-many',
    ],

    [
        'name' => 'quotes_invoices',
        'label' => 'relationships.quotes_invoices',
        'left_module' => 'quotes',
        'right_module' => 'invoices',
        'type' => 'one-to-one',
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
        'right_module' => 'products',
        'type' => 'many-to-many',
    ],

    [
        'name' => 'orders_invoices',
        'label' => 'relationships.orders_invoices',
        'left_module' => 'orders',
        'right_module' => 'invoices',
        'type' => 'one-to-one',
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
        'right_module' => 'leads',
        'type' => 'one-to-one',
    ],

    [
        'name' => 'contacts_invoices',
        'label' => 'relationships.contacts_invoices',
        'left_module' => 'contacts',
        'right_module' => 'invoices',
        'type' => 'one-to-many',
    ],

    [
        'name' => 'contacts_cases',
        'label' => 'relationships.contacts_cases',
        'left_module' => 'contacts',
        'right_module' => 'cases',
        'type' => 'one-to-many',
    ],

];
