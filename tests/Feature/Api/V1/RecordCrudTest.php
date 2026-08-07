<?php

namespace Tests\Feature\Api\V1;

use App\Models\Modules\Lead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithApiFixtures;
use Tests\TestCase;

class RecordCrudTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithApiFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        $this->completeOnboarding();
        $this->makeLeadsModule();
        $this->makeField($this->leadsModule(), ['name' => 'name', 'type' => 'text', 'required' => true]);
        $this->makeField($this->leadsModule(), ['name' => 'email', 'type' => 'email']);
        $this->makeField($this->leadsModule(), ['name' => 'favorite_color', 'type' => 'text', 'is_custom' => true]);
    }

    protected function leadsModule()
    {
        return \App\Models\Module::where('slug', 'leads')->firstOrFail();
    }

    public function test_index_rejects_requests_without_a_token(): void
    {
        $this->getJson('/api/v1/leads')->assertUnauthorized();
    }

    public function test_index_lists_records_in_the_data_envelope(): void
    {
        $this->apiActingAs();
        Lead::create(['name' => 'Acme Corp']);
        Lead::create(['name' => 'Globex Inc']);

        $response = $this->getJson('/api/v1/leads')->assertOk();

        $response->assertJsonCount(2, 'data');
        $response->assertJsonStructure(['data', 'meta' => ['total', 'per_page', 'current_page', 'last_page'], 'links' => ['next', 'prev']]);
    }

    public function test_index_search_filters_by_name(): void
    {
        $this->apiActingAs();
        Lead::create(['name' => 'Acme Corp']);
        Lead::create(['name' => 'Globex Inc']);

        $response = $this->getJson('/api/v1/leads?search=Globex')->assertOk();

        $response->assertJsonCount(1, 'data');
        $this->assertSame('Globex Inc', $response->json('data.0.name'));
    }

    public function test_index_does_not_embed_related_data(): void
    {
        $this->apiActingAs();
        Lead::create(['name' => 'Acme Corp']);

        $response = $this->getJson('/api/v1/leads')->assertOk();

        $this->assertArrayNotHasKey('related', $response->json('data.0'));
    }

    public function test_show_returns_a_single_record_with_related_embedded(): void
    {
        $this->apiActingAs();
        $lead = Lead::create(['name' => 'Acme Corp']);

        $response = $this->getJson("/api/v1/leads/{$lead->id}")->assertOk();

        $this->assertSame('Acme Corp', $response->json('data.name'));
        $this->assertArrayHasKey('related', $response->json('data'));
    }

    public function test_show_returns_404_for_a_missing_record_without_leaking_details(): void
    {
        $this->apiActingAs();

        $response = $this->getJson('/api/v1/leads/019fdb98-7f62-71e2-95c3-c28e1bd1f0da')->assertStatus(404);

        $response->assertJson(['message' => 'Resource not found.']);
        $response->assertDontSee('Lead');
    }

    public function test_store_creates_a_record_from_writable_fields(): void
    {
        $this->apiActingAs();

        $response = $this->postJson('/api/v1/leads', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ])->assertCreated();

        $this->assertSame('Jane Doe', $response->json('data.name'));
        $this->assertDatabaseHas('leads', ['name' => 'Jane Doe', 'email' => 'jane@example.com']);
    }

    public function test_store_writes_custom_fields_flat_into_the_response(): void
    {
        $this->apiActingAs();

        $response = $this->postJson('/api/v1/leads', [
            'name' => 'Jane Doe',
            'favorite_color' => 'teal',
        ])->assertCreated();

        $this->assertSame('teal', $response->json('data.favorite_color'));
        $this->assertArrayNotHasKey('custom_fields', $response->json('data'));
    }

    public function test_store_rejects_missing_required_fields_with_422_json(): void
    {
        $this->apiActingAs();

        $response = $this->postJson('/api/v1/leads', [])->assertStatus(422);

        $response->assertJsonValidationErrors(['name']);
    }

    public function test_store_ignores_owner_id_from_the_payload(): void
    {
        $user = $this->apiActingAs();

        $response = $this->postJson('/api/v1/leads', [
            'name' => 'Jane Doe',
            'owner_id' => '019fdb98-7f62-71e2-95c3-c28e1bd1f0da',
        ])->assertCreated();

        $this->assertSame((string) $user->id, $response->json('data.owner_id'));
    }

    public function test_update_applies_a_partial_patch(): void
    {
        $this->apiActingAs();
        $lead = Lead::create(['name' => 'Acme Corp', 'email' => 'old@example.com']);

        $response = $this->putJson("/api/v1/leads/{$lead->id}", [
            'name' => 'Acme Corporation',
        ])->assertOk();

        $this->assertSame('Acme Corporation', $response->json('data.name'));
        $this->assertSame('old@example.com', $response->json('data.email'));
    }

    public function test_destroy_removes_the_record(): void
    {
        $this->apiActingAs();
        $lead = Lead::create(['name' => 'Acme Corp']);

        $this->deleteJson("/api/v1/leads/{$lead->id}")->assertNoContent();

        $this->assertDatabaseMissing('leads', ['id' => $lead->id]);
    }

    public function test_write_endpoints_reject_requests_without_a_token(): void
    {
        $this->makeUser();
        $lead = Lead::create(['name' => 'Acme Corp']);

        $this->postJson('/api/v1/leads', ['name' => 'x'])->assertUnauthorized();
        $this->putJson("/api/v1/leads/{$lead->id}", ['name' => 'x'])->assertUnauthorized();
        $this->deleteJson("/api/v1/leads/{$lead->id}")->assertUnauthorized();
    }
}
