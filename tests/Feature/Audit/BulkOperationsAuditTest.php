<?php

namespace Tests\Feature\Audit;

use App\Handlers\Modules\AccountsModuleHandler;
use App\Models\AuditLog;
use App\Models\Modules\Account;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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

    public function test_bulk_update_with_explicit_selection_logs_each_records_own_old_value(): void
    {
        $accounts = collect(range(1, 3))->map(fn ($i) => Account::create([
            'name' => "Account {$i}",
            'website' => "https://old-{$i}.example",
        ]));
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
        $this->assertArrayNotHasKey('affected_ids', $changes);

        $oldValuesByRecordId = DB::table('audit_log_affected_records')
            ->where('audit_log_id', $log->id)
            ->pluck('old_value', 'record_id');

        foreach ($accounts as $index => $account) {
            $this->assertSame(
                "https://old-".($index + 1).".example",
                json_decode($oldValuesByRecordId[(string) $account->id], true),
            );
            $this->assertSame('https://example.com', $account->fresh()->website);
        }
    }

    public function test_bulk_update_with_all_matching_also_logs_each_records_own_old_value(): void
    {
        $a = Account::create(['name' => 'Account A', 'website' => 'https://old-a.example']);
        $b = Account::create(['name' => 'Account B', 'website' => 'https://old-b.example']);
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

        $oldValuesByRecordId = DB::table('audit_log_affected_records')
            ->where('audit_log_id', $log->id)
            ->pluck('old_value', 'record_id');

        $this->assertSame('https://old-a.example', json_decode($oldValuesByRecordId[(string) $a->id], true));
        $this->assertSame('https://old-b.example', json_decode($oldValuesByRecordId[(string) $b->id], true));
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

        $oldValuesByRecordId = DB::table('audit_log_affected_records')
            ->where('audit_log_id', $log->id)
            ->pluck('old_value', 'record_id');

        $this->assertSame('Delete Me A', json_decode($oldValuesByRecordId[(string) $a->id], true));
        $this->assertSame('Delete Me B', json_decode($oldValuesByRecordId[(string) $b->id], true));
    }

    public function test_bulk_delete_with_all_matching_also_logs_each_records_label(): void
    {
        $a = Account::create(['name' => 'Bulk Delete A']);
        $b = Account::create(['name' => 'Bulk Delete B']);
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

        $oldValuesByRecordId = DB::table('audit_log_affected_records')
            ->where('audit_log_id', $log->id)
            ->pluck('old_value', 'record_id');

        // Records are gone by the time this runs — their label was captured into
        // old_value at delete time, since there's nothing left to query afterward.
        $this->assertSame('Bulk Delete A', json_decode($oldValuesByRecordId[(string) $a->id], true));
        $this->assertSame('Bulk Delete B', json_decode($oldValuesByRecordId[(string) $b->id], true));
    }
}
