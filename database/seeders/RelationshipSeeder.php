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

      // accounts ↔ contacts
      [
        'name' => 'accounts_contacts',
        'label' => 'relationships.accounts_contacts',
        'left_module' => 'accounts',
        'left_class'  => 'App\Models\Modules\Account',
        'right_class'  => 'App\Models\Modules\Contact',
        'right_module' => 'contacts',
        'relationship_type' => 'one-to-many',
      ],

      // accounts ↔ leads
      [
        'name' => 'accounts_leads',
        'label' => 'relationships.accounts_leads',
        'left_module' => 'accounts',
        'left_class'  => 'App\Models\Modules\Account',
        'right_class'  => 'App\Models\Modules\Lead',
        'right_module' => 'leads',
        'relationship_type' => 'one-to-many',
      ],

      // accounts ↔ quotes
      [
        'name' => 'accounts_quotes',
        'label' => 'relationships.accounts_quotes',
        'left_module' => 'accounts',
        'left_class'  => 'App\Models\Modules\Account',
        'right_class'  => 'App\Models\Modules\Quote',
        'right_module' => 'quotes',
        'relationship_type' => 'one-to-many',
      ],

      // accounts ↔ invoices
      [
        'name' => 'accounts_invoices',
        'label' => 'relationships.accounts_invoices',
        'left_module' => 'accounts',
        'left_class'  => 'App\Models\Modules\Account',
        'right_class'  => 'App\Models\Modules\Invoice',
        'right_module' => 'invoices',
        'relationship_type' => 'one-to-many',
      ],

      // accounts ↔ cases
      [
        'name' => 'accounts_cases',
        'label' => 'relationships.accounts_cases',
        'left_module' => 'accounts',
        'left_class'  => 'App\Models\Modules\Account',
        'right_class'  => 'App\Models\Modules\SupportCase',
        'right_module' => 'cases',
        'relationship_type' => 'one-to-many',
      ],

      // accounts ↔ emails
      [
        'name' => 'accounts_emails',
        'label' => 'relationships.accounts_emails',
        'left_module' => 'accounts',
        'left_class'  => 'App\Models\Modules\Account',
        'right_class'  => 'App\Models\Modules\Email',
        'right_module' => 'emails',
        'relationship_type' => 'one-to-many',
      ],

      // accounts ↔ inquiries
      [
        'name' => 'accounts_inquiries',
        'label' => 'relationships.accounts_inquiries',
        'left_module' => 'accounts',
        'right_module' => 'inquiries',
        'left_class'  => 'App\Models\Modules\Account',
        'right_class'  => 'App\Models\Modules\ContactMessage',
        'relationship_type' => 'one-to-many',
      ],

      // quotes ↔ invoices
      [
        'name' => 'quotes_invoices',
        'label' => 'relationships.quotes_invoices',
        'left_module' => 'quotes',
        'right_module' => 'invoices',
        'left_class'  => 'App\Models\Modules\Quote',
        'right_class'  => 'App\Models\Modules\Invoice',
        'relationship_type' => 'one-to-one',
      ],

      // leads ↔ emails
      [
        'name' => 'leads_emails',
        'label' => 'relationships.leads_emails',
        'left_module' => 'leads',
        'right_module' => 'emails',
        'left_class'  => 'App\Models\Modules\Lead',
        'right_class'  => 'App\Models\Modules\Email',
        'relationship_type' => 'one-to-many',
      ],

      // contacts ↔ emails
      [
        'name' => 'contacts_emails',
        'label' => 'relationships.contacts_emails',
        'left_module' => 'contacts',
        'right_module' => 'emails',
        'left_class'  => 'App\Models\Modules\Contact',
        'right_class'  => 'App\Models\Modules\Email',
        'relationship_type' => 'one-to-many',
      ],
      // contacts ↔ leads
      [
        'name' => 'contacts_leads',
        'label' => 'relationships.contacts_leads',
        'left_module' => 'contacts',
        'right_module' => 'leads',
        'left_class'  => 'App\Models\Modules\Contact',
        'right_class'  => 'App\Models\Modules\Lead',
        'relationship_type' => 'one-to-one',
      ],
      // contacts ↔ invoices
      [
        'name' => 'contacts_invoices',
        'label' => 'relationships.contacts_invoices',
        'left_module' => 'contacts',
        'right_module' => 'invoices',
        'left_class'  => 'App\Models\Modules\Contact',
        'right_class'  => 'App\Models\Modules\Invoice',
        'relationship_type' => 'one-to-many',
      ],
      // contacts ↔ cases
      [
        'name' => 'contacts_cases',
        'label' => 'relationships.contacts_cases',
        'left_module' => 'contacts',
        'right_module' => 'cases',
        'left_class'  => 'App\Models\Modules\Contact',
        'right_class'  => 'App\Models\Modules\SupportCase',
        'relationship_type' => 'one-to-many',
      ],

      // cases ↔ emails
      [
        'name' => 'cases_emails',
        'label' => 'relationships.cases_emails',
        'left_module' => 'cases',
        'right_module' => 'emails',
        'left_class'  => 'App\Models\Modules\SupportCase',
        'right_class'  => 'App\Models\Modules\Email',
        'relationship_type' => 'one-to-many',
      ],

      // inquiries ↔ emails
      [
        'name' => 'inquiries_emails',
        'label' => 'relationships.inquiries_emails',
        'left_module' => 'inquiries',
        'right_module' => 'emails',
        'left_class'  => 'App\Models\Modules\ContactMessage',
        'right_class'  => 'App\Models\Modules\Email',
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
