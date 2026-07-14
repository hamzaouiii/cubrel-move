<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiRouteGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_icons_endpoint_rejects_guests(): void
    {
        $this->getJson('/api/icons')->assertUnauthorized();
    }

    public function test_icons_endpoint_allows_authenticated_users(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->getJson('/api/icons')->assertOk();
    }

    public function test_dropdown_lists_endpoint_rejects_guests(): void
    {
        $this->getJson('/api/dropdown-lists')->assertUnauthorized();
    }

    public function test_dropdown_lists_endpoint_allows_authenticated_users(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->getJson('/api/dropdown-lists')->assertOk();
    }

    public function test_related_module_records_endpoint_rejects_guests(): void
    {
        $this->getJson('/api/related-module-records/some-id')->assertUnauthorized();
    }

    public function test_icons_test_debug_route_no_longer_exists(): void
    {
        // The route itself is gone from routes/api.php. The path now falls through
        // to web.php's generic `{module}/{recordId}` catch-all, which still requires
        // auth — so a guest gets blocked either way, but the important assertion is
        // that the old public "icons api alive" debug response is gone for good.
        $response = $this->getJson('/api/icons-test');

        $response->assertStatus(401);
        $response->assertDontSee('icons api alive');
    }
}
