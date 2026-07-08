<?php

namespace Tests\Feature\Audit;

use App\Models\AuditLog;
use App\Services\Audit\AuditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\InteractsWithDashboardFixtures;
use Tests\TestCase;

/**
 * Covers AuditService::log() directly — the single write path every audit
 * caller (AuditObserver, RecordController's bulk operations,
 * RelationshipService::link()/unlink()) goes through. See
 * docs/audit-trail-implementation.md §3, §5 (added after demo-data
 * relationship seeding was found logging audit rows with no real actor).
 */
class AuditServiceTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithDashboardFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        $this->completeOnboarding();
    }

    public function test_log_is_a_no_op_without_an_authenticated_actor(): void
    {
        auth()->logout();

        AuditService::log('updated', 'accounts', 'some-id', ['field' => ['old' => 'a', 'new' => 'b']]);

        $this->assertSame(0, AuditLog::count());
    }

    public function test_log_writes_normally_with_an_authenticated_actor(): void
    {
        $user = $this->makeUser();
        $this->actingAs($user);

        AuditService::log('updated', 'accounts', 'some-id', ['field' => ['old' => 'a', 'new' => 'b']]);

        $log = AuditLog::firstOrFail();
        $this->assertSame((string) $user->id, (string) $log->user_id);
    }

    public function test_log_persists_affected_ids_into_the_join_table_not_the_diff(): void
    {
        $this->actingAs($this->makeUser());

        AuditService::log('updated', 'accounts', null, ['mode' => 'all_matching', 'count' => 2], ['id-a', 'id-b']);

        $log = AuditLog::firstOrFail();
        $this->assertArrayNotHasKey('affected_ids', $log->diff);
        $this->assertEqualsCanonicalizing(
            ['id-a', 'id-b'],
            DB::table('audit_log_affected_records')->where('audit_log_id', $log->id)->pluck('record_id')->all(),
        );
    }

    public function test_log_is_a_no_op_without_an_authenticated_actor_even_with_affected_ids(): void
    {
        auth()->logout();

        AuditService::log('updated', 'accounts', null, ['mode' => 'all_matching'], ['id-a', 'id-b']);

        $this->assertSame(0, AuditLog::count());
        $this->assertSame(0, DB::table('audit_log_affected_records')->count());
    }
}
