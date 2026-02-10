<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RelationshipSeeder extends Seeder
{
  public function run(): void
  {
    $now = now();

    $relationships = [

      // Accounts ↔ Contacts
      [
        'name' => 'accounts_contacts',
        'lhs_module' => 'Accounts',
        'rhs_module' => 'Contacts',
        'relationship_type' => 'one-to-many',
      ],

      // Accounts ↔ Leads
      [
        'name' => 'accounts_leads',
        'lhs_module' => 'Accounts',
        'rhs_module' => 'Leads',
        'relationship_type' => 'one-to-many',
      ],

      // Accounts ↔ Quotes
      [
        'name' => 'accounts_quotes',
        'lhs_module' => 'Accounts',
        'rhs_module' => 'Quotes',
        'relationship_type' => 'one-to-many',
      ],

      // Accounts ↔ Invoices
      [
        'name' => 'accounts_invoices',
        'lhs_module' => 'Accounts',
        'rhs_module' => 'Invoices',
        'relationship_type' => 'one-to-many',
      ],

      // Accounts ↔ Cases
      [
        'name' => 'accounts_cases',
        'lhs_module' => 'Accounts',
        'rhs_module' => 'Cases',
        'relationship_type' => 'one-to-many',
      ],

      // Accounts ↔ Emails
      [
        'name' => 'accounts_emails',
        'lhs_module' => 'Accounts',
        'rhs_module' => 'Emails',
        'relationship_type' => 'one-to-many',
      ],

      // Accounts ↔ Inquiries
      [
        'name' => 'accounts_inquiries',
        'lhs_module' => 'Accounts',
        'rhs_module' => 'Inquiries',
        'relationship_type' => 'one-to-many',
      ],

      // Quotes ↔ Invoices (conversion)
      [
        'name' => 'quotes_invoices',
        'lhs_module' => 'Quotes',
        'rhs_module' => 'Invoices',
        'relationship_type' => 'one-to-one',
      ],

      // Leads ↔ Emails
      [
        'name' => 'leads_emails',
        'lhs_module' => 'Leads',
        'rhs_module' => 'Emails',
        'relationship_type' => 'one-to-many',
      ],

      // Contacts ↔ Emails
      [
        'name' => 'contacts_emails',
        'lhs_module' => 'Contacts',
        'rhs_module' => 'Emails',
        'relationship_type' => 'one-to-many',
      ],

      // Cases ↔ Emails
      [
        'name' => 'cases_emails',
        'lhs_module' => 'Cases',
        'rhs_module' => 'Emails',
        'relationship_type' => 'one-to-many',
      ],

      // Inquiries ↔ Emails
      [
        'name' => 'inquiries_emails',
        'lhs_module' => 'Inquiries',
        'rhs_module' => 'Emails',
        'relationship_type' => 'one-to-many',
      ],
    ];

    foreach ($relationships as $relationship) {
      DB::table('relationships')->updateOrInsert(
        ['name' => $relationship['name']],
        array_merge($relationship, [
          'id' => uuid_create(UUID_TYPE_RANDOM),
          'created_at' => $now,
          'updated_at' => $now,
        ])
      );
    }
  }
}
