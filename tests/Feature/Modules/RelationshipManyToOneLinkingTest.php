<?php

namespace Tests\Feature\Modules;

use App\Models\Modules\Account;
use App\Models\Modules\Deal;
use App\Models\Relationship;
use App\Models\RelationshipLink;
use App\Services\Relationships\RelationshipService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithDashboardFixtures;
use Tests\TestCase;

class RelationshipManyToOneLinkingTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithDashboardFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        $this->completeOnboarding();

        $this->makeModule([
            'slug' => 'deals',
            'name' => 'Deals',
            'path' => '/deals',
            'model_class' => Deal::class,
            'table_name' => 'deals',
        ]);
        $this->makeModule([
            'slug' => 'accounts',
            'name' => 'Accounts',
            'path' => '/accounts',
            'model_class' => Account::class,
            'table_name' => 'accounts',
        ]);

        $this->actingAs($this->makeUser(['is_admin' => true]));
    }

    

    protected function createManyToOneRelationship(): Relationship
    {
        $dealsModule = \App\Models\Module::where('slug', 'deals')->firstOrFail();

        $this->post("/settings/modules/{$dealsModule->id}/relationships", [
            'name' => 'deals_accounts',
            'label' => 'Account',
            'right_module' => 'accounts',
            'type' => 'many-to-one',
        ])->assertRedirect();

        return Relationship::where('name', 'deals_accounts')->firstOrFail();
    }

    public function test_linking_creates_a_relationship_link_row(): void
    {
        $this->createManyToOneRelationship();

        $deal = Deal::create(['name' => 'Big Deal']);
        $account = Account::create(['name' => 'Acme Inc']);

        $this->post("/modules/deals/{$deal->id}/relationships/deals_accounts", [
            'related_ids' => [(string) $account->id],
        ])->assertSuccessful();

        $link = RelationshipLink::query()->firstOrFail();
        
        $this->assertSame((string) $account->id, (string) $link->left_id);
        $this->assertSame((string) $deal->id, (string) $link->right_id);
    }

    public function test_unlinking_removes_the_relationship_link_row(): void
    {
        $this->createManyToOneRelationship();

        $deal = Deal::create(['name' => 'Big Deal']);
        $account = Account::create(['name' => 'Acme Inc']);

        $this->post("/modules/deals/{$deal->id}/relationships/deals_accounts", [
            'related_ids' => [(string) $account->id],
        ])->assertSuccessful();

        $this->assertSame(1, RelationshipLink::query()->count());

        $this->delete("/modules/deals/{$deal->id}/relationships/deals_accounts/{$account->id}")
            ->assertSuccessful();

        $this->assertSame(0, RelationshipLink::query()->count());
    }

    

    public function test_relinking_a_deal_to_a_new_account_reparents_instead_of_duplicating(): void
    {
        $this->createManyToOneRelationship();

        $deal = Deal::create(['name' => 'Big Deal']);
        $accountA = Account::create(['name' => 'Account A']);
        $accountB = Account::create(['name' => 'Account B']);

        $this->post("/modules/deals/{$deal->id}/relationships/deals_accounts", [
            'related_ids' => [(string) $accountA->id],
        ])->assertSuccessful();

        $this->post("/modules/deals/{$deal->id}/relationships/deals_accounts", [
            'related_ids' => [(string) $accountB->id],
        ])->assertSuccessful();

        $this->assertSame(1, RelationshipLink::query()->count());

        $link = RelationshipLink::query()->firstOrFail();
        $this->assertSame((string) $accountB->id, (string) $link->left_id);
    }

    

    public function test_role_resolves_correctly_on_both_sides(): void
    {
        $relationship = $this->createManyToOneRelationship();

        $dealSide = RelationshipService::getWithSide($relationship->fresh(), 'deals');
        $accountSide = RelationshipService::getWithSide($relationship->fresh(), 'accounts');

        $this->assertSame('child', $dealSide->role);
        $this->assertSame('parent', $accountSide->role);
    }
}
