<?php

namespace Tests\Feature\Audit;

use App\Models\AuditLog;
use App\Models\Modules\Account;
use App\Models\Modules\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithDashboardFixtures;
use Tests\TestCase;

class AuditObserverTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithDashboardFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        $this->completeOnboarding();
    }

    protected function registerAccountsModule(): void
    {
        $this->makeModule([
            'slug' => 'accounts',
            'name' => 'Accounts',
            'path' => '/accounts',
            'model_class' => Account::class,
            'table_name' => 'accounts',
        ]);
    }

    protected function registerOrdersModule(): \App\Models\Module
    {
        return $this->makeModule([
            'slug' => 'orders',
            'name' => 'Orders',
            'path' => '/orders',
            'model_class' => Order::class,
            'table_name' => 'orders',
        ]);
    }

    public function test_creating_a_record_logs_a_created_event_with_null_diff(): void
    {
        $this->registerAccountsModule();
        $user = $this->makeUser(['is_admin' => true]);
        $this->actingAs($user);

        $account = Account::create(['name' => 'Acme Ltd']);

        $log = AuditLog::where('action', 'created')
            ->where('module_slug', 'accounts')
            ->where('record_id', $account->id)
            ->firstOrFail();

        $this->assertSame((string) $user->id, (string) $log->user_id);
        $this->assertNull($log->impersonator_id);
        $this->assertNull($log->getAttribute('diff'));
    }

    public function test_updating_a_record_logs_the_changed_field_diff(): void
    {
        $this->registerAccountsModule();
        $user = $this->makeUser(['is_admin' => true]);
        $this->actingAs($user);

        $account = Account::create(['name' => 'Original Name']);
        AuditLog::query()->delete(); 

        $account->update(['name' => 'New Name']);

        $log = AuditLog::where('action', 'updated')
            ->where('record_id', $account->id)
            ->firstOrFail();

        $diff = $log->toDisplayArray()['changes'];
        $this->assertSame('Original Name', $diff['name']['old']);
        $this->assertSame('New Name', $diff['name']['new']);
    }

    public function test_saving_with_no_actual_changes_does_not_log_anything(): void
    {
        $this->registerAccountsModule();
        $user = $this->makeUser(['is_admin' => true]);
        $this->actingAs($user);

        $account = Account::create(['name' => 'Unchanged Co']);
        $countBefore = AuditLog::count();

        
        
        $account->update(['name' => 'Unchanged Co']);

        $this->assertSame($countBefore, AuditLog::count());
    }

    

    public function test_fields_flagged_is_calculated_are_excluded_from_the_diff(): void
    {
        $ordersModule = $this->registerOrdersModule();

        foreach (['total', 'subtotal', 'tax_amount', 'discount_amount'] as $name) {
            $this->makeField($ordersModule, [
                'name' => $name,
                'key' => "orders.{$name}",
                'type' => 'currency',
                'is_calculated' => true,
            ]);
        }

        $user = $this->makeUser(['is_admin' => true]);
        $this->actingAs($user);

        $order = Order::create(['name' => 'Order 1', 'status' => 'draft']);
        AuditLog::query()->delete();

        
        
        $order->update([
            'status' => 'confirmed',
            'total' => 999,
            'subtotal' => 999,
            'tax_amount' => 0,
            'discount_amount' => 0,
        ]);

        $log = AuditLog::where('action', 'updated')->where('record_id', $order->id)->firstOrFail();
        $diff = $log->toDisplayArray()['changes'];

        $this->assertArrayHasKey('status', $diff);
        $this->assertArrayNotHasKey('total', $diff);
        $this->assertArrayNotHasKey('subtotal', $diff);
        $this->assertArrayNotHasKey('tax_amount', $diff);
        $this->assertArrayNotHasKey('discount_amount', $diff);
    }

    

    public function test_is_calculated_flag_not_field_name_drives_exclusion(): void
    {
        $ordersModule = $this->registerOrdersModule();

        $this->makeField($ordersModule, [
            'name' => 'total',
            'key' => 'orders.total',
            'type' => 'currency',
            'is_calculated' => false,
        ]);
        
        
        $this->makeField($ordersModule, [
            'name' => 'order_number',
            'key' => 'orders.order_number',
            'type' => 'text',
            'is_calculated' => true,
        ]);

        $user = $this->makeUser(['is_admin' => true]);
        $this->actingAs($user);

        $order = Order::create(['name' => 'Order 1', 'total' => 0, 'order_number' => 'ORD-1']);
        AuditLog::query()->delete();

        $order->update(['total' => 500, 'order_number' => 'ORD-2']);

        $log = AuditLog::where('action', 'updated')->where('record_id', $order->id)->firstOrFail();
        $diff = $log->toDisplayArray()['changes'];

        $this->assertArrayHasKey('total', $diff);
        $this->assertArrayNotHasKey('order_number', $diff);
    }

    public function test_deleting_a_record_captures_its_display_label(): void
    {
        $this->registerAccountsModule();
        $user = $this->makeUser(['is_admin' => true]);
        $this->actingAs($user);

        $account = Account::create(['name' => 'Soon Gone LLC']);
        $accountId = $account->id;
        $account->delete();

        $log = AuditLog::where('action', 'deleted')
            ->where('record_id', $accountId)
            ->firstOrFail();

        $this->assertSame('Soon Gone LLC', $log->toDisplayArray()['changes']['record_label']);
    }

    public function test_record_type_field_changes_resolve_related_display_labels(): void
    {
        $this->registerAccountsModule();
        $this->makeModule([
            'slug' => 'users',
            'name' => 'Users',
            'path' => '/users',
            'model_class' => User::class,
            'table_name' => 'users',
        ]);

        $accountsModule = \App\Models\Module::where('slug', 'accounts')->firstOrFail();
        $this->makeField($accountsModule, [
            'name' => 'owner_id',
            'key' => 'accounts.owner_id',
            'type' => 'record',
            'related_module' => 'users',
        ]);

        $root = $this->makeUser(['is_admin' => true, 'is_root' => true, 'first_name' => 'Root', 'last_name' => 'User']);
        $newOwner = $this->makeUser(['first_name' => 'New', 'last_name' => 'Owner']);
        $this->actingAs($root);

        $account = Account::create(['name' => 'Reassign Me', 'owner_id' => $root->id]);
        AuditLog::query()->delete();

        $account->update(['owner_id' => $newOwner->id]);

        $log = AuditLog::where('action', 'updated')->where('record_id', $account->id)->firstOrFail();
        $diff = $log->toDisplayArray()['changes'];

        $this->assertSame((string) $root->id, (string) $diff['owner_id']['old']);
        $this->assertSame((string) $newOwner->id, (string) $diff['owner_id']['new']);
        $this->assertSame($root->name, $diff['owner_id']['old_label']);
        $this->assertSame($newOwner->name, $diff['owner_id']['new_label']);
    }

    

    public function test_non_admin_can_save_their_own_user_record_without_crashing(): void
    {
        $this->makeModule([
            'slug' => 'users',
            'name' => 'Users',
            'path' => '/users',
            'model_class' => User::class,
            'table_name' => 'users',
        ]);

        $plainUser = $this->makeUser(['is_admin' => false, 'first_name' => 'Plain', 'last_name' => 'User']);
        $this->actingAs($plainUser);

        
        
        $plainUser->update(['first_name' => 'Updated']);

        $this->assertSame('Updated', $plainUser->fresh()->first_name);
    }
}
