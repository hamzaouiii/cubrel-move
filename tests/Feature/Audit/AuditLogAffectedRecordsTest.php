<?php

namespace Tests\Feature\Audit;

use App\Handlers\Modules\AccountsModuleHandler;
use App\Models\AuditLog;
use App\Models\Modules\Account;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithDashboardFixtures;
use Tests\TestCase;

/**
 * Covers AuditLogController::affectedRecords() — the per-record breakdown
 * behind the global Audit Trail's "list of affected records" view for a bulk
 * update/delete batch row. See docs/audit-trail-implementation.md §4.2/§6.1.
 */
class AuditLogAffectedRecordsTest extends TestCase
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
    }

    public function test_non_admin_is_forbidden(): void
    {
        $this->actingAs($this->makeUser(['is_admin' => false]));

        $log = AuditLog::create(['action' => 'updated', 'module_slug' => 'accounts']);

        $this->get("/settings/audit-trail/{$log->id}/affected-records")->assertForbidden();
    }

    public function test_bulk_update_row_lists_each_records_own_old_and_new_value(): void
    {
        $this->actingAs($this->makeUser(['is_admin' => true]));

        $a = Account::create(['name' => 'Account A', 'website' => 'https://old-a.example']);
        $b = Account::create(['name' => 'Account B', 'website' => 'https://old-b.example']);
        AuditLog::query()->delete();

        $this->put('/accounts', [
            'field' => 'accounts.website',
            'value' => 'https://new.example',
            'selectedIds' => [(string) $a->id, (string) $b->id],
            'allMatchingSelected' => false,
        ])->assertSessionHas('success');

        $log = AuditLog::where('action', 'updated')->firstOrFail();

        $response = $this->getJson("/settings/audit-trail/{$log->id}/affected-records");

        $response->assertOk();
        $this->assertSame('updated', $response->json('log.action'));
        $this->assertSame('https://new.example', $response->json('log.changes.value'));

        $rows = collect($response->json('data'))->keyBy('record_id');

        $this->assertSame('Account A', $rows[(string) $a->id]['label']);
        $this->assertSame('https://old-a.example', $rows[(string) $a->id]['old_value']);
        $this->assertSame('Account B', $rows[(string) $b->id]['label']);
        $this->assertSame('https://old-b.example', $rows[(string) $b->id]['old_value']);
    }

    public function test_bulk_delete_row_lists_each_records_captured_label(): void
    {
        $this->actingAs($this->makeUser(['is_admin' => true]));

        $a = Account::create(['name' => 'Delete Me A']);
        $b = Account::create(['name' => 'Delete Me B']);
        AuditLog::query()->delete();

        $this->delete('/accounts', [
            'selectedIds' => [(string) $a->id, (string) $b->id],
            'allMatchingSelected' => false,
        ])->assertSessionHas('success');

        $log = AuditLog::where('action', 'deleted')->firstOrFail();

        $response = $this->getJson("/settings/audit-trail/{$log->id}/affected-records");

        $response->assertOk();

        $rows = collect($response->json('data'))->keyBy('record_id');

        $this->assertSame('Delete Me A', $rows[(string) $a->id]['label']);
        $this->assertFalse($rows[(string) $a->id]['still_exists']);
        $this->assertSame('Delete Me B', $rows[(string) $b->id]['label']);
    }
}
