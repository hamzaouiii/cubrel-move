<?php

namespace Tests\Feature\Audit;

use App\Models\AuditLog;
use App\Models\ImpersonationSession;
use App\Models\Modules\Account;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithDashboardFixtures;
use Tests\TestCase;

/**
 * Covers impersonation session tracking (UserController::impersonate()/
 * leaveImpersonation()) and the actor-resolution/transparency behavior of
 * AuditService::log() while impersonating. See
 * docs/audit-trail-implementation.md §3, §3.1, §5.2.
 */
class ImpersonationAuditTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithDashboardFixtures;

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
    }

    public function test_impersonating_creates_a_session_row(): void
    {
        $root = $this->makeUser(['is_root' => true, 'is_admin' => true, 'status' => 'active']);
        $target = $this->makeUser(['is_root' => false, 'status' => 'active']);
        $this->actingAs($root);

        $response = $this->post(route('impersonate', $target));
        $response->assertRedirect(route('dashboard'));

        $session = ImpersonationSession::firstOrFail();
        $this->assertSame((string) $root->id, (string) $session->impersonator_id);
        $this->assertSame((string) $target->id, (string) $session->target_user_id);
        $this->assertNotNull($session->started_at);
        $this->assertNull($session->ended_at);
        $this->assertNotNull($session->ip_address);
    }

    public function test_actions_taken_while_impersonating_are_fully_transparent_not_masked(): void
    {
        $root = $this->makeUser(['is_root' => true, 'is_admin' => true, 'status' => 'active']);
        $target = $this->makeUser(['is_root' => false, 'is_admin' => true, 'status' => 'active']);
        $this->actingAs($root);

        $this->post(route('impersonate', $target))->assertRedirect();

        // Every subsequent request in this test is now authenticated AS $target.
        $account = Account::create(['name' => 'Original']);
        AuditLog::query()->delete(); // isolate from the 'created' row

        $this->put("/accounts/{$account->id}", ['name' => 'Changed While Impersonating'])
            ->assertSessionHas('success');

        $log = AuditLog::where('action', 'updated')->where('record_id', $account->id)->firstOrFail();

        // user_id is the session identity (the impersonated user) ...
        $this->assertSame((string) $target->id, (string) $log->user_id);
        // ... but impersonator_id always reveals the real actor, unconditionally.
        $this->assertSame((string) $root->id, (string) $log->impersonator_id);

        $display = $log->toDisplayArray();
        $this->assertSame($root->name, $display['impersonator']['name']);
    }

    /**
     * Regression test for docs/audit-trail-implementation.md §3.1: closing
     * out the session must read session('impersonator_id') /
     * session('impersonation_session_id') BEFORE Auth::login()/forget() run,
     * or the session row ends up unattributed.
     */
    public function test_leaving_impersonation_closes_the_session_with_correct_attribution(): void
    {
        $root = $this->makeUser(['is_root' => true, 'is_admin' => true, 'status' => 'active']);
        $target = $this->makeUser(['is_root' => false, 'status' => 'active']);
        $this->actingAs($root);

        $this->post(route('impersonate', $target))->assertRedirect();
        $session = ImpersonationSession::firstOrFail();
        $this->assertNull($session->ended_at);

        $this->post(route('leave-impersonate'))->assertRedirect(route('dashboard'));

        $session->refresh();
        $this->assertNotNull($session->ended_at);
        $this->assertSame((string) $root->id, (string) $session->impersonator_id);
        $this->assertSame((string) $target->id, (string) $session->target_user_id);
        $this->assertGreaterThanOrEqual(0, $session->durationInSeconds());
    }

    /**
     * Regression test for docs/audit-trail-implementation.md §5.2: Carbon's
     * diffInSeconds() sign convention previously produced a negative
     * duration for a session that had clearly already elapsed positively.
     */
    public function test_session_duration_is_positive_not_negative(): void
    {
        $root = $this->makeUser(['is_root' => true]);
        $target = $this->makeUser();

        $session = ImpersonationSession::create([
            'impersonator_id' => $root->id,
            'target_user_id' => $target->id,
            'ip_address' => '127.0.0.1',
            'started_at' => now()->subSeconds(5),
            'ended_at' => now(),
        ]);

        $this->assertGreaterThan(0, $session->durationInSeconds());
    }

    public function test_ongoing_session_duration_is_also_positive(): void
    {
        $root = $this->makeUser(['is_root' => true]);
        $target = $this->makeUser();

        $session = ImpersonationSession::create([
            'impersonator_id' => $root->id,
            'target_user_id' => $target->id,
            'started_at' => now()->subSeconds(3),
            'ended_at' => null,
        ]);

        $this->assertGreaterThan(0, $session->durationInSeconds());
        $this->assertTrue($session->toDisplayArray()['ongoing']);
    }
}
