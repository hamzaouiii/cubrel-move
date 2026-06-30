<?php

namespace Tests\Feature;

use App\Models\Modules\Deal;
use App\Models\Modules\Lead;
use App\Services\Aggregation\AggregationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\Concerns\InteractsWithDashboardFixtures;
use Tests\TestCase;

class AggregationServiceTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithDashboardFixtures;

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

    // ── metric() ─────────────────────────────────────────────────────────────

    public function test_metric_count(): void
    {
        $module = $this->makeLeadsModule();
        $owner  = $this->makeUser();
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

    // ── breakdown() ──────────────────────────────────────────────────────────

    public function test_breakdown_groups_and_orders_by_count_desc(): void
    {
        $module = $this->makeLeadsModule();
        $this->makeField($module, ['name' => 'company', 'type' => 'text']);
        $owner = $this->makeUser();

        Lead::factory()->count(2)->create(['owner_id' => $owner->id, 'company' => 'Acme Inc.']);
        Lead::factory()->count(1)->create(['owner_id' => $owner->id, 'company' => 'Globex Inc.']);

        $result = AggregationService::breakdown($module, [
            'groupBy'   => 'company',
            'metric'    => ['type' => 'count'],
            'chartType' => 'donut',
        ]);

        $this->assertSame(['Acme Inc.', 'Globex Inc.'], $result['labels']);
        $this->assertSame([2.0, 1.0], $result['series'][0]['data']);
    }

    public function test_breakdown_respects_limit(): void
    {
        $module = $this->makeLeadsModule();
        $this->makeField($module, ['name' => 'company', 'type' => 'text']);
        $owner = $this->makeUser();

        Lead::factory()->create(['owner_id' => $owner->id, 'company' => 'A']);
        Lead::factory()->create(['owner_id' => $owner->id, 'company' => 'B']);
        Lead::factory()->create(['owner_id' => $owner->id, 'company' => 'C']);

        $result = AggregationService::breakdown($module, [
            'groupBy'   => 'company',
            'metric'    => ['type' => 'count'],
            'chartType' => 'donut',
            'limit'     => 2,
        ]);

        $this->assertCount(2, $result['labels']);
    }

    public function test_breakdown_rejects_invalid_chart_type(): void
    {
        $module = $this->makeLeadsModule();
        $this->makeField($module, ['name' => 'company', 'type' => 'text']);

        $this->expectException(HttpException::class);
        AggregationService::breakdown($module, [
            'groupBy'   => 'company',
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
            'metric'    => ['type' => 'count'],
            'interval'  => 'month',
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
            'interval'  => 'fortnight',
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
        $owner  = $this->makeUser();
        Lead::factory()->count(3)->create(['owner_id' => $owner->id]);

        $result = AggregationService::recordList($module, ['limit' => 2]);

        $this->assertCount(2, $result['rows']);
        $this->assertSame('leads', $result['moduleSlug']);
    }
}
