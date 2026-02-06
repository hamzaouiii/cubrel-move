<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\DropDownList;
use Illuminate\Support\Str;
use App\Models\Module;

class dropdownListSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {

    // Cases status (existing)
    DropdownList::create(
      [
        'id' => Str::uuid(),
        'key' => 'cases_status_list',
        'is_global' => false,
        'values' => [
          ['value' => 'open', 'label' => 'dropdowns.cases_status_list.open'],
          ['value' => 'in_progress', 'label' => 'dropdowns.cases_status_list.in_progress'],
          ['value' => 'pending_input', 'label' => 'dropdowns.cases_status_list.pending_input'],
          ['value' => 'rejected', 'label' => 'dropdowns.cases_status_list.rejected'],
          ['value' => 'closed', 'label' => 'dropdowns.cases_status_list.closed'],
        ],
      ]
    );

    // Cases priority (existing)
    DropdownList::create(
      [
        'id' => Str::uuid(),
        'key' => 'cases_priority_list',
        'is_global' => false,
        'values' => [
          ['value' => 'low', 'label' => 'dropdowns.cases_priority_list.low'],
          ['value' => 'medium', 'label' => 'dropdowns.cases_priority_list.medium'],
          ['value' => 'high', 'label' => 'dropdowns.cases_priority_list.high'],
          ['value' => 'urgent', 'label' => 'dropdowns.cases_priority_list.urgent'],
        ],
      ]
    );

    // Emails status
    DropdownList::create(
      [
        'id' => Str::uuid(),
        'key' => 'emails_status_list',
        'is_global' => false,
        'values' => [
          ['value' => 'draft', 'label' => 'dropdowns.emails_status_list.draft'],
          ['value' => 'scheduled', 'label' => 'dropdowns.emails_status_list.scheduled'],
          ['value' => 'sent', 'label' => 'dropdowns.emails_status_list.sent'],
          ['value' => 'delivered', 'label' => 'dropdowns.emails_status_list.delivered'],
          ['value' => 'read', 'label' => 'dropdowns.emails_status_list.read'],
          ['value' => 'failed', 'label' => 'dropdowns.emails_status_list.failed'],
        ],
      ]
    );

    // Quotes status
    DropdownList::create(
      [
        'id' => Str::uuid(),
        'key' => 'quotes_status_list',
        'is_global' => false,
        'values' => [
          ['value' => 'draft', 'label' => 'dropdowns.quotes_status_list.draft'],
          ['value' => 'sent', 'label' => 'dropdowns.quotes_status_list.sent'],
          ['value' => 'viewed', 'label' => 'dropdowns.quotes_status_list.viewed'],
          ['value' => 'accepted', 'label' => 'dropdowns.quotes_status_list.accepted'],
          ['value' => 'rejected', 'label' => 'dropdowns.quotes_status_list.rejected'],
          ['value' => 'expired', 'label' => 'dropdowns.quotes_status_list.expired'],
        ],
      ]
    );

    // Inquiries status
    DropdownList::create(
      [
        'id' => Str::uuid(),
        'key' => 'inquiries_status_list',
        'is_global' => false,
        'values' => [
          ['value' => 'new', 'label' => 'dropdowns.inquiries_status_list.new'],
          ['value' => 'acknowledged', 'label' => 'dropdowns.inquiries_status_list.acknowledged'],
          ['value' => 'in_progress', 'label' => 'dropdowns.inquiries_status_list.in_progress'],
          ['value' => 'waiting_response', 'label' => 'dropdowns.inquiries_status_list.waiting_response'],
          ['value' => 'resolved', 'label' => 'dropdowns.inquiries_status_list.resolved'],
          ['value' => 'closed', 'label' => 'dropdowns.inquiries_status_list.closed'],
        ],
      ]
    );

    // Invoices status
    DropdownList::create(
      [
        'id' => Str::uuid(),
        'key' => 'invoices_status_list',
        'is_global' => false,
        'values' => [
          ['value' => 'draft', 'label' => 'dropdowns.invoices_status_list.draft'],
          ['value' => 'sent', 'label' => 'dropdowns.invoices_status_list.sent'],
          ['value' => 'viewed', 'label' => 'dropdowns.invoices_status_list.viewed'],
          ['value' => 'partial', 'label' => 'dropdowns.invoices_status_list.partial'],
          ['value' => 'paid', 'label' => 'dropdowns.invoices_status_list.paid'],
          ['value' => 'overdue', 'label' => 'dropdowns.invoices_status_list.overdue'],
          ['value' => 'void', 'label' => 'dropdowns.invoices_status_list.void'],
        ],
      ]
    );
  }
}
