<?php

namespace Tests\Feature\Audit;

use App\Models\AuditLog;
use App\Services\Audit\AuditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
