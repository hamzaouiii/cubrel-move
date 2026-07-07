<?php

namespace Tests\Feature\Audit;

use App\Models\ImpersonationSession;
use App\Models\Settings\SettingValue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\InteractsWithDashboardFixtures;
use Tests\TestCase;

/**
 * Covers ImpersonationSessionController — deliberately gated to "any admin",
 * not root-only, per docs/audit-trail-implementation.md §2/§6.
 */
class ImpersonationSessionControllerTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithDashboardFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        $this->completeOnboarding();
        SettingValue::create(['setting_item' => 'preferences', 'key' => 'list_view_limit', 'value' => 25]);
    }

    public function test_non_admin_is_forbidden(): void
    {
        $this->actingAs($this->makeUser(['is_admin' => false]));

        $this->get('/settings/impersonation-sessions')->assertForbidden();
    }

    public function test_plain_admin_without_root_can_view_sessions(): void
    {
        // Deliberately NOT root — this page is admin-visible, no root guard.
        $admin = $this->makeUser(['is_admin' => true, 'is_root' => false]);
        $this->actingAs($admin);

        $root = $this->makeUser(['is_root' => true]);
        $target = $this->makeUser();

        ImpersonationSession::create([
            'impersonator_id' => $root->id,
            'target_user_id' => $target->id,
            'ip_address' => '10.0.0.1',
            'started_at' => now()->subMinute(),
            'ended_at' => now(),
        ]);

        $response = $this->get('/settings/impersonation-sessions');

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Settings/ImpersonationSessions/Index')
            ->has('sessions', 1)
            ->where('sessions.0.ongoing', false)
        );
    }

    public function test_filters_by_target_user(): void
    {
        $admin = $this->makeUser(['is_admin' => true]);
        $this->actingAs($admin);

        $root = $this->makeUser(['is_root' => true]);
        $targetA = $this->makeUser();
        $targetB = $this->makeUser();

        ImpersonationSession::create([
            'impersonator_id' => $root->id,
            'target_user_id' => $targetA->id,
            'started_at' => now()->subMinute(),
            'ended_at' => now(),
        ]);
        ImpersonationSession::create([
            'impersonator_id' => $root->id,
            'target_user_id' => $targetB->id,
            'started_at' => now()->subMinute(),
            'ended_at' => now(),
        ]);

        $response = $this->get("/settings/impersonation-sessions?target_user_id={$targetA->id}");

        $response->assertInertia(fn (Assert $page) => $page
            ->has('sessions', 1)
            ->where('sessions.0.target_user.id', (string) $targetA->id)
        );
    }

    public function test_ongoing_session_is_flagged_as_such(): void
    {
        $this->actingAs($this->makeUser(['is_admin' => true]));

        $root = $this->makeUser(['is_root' => true]);
        $target = $this->makeUser();

        ImpersonationSession::create([
            'impersonator_id' => $root->id,
            'target_user_id' => $target->id,
            'started_at' => now()->subMinute(),
            'ended_at' => null,
        ]);

        $response = $this->get('/settings/impersonation-sessions');

        $response->assertInertia(fn (Assert $page) => $page
            ->where('sessions.0.ongoing', true)
        );
    }
}
