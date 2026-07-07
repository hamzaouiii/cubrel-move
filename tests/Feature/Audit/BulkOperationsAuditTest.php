<?php

namespace Tests\Feature\Audit;

use App\Handlers\Modules\AccountsModuleHandler;
use App\Models\AuditLog;
use App\Models\Modules\Account;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithDashboardFixtures;
use Tests\TestCase;

/**
 * Covers the explicit AuditService::log() calls in RecordController's
 * updateMany()/destroyMany() — bulk operations use query-builder writes that
 * never fire Eloquent model events, so AuditObserver alone can't see them.
 * See docs/audit-trail-implementation.md §4.2.
 */
class BulkOperationsAuditTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithDashboardFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        $this->completeOnboarding();

        $module = $this->makeModule([
            'slug' => 'accounts',
            'name' => 'Accounts',
            'path' => '/accounts',
            'model_class' => Account::class,
            'handler_class' => AccountsModuleHandler::class,
            'table_name' => 'accounts',
        ]);

        $this->makeField($module, [
            'name' => 'website',
            'key' => 'accounts.website',
            'type' => 'text',
            'is_custom' => false,
        ]);

        $this->actingAs($this->makeUser(['is_admin' => true]));
    }

    public function test_bulk_update_with_explicit_selection_logs_one_row_with_affected_ids(): void
    {
        $accounts = collect(range(1, 3))->map(fn ($i) => Account::create(['name' => "Account {$i}"]));
        AuditLog::query()->delete();

        $this->put('/accounts', [
            'field' => 'accounts.website',
            'value' => 'https://example.com',
            'selectedIds' => $accounts->pluck('id')->map(fn ($id) => (string) $id)->all(),
            'allMatchingSelected' => false,
        ])->assertSessionHas('success');

        $this->assertSame(1, AuditLog::where('action', 'updated')->count());

        $log = AuditLog::where('action', 'updated')->firstOrFail();
        $changes = $log->toDisplayArray()['changes'];

        $this->assertSame('explicit', $changes['mode']);
        $this->assertSame(3, $changes['count']);
        $this->assertEqualsCanonicalizing(
            $accounts->pluck('id')->map(fn ($id) => (string) $id)->all(),
            collect($changes['affected_ids'])->map(fn ($id) => (string) $id)->all(),
        );

        foreach ($accounts as $account) {
            $this->assertSame('https://example.com', $account->fresh()->website);
        }
    }

    public function test_bulk_update_with_all_matching_logs_count_without_affected_ids(): void
    {
        Account::create(['name' => 'Account A']);
        Account::create(['name' => 'Account B']);
        AuditLog::query()->delete();

        $this->put('/accounts', [
            'field' => 'accounts.website',
            'value' => 'https://all-matching.example',
            'allMatchingSelected' => true,
            'filters' => [],
        ])->assertSessionHas('success');

        $log = AuditLog::where('action', 'updated')->firstOrFail();
        $changes = $log->toDisplayArray()['changes'];

        $this->assertSame('all_matching', $changes['mode']);
        $this->assertSame(2, $changes['count']);
        $this->assertArrayNotHasKey('affected_ids', $changes);
    }

    public function test_bulk_delete_with_explicit_selection_captures_labels_before_deleting(): void
    {
        $a = Account::create(['name' => 'Delete Me A']);
        $b = Account::create(['name' => 'Delete Me B']);
        AuditLog::query()->delete();

        $this->delete('/accounts', [
            'selectedIds' => [(string) $a->id, (string) $b->id],
            'allMatchingSelected' => false,
        ])->assertSessionHas('success');

        $this->assertSame(0, Account::whereIn('id', [$a->id, $b->id])->count());

        $log = AuditLog::where('action', 'deleted')->firstOrFail();
        $changes = $log->toDisplayArray()['changes'];

        $this->assertSame('explicit', $changes['mode']);
        $this->assertSame(2, $changes['count']);
        $this->assertSame('Delete Me A', $changes['record_labels'][(string) $a->id]);
        $this->assertSame('Delete Me B', $changes['record_labels'][(string) $b->id]);
    }

    public function test_bulk_delete_with_all_matching_logs_count_only(): void
    {
        Account::create(['name' => 'Bulk Delete A']);
        Account::create(['name' => 'Bulk Delete B']);
        AuditLog::query()->delete();

        $this->delete('/accounts', [
            'allMatchingSelected' => true,
            'filters' => [],
        ])->assertSessionHas('success');

        $this->assertSame(0, Account::count());

        $log = AuditLog::where('action', 'deleted')->firstOrFail();
        $changes = $log->toDisplayArray()['changes'];

        $this->assertSame('all_matching', $changes['mode']);
        $this->assertSame(2, $changes['count']);
        $this->assertArrayNotHasKey('affected_ids', $changes);
    }
}
