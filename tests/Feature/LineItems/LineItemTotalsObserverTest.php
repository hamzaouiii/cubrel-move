<?php

namespace Tests\Feature\LineItems;

use App\Models\AuditLog;
use App\Models\Modules\LineItem;
use App\Models\Modules\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Concerns\InteractsWithDashboardFixtures;
use Tests\TestCase;

class LineItemTotalsObserverTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithDashboardFixtures;

    protected Order $order;

    protected function setUp(): void
    {
        parent::setUp();
        $this->completeOnboarding();

        $this->makeModule([
            'slug' => 'orders',
            'name' => 'Orders',
            'path' => '/orders',
            'model_class' => Order::class,
            'table_name' => 'orders',
        ]);

        $this->actingAs($this->makeUser(['is_admin' => true]));

        $this->order = Order::create(['name' => 'Test Order']);
    }

    protected function makeLineItem(array $overrides = []): LineItem
    {
        $item = new LineItem(array_merge([
            'parent_type' => 'orders',
            'parent_id' => $this->order->id,
            'name' => 'Widget',
            'unit_price' => 100,
            'quantity' => 1,
            'discount' => 0,
            'tax_rate' => 0,
        ], $overrides));
        $item->id = Str::uuid();
        $item->calculateTotals()->save();

        return $item;
    }

    public function test_creating_a_line_item_recomputes_the_parent_total(): void
    {
        $this->makeLineItem(['unit_price' => 100, 'quantity' => 2]);

        $this->order->refresh();
        $this->assertSame('200.00', (string) $this->order->total);
        $this->assertSame('200.00', (string) $this->order->subtotal);
    }

    public function test_totals_sum_across_multiple_line_items(): void
    {
        $this->makeLineItem(['unit_price' => 100, 'quantity' => 1]);
        $this->makeLineItem(['unit_price' => 50, 'quantity' => 2]);

        $this->order->refresh();
        $this->assertSame('200.00', (string) $this->order->total);
    }

    public function test_updating_a_line_item_recomputes_the_parent_total(): void
    {
        $item = $this->makeLineItem(['unit_price' => 100, 'quantity' => 1]);

        $item->fill(['unit_price' => 300])->calculateTotals()->save();

        $this->order->refresh();
        $this->assertSame('300.00', (string) $this->order->total);
    }

    public function test_deleting_a_line_item_recomputes_the_parent_total(): void
    {
        $first = $this->makeLineItem(['unit_price' => 100, 'quantity' => 1]);
        $this->makeLineItem(['unit_price' => 50, 'quantity' => 1]);

        $first->delete();

        $this->order->refresh();
        $this->assertSame('50.00', (string) $this->order->total);
    }

    public function test_deleting_the_last_line_item_zeroes_out_the_parent_total(): void
    {
        $item = $this->makeLineItem(['unit_price' => 100, 'quantity' => 1]);

        $item->delete();

        $this->order->refresh();
        $this->assertSame('0.00', (string) $this->order->total);
    }

    

    public function test_recomputing_the_parent_does_not_create_an_audit_log_entry(): void
    {
        $this->makeLineItem(['unit_price' => 100, 'quantity' => 1]);

        $this->assertSame(0, AuditLog::where('module_slug', 'orders')
            ->where('record_id', $this->order->id)
            ->where('action', 'updated')
            ->count());
    }

    public function test_a_line_item_for_an_unregistered_parent_module_does_not_crash(): void
    {
        $item = new LineItem([
            'parent_type' => 'nonexistent-module',
            'parent_id' => (string) Str::uuid(),
            'name' => 'Orphan',
            'unit_price' => 100,
            'quantity' => 1,
        ]);
        $item->id = Str::uuid();

        $item->calculateTotals()->save();

        $this->assertTrue(true);
    }
}
