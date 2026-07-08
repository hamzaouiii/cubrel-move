<?php

namespace Tests\Feature;

use App\Models\DropdownList;
use App\Models\Modules\Contact;
use App\Models\Modules\Deal;
use App\Models\Modules\Invoice;
use App\Models\Modules\Lead;
use App\Models\Relationship;
use App\Models\RelationshipLink;
use App\Models\User;
use App\Services\Aggregation\AggregationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\Concerns\InteractsWithDashboardFixtures;
use Tests\TestCase;

class AggregationServiceTest extends TestCase
{
    use InteractsWithDashboardFixtures;
    use RefreshDatabase;

    protected function makeLeadsModule()
    {
        return $this->makeModule(['slug' => 'leads', 'model_class' => Lead::class]);
    }

    protected function makeDealsModule()
    {
        return $this->makeModule([
            'slug' => 'deals', 'name' => 'Deals', 'path' => '/deals', 'model_class' => Deal::class,
        ]);
    }

    protected function makeUsersModule(array $overrides = [])
    {
        return $this->makeModule(array_merge([
            'slug' => 'users', 'name' => 'Users', 'path' => '/users',
            'has_owner' => false, 'model_class' => User::class,
        ], $overrides));
    }

    protected function makeContactsModule(array $overrides = [])
    {
        return $this->makeModule(array_merge([
            'slug' => 'contacts', 'name' => 'Contacts', 'path' => '/contacts',
            'has_owner' => false, 'model_class' => Contact::class,
        ], $overrides));
    }

    protected function makeInvoicesModule(array $overrides = [])
    {
        return $this->makeModule(array_merge([
            'slug' => 'invoices', 'name' => 'Invoices', 'path' => '/invoices',
            'has_owner' => false, 'model_class' => Invoice::class,
        ], $overrides));
    }

    // ── metric() ─────────────────────────────────────────────────────────────

    public function test_metric_count(): void
    {
        $module = $this->makeLeadsModule();
        $owner = $this->makeUser();
        Lead::factory()->count(3)->create(['owner_id' => $owner->id]);

        $result = AggregationService::metric($module, ['aggregate' => 'count']);

        $this->assertSame(3, $result['value']);
    }

    public function test_metric_sum(): void
    {
        $module = $this->makeDealsModule();
        $this->makeField($module, ['name' => 'amount', 'type' => 'currency']);
        $owner = $this->makeUser();

        Deal::factory()->create(['owner_id' => $owner->id, 'amount' => 100]);
        Deal::factory()->create(['owner_id' => $owner->id, 'amount' => 250]);

        $result = AggregationService::metric($module, ['aggregate' => 'sum', 'field' => 'amount']);

        $this->assertSame(350.0, $result['value']);
    }

    public function test_metric_rejects_invalid_aggregate(): void
    {
        $module = $this->makeLeadsModule();

        $this->expectException(HttpException::class);
        AggregationService::metric($module, ['aggregate' => 'median']);
    }

    public function test_metric_sum_requires_field(): void
    {
        $module = $this->makeDealsModule();

        $this->expectException(HttpException::class);
        AggregationService::metric($module, ['aggregate' => 'sum']);
    }

    public function test_metric_sum_rejects_non_numeric_field(): void
    {
        $module = $this->makeDealsModule();
        $this->makeField($module, ['name' => 'sales_stage', 'type' => 'select']);

        $this->expectException(HttpException::class);
        AggregationService::metric($module, ['aggregate' => 'sum', 'field' => 'sales_stage']);
    }

    // breakdown groups correctly and orders by count 
    public function test_breakdown_groups_and_orders_by_count_desc(): void
    {
        $module = $this->makeDealsModule();
        $dropdownList = DropdownList::create([
          'key' => 'test_dropdown',
            'values' => [
                ['value' => 'closed_won',  'label' => 'Closed Won'],
                ['value' => 'closed_lost', 'label' => 'Closed Lost'],
            ],
        ]);

        $this->makeField($module, ['name' => 'sales_stage', 'type' => 'select','dropdown_list_id' => $dropdownList->id]);

        $owner = $this->makeUser();

        Deal::factory()->count(2)->create(['owner_id' => $owner->id, 'sales_stage' => 'closed_won']);
        Deal::factory()->count(1)->create(['owner_id' => $owner->id, 'sales_stage' => 'closed_lost']);

        $result = AggregationService::breakdown($module, [
            'groupBy' => 'sales_stage',
            'metric' => ['type' => 'count'],
            'chartType' => 'donut',
        ]);

        $this->assertSame(['Closed Won', 'Closed Lost'], $result['labels']);
        $this->assertSame([2.0, 1.0], $result['series'][0]['data']);
    }
//
    public function test_breakdown_respects_limit(): void
    {
        $module = $this->makeDealsModule();
                $dropdownList = DropdownList::create([
          'key' => 'test_dropdown',
            'values' => [
                ['value' => 'closed_won',  'label' => 'Closed Won'],
                ['value' => 'closed_lost', 'label' => 'Closed Lost'],
                ['value' => 'proposal', 'label' => 'proposal'],
            ],
        ]);
                $this->makeField($module, ['name' => 'sales_stage', 'type' => 'select','dropdown_list_id' => $dropdownList->id]);

        $owner = $this->makeUser();

        Deal::factory()->create(['owner_id' => $owner->id, 'sales_stage' => 'closed_won']);
        Deal::factory()->create(['owner_id' => $owner->id, 'sales_stage' => 'closed_lost']);
        Deal::factory()->create(['owner_id' => $owner->id, 'sales_stage' => 'proposal']);

        $result = AggregationService::breakdown($module, [
            'groupBy' => 'sales_stage',
            'metric' => ['type' => 'count'],
            'chartType' => 'donut',
            'limit' => 2,
        ]);

        $this->assertCount(2, $result['labels']);
    }

    public function test_breakdown_rejects_invalid_chart_type(): void
    {
        $module = $this->makeLeadsModule();
        $this->makeField($module, ['name' => 'company', 'type' => 'text']);

        $this->expectException(HttpException::class);
        AggregationService::breakdown($module, [
            'groupBy' => 'company',
            'chartType' => 'pyramid',
        ]);
    }

    public function test_breakdown_rejects_missing_group_by(): void
    {
        $module = $this->makeLeadsModule();

        $this->expectException(HttpException::class);
        AggregationService::breakdown($module, ['chartType' => 'donut']);
    }

    // ── timeSeries() ─────────────────────────────────────────────────────────

    public function test_time_series_buckets_by_month_and_fills_gaps_with_zero(): void
    {
        Carbon::setTestNow('2024-06-15 12:00:00');

        $module = $this->makeLeadsModule();
        $this->makeField($module, ['name' => 'created_at', 'type' => 'datetime']);
        $owner = $this->makeUser();

        Lead::factory()->count(2)->create(['owner_id' => $owner->id, 'created_at' => '2024-03-10']);
        Lead::factory()->count(1)->create(['owner_id' => $owner->id, 'created_at' => '2024-06-01']);

        $result = AggregationService::timeSeries($module, [
            'dateField' => 'created_at',
            'metric' => ['type' => 'count'],
            'interval' => 'month',
            'dateRange' => 'last_6_months',
            'chartType' => 'bar',
        ]);

        $this->assertSame(
            ['2024-01', '2024-02', '2024-03', '2024-04', '2024-05', '2024-06'],
            $result['labels']
        );
        $this->assertSame([0.0, 0.0, 2.0, 0.0, 0.0, 1.0], $result['series'][0]['data']);

        Carbon::setTestNow();
    }

    public function test_time_series_rejects_invalid_interval(): void
    {
        $module = $this->makeLeadsModule();
        $this->makeField($module, ['name' => 'created_at', 'type' => 'datetime']);

        $this->expectException(HttpException::class);
        AggregationService::timeSeries($module, [
            'dateField' => 'created_at',
            'interval' => 'fortnight',
        ]);
    }

    public function test_time_series_rejects_non_date_field(): void
    {
        $module = $this->makeLeadsModule();
        $this->makeField($module, ['name' => 'company', 'type' => 'text']);

        $this->expectException(HttpException::class);
        AggregationService::timeSeries($module, ['dateField' => 'company']);
    }

    // ── recordList() ─────────────────────────────────────────────────────────

    public function test_record_list_returns_rows_and_module_meta(): void
    {
        $module = $this->makeLeadsModule();
        $owner = $this->makeUser();
        Lead::factory()->count(3)->create(['owner_id' => $owner->id]);

        $result = AggregationService::recordList($module, ['limit' => 2]);

        $this->assertCount(2, $result['rows']);
        $this->assertSame('leads', $result['moduleSlug']);
    }

    // ── people() — via record-type field (e.g. owner_id) ────────────────────────
    //
    // None of these tests call actingAs(), so Auth::check() is false the whole
    // time — meaning AdminOnlyModuleScope would normally hide the 'users'
    // module from every query here. Every test below that resolves 'users' as
    // the people module is therefore also a regression check for the
    // Module::withoutGlobalScope(AdminOnlyModuleScope::class) fix in
    // resolvePeopleModule() — without it, these would all fail with
    // "Related people module not found or inactive."

    public function test_people_via_field_ranks_by_count_desc(): void
    {
        $leads = $this->makeLeadsModule();
        $this->makeField($leads, ['name' => 'owner_id', 'type' => 'record', 'related_module' => 'users']);
        $this->makeUsersModule();

        $topOwner = $this->makeUser();
        $otherOwner = $this->makeUser();

        Lead::factory()->count(3)->create(['owner_id' => $topOwner->id]);
        Lead::factory()->count(1)->create(['owner_id' => $otherOwner->id]);

        $result = AggregationService::people($leads, [
            'relationField' => 'owner_id',
            'aggregate' => 'count',
        ]);

        $this->assertSame('users', $result['peopleModuleSlug']);
        $this->assertSame('count', $result['aggregate']);
        $this->assertCount(2, $result['rows']);
        $this->assertSame((string) $topOwner->id, $result['rows'][0]['id']);
        $this->assertSame(3.0, $result['rows'][0]['value']);
        $this->assertSame((string) $otherOwner->id, $result['rows'][1]['id']);
        $this->assertSame(1.0, $result['rows'][1]['value']);
    }

    public function test_people_via_field_sum_aggregate(): void
    {
        $deals = $this->makeDealsModule();
        $this->makeField($deals, ['name' => 'owner_id', 'type' => 'record', 'related_module' => 'users']);
        $this->makeField($deals, ['name' => 'amount', 'type' => 'currency']);
        $this->makeUsersModule();

        $owner = $this->makeUser();
        Deal::factory()->create(['owner_id' => $owner->id, 'amount' => 100]);
        Deal::factory()->create(['owner_id' => $owner->id, 'amount' => 250]);

        $result = AggregationService::people($deals, [
            'relationField' => 'owner_id',
            'aggregate' => 'sum',
            'field' => 'amount',
        ]);

        $this->assertCount(1, $result['rows']);
        $this->assertSame((string) $owner->id, $result['rows'][0]['id']);
        $this->assertSame(350.0, $result['rows'][0]['value']);
    }

    public function test_people_via_field_resolves_avatar_when_present(): void
    {
        $leads = $this->makeLeadsModule();
        $this->makeField($leads, ['name' => 'owner_id', 'type' => 'record', 'related_module' => 'users']);
        $usersModule = $this->makeUsersModule();
        $this->makeField($usersModule, ['name' => 'avatar', 'type' => 'image']);

        $withAvatar = $this->makeUser(['avatar' => '/storage/uploads/images/ada.jpg']);
        $withoutAvatar = $this->makeUser();

        Lead::factory()->create(['owner_id' => $withAvatar->id]);
        Lead::factory()->create(['owner_id' => $withoutAvatar->id]);

        $result = AggregationService::people($leads, [
            'relationField' => 'owner_id',
            'aggregate' => 'count',
        ]);

        $rowsById = collect($result['rows'])->keyBy('id');
        $this->assertSame('/storage/uploads/images/ada.jpg', $rowsById[(string) $withAvatar->id]['avatar']);
        $this->assertNull($rowsById[(string) $withoutAvatar->id]['avatar']);
    }

    public function test_people_via_field_respects_limit(): void
    {
        $leads = $this->makeLeadsModule();
        $this->makeField($leads, ['name' => 'owner_id', 'type' => 'record', 'related_module' => 'users']);
        $this->makeUsersModule();

        foreach (range(1, 3) as $i) {
            Lead::factory()->create(['owner_id' => $this->makeUser()->id]);
        }

        $result = AggregationService::people($leads, [
            'relationField' => 'owner_id',
            'aggregate' => 'count',
            'limit' => 2,
        ]);

        $this->assertCount(2, $result['rows']);
    }

    public function test_people_rejects_missing_relation_field(): void
    {
        $leads = $this->makeLeadsModule();

        $this->expectException(HttpException::class);
        AggregationService::people($leads, ['aggregate' => 'count']);
    }

    public function test_people_rejects_non_record_relation_field(): void
    {
        $leads = $this->makeLeadsModule();
        $this->makeField($leads, ['name' => 'company', 'type' => 'text']);

        $this->expectException(HttpException::class);
        AggregationService::people($leads, ['relationField' => 'company', 'aggregate' => 'count']);
    }

    public function test_people_rejects_relation_field_pointing_to_missing_module(): void
    {
        $leads = $this->makeLeadsModule();
        $this->makeField($leads, ['name' => 'owner_id', 'type' => 'record', 'related_module' => 'ghost_module']);

        $this->expectException(HttpException::class);
        AggregationService::people($leads, ['relationField' => 'owner_id', 'aggregate' => 'count']);
    }

    public function test_people_via_field_rejects_invalid_aggregate(): void
    {
        $leads = $this->makeLeadsModule();
        $this->makeField($leads, ['name' => 'owner_id', 'type' => 'record', 'related_module' => 'users']);
        $this->makeUsersModule();

        $this->expectException(HttpException::class);
        AggregationService::people($leads, ['relationField' => 'owner_id', 'aggregate' => 'median']);
    }

    // ── people() — via named Relationship (relationships/relationship_links) ───

    public function test_people_via_relationship_ranks_by_count_desc(): void
    {
        $invoices = $this->makeInvoicesModule();
        $this->makeContactsModule();

        $relationship = Relationship::create([
            'name' => 'contacts_invoices',
            'label' => 'relationships.contacts_invoices',
            'left_module' => 'contacts',
            'right_module' => 'invoices',
            'type' => 'one-to-many',
        ]);

        // BaseModule::booted() auto-fills owner_id on create when none is given,
        // falling back to the first user in the DB — there must be one already.
        $this->makeUser();

        $topContact = Contact::factory()->create();
        $otherContact = Contact::factory()->create();

        $topInvoices = Invoice::factory()->count(2)->create();
        $otherInvoice = Invoice::factory()->create();

        foreach ($topInvoices as $invoice) {
            RelationshipLink::create([
                'relationship_id' => $relationship->id,
                'left_id' => $topContact->id,
                'right_id' => $invoice->id,
            ]);
        }

        RelationshipLink::create([
            'relationship_id' => $relationship->id,
            'left_id' => $otherContact->id,
            'right_id' => $otherInvoice->id,
        ]);

        $result = AggregationService::people($invoices, [
            'relationshipName' => 'contacts_invoices',
            'aggregate' => 'count',
        ]);

        $this->assertSame('contacts', $result['peopleModuleSlug']);
        $this->assertCount(2, $result['rows']);
        $this->assertSame((string) $topContact->id, $result['rows'][0]['id']);
        $this->assertSame(2.0, $result['rows'][0]['value']);
        $this->assertSame((string) $otherContact->id, $result['rows'][1]['id']);
        $this->assertSame(1.0, $result['rows'][1]['value']);
    }

    public function test_people_via_relationship_sum_aggregate(): void
    {
        $invoices = $this->makeInvoicesModule();
        $this->makeContactsModule();
        $this->makeField($invoices, ['name' => 'total', 'type' => 'currency']);

        $relationship = Relationship::create([
            'name' => 'contacts_invoices',
            'label' => 'relationships.contacts_invoices',
            'left_module' => 'contacts',
            'right_module' => 'invoices',
            'type' => 'one-to-many',
        ]);

        // BaseModule::booted() auto-fills owner_id on create when none is given,
        // falling back to the first user in the DB — there must be one already.
        $this->makeUser();

        $contact = Contact::factory()->create();
        $invoiceA = Invoice::factory()->create(['total' => 100]);
        $invoiceB = Invoice::factory()->create(['total' => 250]);

        RelationshipLink::create(['relationship_id' => $relationship->id, 'left_id' => $contact->id, 'right_id' => $invoiceA->id]);
        RelationshipLink::create(['relationship_id' => $relationship->id, 'left_id' => $contact->id, 'right_id' => $invoiceB->id]);

        $result = AggregationService::people($invoices, [
            'relationshipName' => 'contacts_invoices',
            'aggregate' => 'sum',
            'field' => 'total',
        ]);

        $this->assertCount(1, $result['rows']);
        $this->assertSame((string) $contact->id, $result['rows'][0]['id']);
        $this->assertSame(350.0, $result['rows'][0]['value']);
    }

    public function test_people_rejects_unknown_relationship_name(): void
    {
        $invoices = $this->makeInvoicesModule();

        $this->expectException(HttpException::class);
        AggregationService::people($invoices, ['relationshipName' => 'does-not-exist', 'aggregate' => 'count']);
    }

    public function test_people_rejects_relationship_not_involving_module(): void
    {
        $invoices = $this->makeInvoicesModule();
        $this->makeContactsModule();
        $this->makeDealsModule();

        Relationship::create([
            'name' => 'deals_contacts',
            'label' => 'relationships.deals_contacts',
            'left_module' => 'deals',
            'right_module' => 'contacts',
            'type' => 'many-to-many',
        ]);

        $this->expectException(HttpException::class);
        AggregationService::people($invoices, ['relationshipName' => 'deals_contacts', 'aggregate' => 'count']);
    }

    public function test_people_via_relationship_rejects_disallowed_filter(): void
    {
        $invoices = $this->makeInvoicesModule();
        $this->makeContactsModule();

        Relationship::create([
            'name' => 'contacts_invoices',
            'label' => 'relationships.contacts_invoices',
            'left_module' => 'contacts',
            'right_module' => 'invoices',
            'type' => 'one-to-many',
        ]);

        $this->expectException(HttpException::class);
        AggregationService::people($invoices, [
            'relationshipName' => 'contacts_invoices',
            'aggregate' => 'count',
            'filters' => [['field' => 'notes', 'operator' => 'equals', 'value' => 'x']],
        ]);
    }
}
