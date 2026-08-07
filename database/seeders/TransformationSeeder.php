<?php

namespace Database\Seeders;

use App\Models\Transformation;
use Illuminate\Database\Seeder;

/**
 * Seeds a handful of Transformations that model common CRM record
 * conversions (Quote -> Order, Lead -> Contact, Call -> Note, etc.) as
 * the engine's reference implementations. Kept as ordinary rows (not
 * migration-baked) so every one of them stays fully editable via the
 * Studio Transformations builder afterward, same as any transformation
 * an admin creates by hand.
 *
 * Idempotent against migrate:fresh --seed: each transformation is
 * looked up by its stable key (source/target module pair) and
 * replaced wholesale rather than duplicated on re-seed.
 */
class TransformationSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedQuoteToOrder();
        $this->seedQuoteToInvoice();
        $this->seedDealToQuote();
        $this->seedLeadToAccount();
        $this->seedLeadToDeal();
        $this->seedLeadToContact();
        $this->seedCaseToTask();
        $this->seedCallToNote();
        $this->seedMeetingToNote();
        $this->seedEmailToContact();
    }

    protected function seedQuoteToOrder(): void
    {
        $this->upsert(
            [
                'source_module' => 'quotes',
                'target_module' => 'orders',
                'name' => 'Order',
                'description' => 'Create a Sales Order once a Quote is accepted.',
                'enabled' => true,
                'automation_enabled' => false,
                'conditions' => [
                    ['field' => 'status', 'operator' => 'equals', 'value' => 'accepted'],
                ],
                'conditions_match' => 'all',
                'link_records_enabled' => false,
            ],
            [
                ['mode' => 'field', 'source_field' => 'name', 'target_field' => 'name'],
                ['mode' => 'field', 'source_field' => 'description', 'target_field' => 'description'],
                ['mode' => 'field', 'source_field' => 'owner_id', 'target_field' => 'owner_id'],
                // orders.order_number is required and free-text, quotes.number is
                // numeric, so build a readable order number from the quote number
                ['mode' => 'expression', 'target_field' => 'order_number', 'expression' => [
                    ['type' => 'text', 'value' => 'ORD-'],
                    ['type' => 'field', 'value' => 'number'],
                ]],
                // An order created from an already-accepted quote starts confirmed,
                // not as a fresh draft
                ['mode' => 'static', 'target_field' => 'status', 'value' => 'confirmed'],
            ],
            // The priced line items and any discussion notes carry straight over
            ['line_items', 'notes'],
        );
    }

    protected function seedQuoteToInvoice(): void
    {
        $this->upsert(
            [
                'source_module' => 'quotes',
                'target_module' => 'invoices',
                'name' => 'Invoice',
                'description' => 'Create an Invoice from an accepted Quote.',
                'enabled' => true,
                'automation_enabled' => false,
                'conditions' => [
                    ['field' => 'status', 'operator' => 'equals', 'value' => 'accepted'],
                    ['field' => 'total', 'operator' => 'greater_than', 'value' => 0],
                ],
                // Explicit even though 'all' is also the column default,
                // this reference implementation is meant to demonstrate
                // every configurable option. With match "all" (AND), each
                // field can only be used once (see validateRequest()'s
                // duplicate-condition-field check), 'status' and 'total'
                // are distinct fields so this is compliant.
                'conditions_match' => 'all',
                'link_records_enabled' => false,
            ],
            [
                ['mode' => 'field', 'source_field' => 'name', 'target_field' => 'name'],
                ['mode' => 'field', 'source_field' => 'description', 'target_field' => 'description'],
                ['mode' => 'field', 'source_field' => 'notes', 'target_field' => 'notes'],
                ['mode' => 'field', 'source_field' => 'owner_id', 'target_field' => 'owner_id'],
                // Target fields with no direct source counterpart are
                // just mapping rows in static/expression mode, there is
                // no separate "defaults" list.
                ['mode' => 'static', 'target_field' => 'status', 'value' => 'draft'],
                ['mode' => 'expression', 'target_field' => 'issue_date', 'expression' => [
                    ['type' => 'helper', 'value' => 'today'],
                ]],
                // Both are plain dates, so honour the quote's own validity
                // window as the payment due date unless the invoice is edited
                ['mode' => 'field', 'source_field' => 'valid_until', 'target_field' => 'due_date'],
            ],
            ['line_items', 'notes'],
        );
    }

    protected function seedDealToQuote(): void
    {
        $this->upsert(
            [
                'source_module' => 'deals',
                'target_module' => 'quotes',
                'name' => 'Quote',
                'description' => 'Draft a Quote once a Deal reaches the proposal stage.',
                'enabled' => true,
                'automation_enabled' => false,
                // Pricing is normally sent out once a deal moves to "proposal"
                'conditions' => [
                    ['field' => 'sales_stage', 'operator' => 'equals', 'value' => 'proposal'],
                ],
                'conditions_match' => 'all',
                'link_records_enabled' => false,
            ],
            [
                ['mode' => 'field', 'source_field' => 'name', 'target_field' => 'name'],
                ['mode' => 'field', 'source_field' => 'description', 'target_field' => 'description'],
                ['mode' => 'field', 'source_field' => 'owner_id', 'target_field' => 'owner_id'],
                // quotes.status is required, every new quote starts as a draft
                ['mode' => 'static', 'target_field' => 'status', 'value' => 'draft'],
                // Both are plain dates, align the quote's validity with when
                // the deal is expected to close
                ['mode' => 'field', 'source_field' => 'expected_close_date', 'target_field' => 'valid_until'],
                ['mode' => 'expression', 'target_field' => 'notes', 'expression' => [
                    ['type' => 'text', 'value' => 'Quote generated from a deal (stage: '],
                    ['type' => 'field', 'value' => 'sales_stage'],
                    ['type' => 'text', 'value' => ', type: '],
                    ['type' => 'field', 'value' => 'type'],
                    ['type' => 'text', 'value' => ').'],
                ]],
            ],
            // The products already tied to the deal, and any notes discussed so far
            ['products', 'notes'],
        );
    }

    protected function seedLeadToAccount(): void
    {
        $this->upsert(
            [
                'source_module' => 'leads',
                'target_module' => 'accounts',
                'name' => 'Account',
                'description' => 'Turn a Lead into an Account once it is ready to become a customer record.',
                'enabled' => true,
                // Leads carry no status field in this build, so there is no
                // natural auto-trigger condition, conversion stays manual
                'automation_enabled' => false,
                'conditions' => [],
                'conditions_match' => 'all',
                'link_records_enabled' => false,
            ],
            [
                ['mode' => 'field', 'source_field' => 'name', 'target_field' => 'name'],
                ['mode' => 'field', 'source_field' => 'description', 'target_field' => 'description'],
                ['mode' => 'field', 'source_field' => 'owner_id', 'target_field' => 'owner_id'],
                ['mode' => 'field', 'source_field' => 'email', 'target_field' => 'email'],
                ['mode' => 'field', 'source_field' => 'phone', 'target_field' => 'phone'],
                ['mode' => 'field', 'source_field' => 'address', 'target_field' => 'billing_address'],
            ],
            // A contact already linked to the lead follows it onto the account
            ['contacts', 'notes'],
        );
    }

    protected function seedLeadToDeal(): void
    {
        $this->upsert(
            [
                'source_module' => 'leads',
                'target_module' => 'deals',
                'name' => 'Deal',
                'description' => 'Open a Deal from a promising Lead.',
                'enabled' => true,
                'automation_enabled' => false,
                'conditions' => [],
                'conditions_match' => 'all',
                'link_records_enabled' => false,
            ],
            [
                ['mode' => 'field', 'source_field' => 'name', 'target_field' => 'name'],
                ['mode' => 'field', 'source_field' => 'description', 'target_field' => 'description'],
                ['mode' => 'field', 'source_field' => 'owner_id', 'target_field' => 'owner_id'],
                // A deal opened straight from a lead always starts at the top
                // of the pipeline
                ['mode' => 'static', 'target_field' => 'sales_stage', 'value' => 'prospecting'],
                ['mode' => 'static', 'target_field' => 'type', 'value' => 'new_business'],
                ['mode' => 'static', 'target_field' => 'probability', 'value' => '10'],
            ],
            ['contacts', 'notes'],
        );
    }

    protected function seedLeadToContact(): void
    {
        $this->upsert(
            [
                'source_module' => 'leads',
                'target_module' => 'contacts',
                'name' => 'Contact',
                'description' => 'Convert a qualified Lead into a Contact.',
                'enabled' => true,
                'automation_enabled' => false,
                'conditions' => [],
                'conditions_match' => 'all',
                // Reuses the existing one-to-one leads<->contacts relationship
                // rather than creating a second, redundant one
                'link_records_enabled' => false,
            ],
            [
                ['mode' => 'field', 'source_field' => 'name', 'target_field' => 'name'],
                ['mode' => 'field', 'source_field' => 'first_name', 'target_field' => 'first_name'],
                ['mode' => 'field', 'source_field' => 'last_name', 'target_field' => 'last_name'],
                ['mode' => 'field', 'source_field' => 'email', 'target_field' => 'email'],
                ['mode' => 'field', 'source_field' => 'phone', 'target_field' => 'phone'],
                ['mode' => 'field', 'source_field' => 'description', 'target_field' => 'description'],
                ['mode' => 'field', 'source_field' => 'owner_id', 'target_field' => 'owner_id'],
                ['mode' => 'expression', 'target_field' => 'notes', 'expression' => [
                    ['type' => 'text', 'value' => 'Converted from a lead.'],
                ]],
            ],
            ['notes'],
        );
    }

    protected function seedCaseToTask(): void
    {
        $this->upsert(
            [
                'source_module' => 'cases',
                'target_module' => 'tasks',
                'name' => 'Follow-up Task',
                'description' => 'Create a wrap-up Task once a Case is closed.',
                'enabled' => true,
                'automation_enabled' => false,
                'conditions' => [
                    ['field' => 'status', 'operator' => 'equals', 'value' => 'closed'],
                ],
                'conditions_match' => 'all',
                // Reuses the existing cases<->tasks activity relationship
                'link_records_enabled' => false,
            ],
            [
                ['mode' => 'expression', 'target_field' => 'name', 'expression' => [
                    ['type' => 'text', 'value' => 'Follow up: '],
                    ['type' => 'field', 'value' => 'subject'],
                ]],
                ['mode' => 'field', 'source_field' => 'description', 'target_field' => 'description'],
                ['mode' => 'field', 'source_field' => 'owner_id', 'target_field' => 'owner_id'],
                ['mode' => 'field', 'source_field' => 'priority', 'target_field' => 'priority'],
                ['mode' => 'static', 'target_field' => 'status', 'value' => 'not_started'],
                // tasks.due_at is required and both fields are plain datetimes,
                // so the wrap-up work is due by the time the case is meant to close
                ['mode' => 'field', 'source_field' => 'closed_at', 'target_field' => 'due_at'],
            ],
            [],
        );
    }

    protected function seedCallToNote(): void
    {
        $this->upsert(
            [
                'source_module' => 'calls',
                'target_module' => 'notes',
                'name' => 'Call Note',
                'description' => 'Log a Note summarizing a completed Call.',
                'enabled' => true,
                'automation_enabled' => false,
                // Only calls that actually took place are worth summarizing
                'conditions' => [
                    ['field' => 'status', 'operator' => 'equals', 'value' => 'held'],
                ],
                'conditions_match' => 'all',
                'link_records_enabled' => false,
            ],
            [
                ['mode' => 'field', 'source_field' => 'name', 'target_field' => 'name'],
                ['mode' => 'field', 'source_field' => 'owner_id', 'target_field' => 'owner_id'],
                ['mode' => 'expression', 'target_field' => 'description', 'expression' => [
                    ['type' => 'text', 'value' => 'Call ('],
                    ['type' => 'field', 'value' => 'direction'],
                    ['type' => 'text', 'value' => ') — outcome: '],
                    ['type' => 'field', 'value' => 'outcome'],
                    ['type' => 'text', 'value' => ', duration: '],
                    ['type' => 'field', 'value' => 'duration_minutes'],
                    ['type' => 'text', 'value' => ' min.'],
                ]],
            ],
            [],
        );
    }

    protected function seedMeetingToNote(): void
    {
        $this->upsert(
            [
                'source_module' => 'meetings',
                'target_module' => 'notes',
                'name' => 'Meeting Note',
                'description' => 'Log a Note summarizing a completed Meeting.',
                'enabled' => true,
                'automation_enabled' => false,
                'conditions' => [
                    ['field' => 'status', 'operator' => 'equals', 'value' => 'held'],
                ],
                'conditions_match' => 'all',
                'link_records_enabled' => false,
            ],
            [
                ['mode' => 'field', 'source_field' => 'name', 'target_field' => 'name'],
                ['mode' => 'field', 'source_field' => 'owner_id', 'target_field' => 'owner_id'],
                ['mode' => 'expression', 'target_field' => 'description', 'expression' => [
                    ['type' => 'text', 'value' => 'Meeting from '],
                    ['type' => 'field', 'value' => 'start_at'],
                    ['type' => 'text', 'value' => ' to '],
                    ['type' => 'field', 'value' => 'end_at'],
                    ['type' => 'text', 'value' => ' — status: '],
                    ['type' => 'field', 'value' => 'status'],
                ]],
            ],
            [],
        );
    }

    protected function seedEmailToContact(): void
    {
        $this->upsert(
            [
                'source_module' => 'emails',
                'target_module' => 'contacts',
                'name' => 'Contact',
                'description' => 'Create a Contact from a captured Email whose sender isn\'t in the CRM yet.',
                'enabled' => true,
                // Transformation::evaluateSingleCondition() only ever inspects
                // the source record's own field values — it has no operator
                // for "this record has no linked Contact yet", so an
                // unmatched-sender trigger can't be expressed as an
                // automation condition. Same situation as Lead -> Account /
                // Lead -> Deal below: stays a manual "Transform" action from
                // the Email record until the engine grows relationship-aware
                // conditions.
                'automation_enabled' => false,
                'conditions' => [],
                'conditions_match' => 'all',
                // Reuses the existing many-to-many contacts<->emails
                // activity relationship rather than creating a second one.
                'link_records_enabled' => false,
            ],
            [
                ['mode' => 'field', 'source_field' => 'from_name', 'target_field' => 'name'],
                ['mode' => 'field', 'source_field' => 'from_address', 'target_field' => 'email'],
                ['mode' => 'field', 'source_field' => 'owner_id', 'target_field' => 'owner_id'],
                ['mode' => 'expression', 'target_field' => 'notes', 'expression' => [
                    ['type' => 'text', 'value' => 'Created from a captured email — subject: '],
                    ['type' => 'field', 'value' => 'name'],
                ]],
            ],
            [],
        );
    }

    /**
     * Creates/replaces a transformation and its steps in one go. Model
     * events are suppressed during seeding (DatabaseSeeder uses
     * WithoutModelEvents), so the relationship auto-provisioning that
     * normally runs on Transformation::saved() must be called explicitly.
     */
    protected function upsert(array $attributes, array $mappings, array $relationships): void
    {
        $transformation = Transformation::updateOrCreate(
            [
                'source_module' => $attributes['source_module'],
                'target_module' => $attributes['target_module'],
            ],
            $attributes
        );

        $transformation->ensureRelationship();

        $transformation->steps()->delete();

        $steps = [
            ['order' => 0, 'type' => 'create_record', 'configuration' => []],
            ['order' => 1, 'type' => 'copy_fields', 'configuration' => ['mappings' => $mappings]],
            ['order' => 2, 'type' => 'copy_relationships', 'configuration' => ['relationships' => $relationships]],
        ];

        if ($attributes['link_records_enabled'] ?? true) {
            $steps[] = ['order' => 3, 'type' => 'link_records', 'configuration' => []];
        }

        $transformation->steps()->createMany($steps);
    }
}
