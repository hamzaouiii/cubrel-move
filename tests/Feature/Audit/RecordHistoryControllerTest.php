<?php

namespace Tests\Feature\Audit;

use App\Models\AuditLog;
use App\Models\Modules\Account;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithDashboardFixtures;
use Tests\TestCase;

/**
 * Covers RecordHistoryController — the per-record "View History" JSON
 * endpoint. See docs/audit-trail-implementation.md §4.2/§6.1.
 */
class RecordHistoryControllerTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithDashboardFixtures;

    protected Account $accountA;
    protected Account $accountB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->completeOnboarding();

        $this->makeModule([
            'slug' => 'accounts',
            'name' => 'Accounts',
            'path' => '/accounts',
            'model_class' => Account::class,
            'table_name' => 'accounts',
        ]);

        $this->actingAs($this->makeUser());

        $this->accountA = Account::create(['name' => 'Account A']);
        $this->accountB = Account::create(['name' => 'Account B']);
    }

    public function test_history_only_includes_entries_for_the_requested_record(): void
    {
        AuditLog::query()->delete();

        $this->accountA->update(['name' => 'Account A Updated']);
        $this->accountB->update(['name' => 'Account B Updated']);

        $response = $this->getJson("/modules/accounts/{$this->accountA->id}/history");

        $response->assertOk();
        $data = $response->json('data');

        $this->assertCount(1, $data);
        $this->assertSame((string) $this->accountA->id, (string) $data[0]['record_id']);
    }

    /**
     * Regression test for the fix documented in
     * docs/audit-trail-implementation.md §4.2/§6: a bulk edit logs a single
     * batch row with record_id = null, so a record's own history must also
     * match on 'affected_ids' inside the diff, not just record_id.
     */
    public function test_history_includes_bulk_batch_entries_the_record_was_part_of(): void
    {
        AuditLog::query()->delete();

        \App\Services\Audit\AuditService::log('updated', 'accounts', null, [
            'mode' => 'explicit',
            'field' => 'website',
            'value' => 'https://example.com',
            'count' => 2,
            'affected_ids' => [(string) $this->accountA->id, (string) $this->accountB->id],
        ]);

        $response = $this->getJson("/modules/accounts/{$this->accountA->id}/history");

        $response->assertOk();
        $data = $response->json('data');

        $this->assertCount(1, $data);
        $this->assertNull($data[0]['record_id']);
        $this->assertSame(2, $data[0]['changes']['count']);
    }

    public function test_history_for_an_unrelated_record_does_not_see_another_records_bulk_batch(): void
    {
        $accountC = Account::create(['name' => 'Account C']);
        AuditLog::query()->delete();

        \App\Services\Audit\AuditService::log('updated', 'accounts', null, [
            'mode' => 'explicit',
            'field' => 'website',
            'value' => 'https://example.com',
            'count' => 2,
            'affected_ids' => [(string) $this->accountA->id, (string) $this->accountB->id],
        ]);

        $response = $this->getJson("/modules/accounts/{$accountC->id}/history");

        $response->assertOk();
        $this->assertCount(0, $response->json('data'));
    }
}
