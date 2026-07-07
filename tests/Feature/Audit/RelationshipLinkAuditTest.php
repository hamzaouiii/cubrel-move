<?php

namespace Tests\Feature\Audit;

use App\Models\AuditLog;
use App\Models\Modules\Account;
use App\Models\Modules\Contact;
use App\Models\Relationship;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithDashboardFixtures;
use Tests\TestCase;

/**
 * Covers RelationshipService::link()/unlink() logging an entry on BOTH sides
 * of a relationship, so either record's own history shows the connection
 * regardless of which side the action was performed from. See
 * docs/audit-trail-implementation.md §4.3.
 */
class RelationshipLinkAuditTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithDashboardFixtures;

    protected Account $account;
    protected Contact $contact;

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
        $this->makeModule([
            'slug' => 'contacts',
            'name' => 'Contacts',
            'path' => '/contacts',
            'model_class' => Contact::class,
            'table_name' => 'contacts',
        ]);

        Relationship::create([
            'name' => 'accounts_contacts',
            'label' => 'Contacts',
            'left_module' => 'accounts',
            'right_module' => 'contacts',
            'type' => 'many-to-many',
            'is_system' => true,
        ]);

        $this->actingAs($this->makeUser(['is_admin' => true]));

        $this->account = Account::create(['name' => 'Voß Brückner AG']);
        $this->contact = Contact::create(['name' => 'Eladio Koelpin']);
    }

    public function test_linking_two_records_logs_an_entry_on_both_sides(): void
    {
        AuditLog::query()->delete();

        $this->post(
            "/modules/accounts/{$this->account->id}/relationships/accounts_contacts",
            ['related_ids' => [(string) $this->contact->id]],
        )->assertSuccessful();

        $this->assertSame(2, AuditLog::where('action', 'linked')->count());

        $accountSide = AuditLog::where('action', 'linked')
            ->where('module_slug', 'accounts')
            ->where('record_id', $this->account->id)
            ->firstOrFail();
        $accountChanges = $accountSide->toDisplayArray()['changes'];
        $this->assertSame('accounts_contacts', $accountChanges['relationship']);
        $this->assertSame('contacts', $accountChanges['related_module']);
        $this->assertSame((string) $this->contact->id, (string) $accountChanges['related_id']);
        $this->assertSame('Eladio Koelpin', $accountChanges['related_label']);

        $contactSide = AuditLog::where('action', 'linked')
            ->where('module_slug', 'contacts')
            ->where('record_id', $this->contact->id)
            ->firstOrFail();
        $contactChanges = $contactSide->toDisplayArray()['changes'];
        $this->assertSame('accounts', $contactChanges['related_module']);
        $this->assertSame((string) $this->account->id, (string) $contactChanges['related_id']);
        $this->assertSame('Voß Brückner AG', $contactChanges['related_label']);
    }

    public function test_unlinking_logs_an_entry_on_both_sides(): void
    {
        $this->account->link('accounts_contacts', $this->contact->id);
        AuditLog::query()->delete();

        $this->delete(
            "/modules/accounts/{$this->account->id}/relationships/accounts_contacts/{$this->contact->id}",
        )->assertSuccessful();

        $this->assertSame(2, AuditLog::where('action', 'unlinked')->count());

        $accountSide = AuditLog::where('action', 'unlinked')
            ->where('module_slug', 'accounts')
            ->where('record_id', $this->account->id)
            ->firstOrFail();
        $this->assertSame('Eladio Koelpin', $accountSide->toDisplayArray()['changes']['related_label']);

        $contactSide = AuditLog::where('action', 'unlinked')
            ->where('module_slug', 'contacts')
            ->where('record_id', $this->contact->id)
            ->firstOrFail();
        $this->assertSame('Voß Brückner AG', $contactSide->toDisplayArray()['changes']['related_label']);
    }

    /**
     * Regression test: demo-data seeding (RelationshipPopulationSeeder)
     * calls RelationshipService::link() directly, with no authenticated
     * user in a console context. Model-event-driven auditing already skips
     * this naturally when a seeder wraps creation in
     * Model::withoutEvents() (see DatabaseSeeder's WithoutModelEvents
     * trait), but link()/unlink() never went through model events at all —
     * before this fix, seeded links were logged anyway, attributed to no
     * one ("Unknown" in the UI). AuditService::log() now uniformly no-ops
     * without an authenticated actor, for every caller.
     */
    public function test_linking_without_an_authenticated_actor_logs_nothing(): void
    {
        auth()->logout();
        AuditLog::query()->delete();

        $this->account->link('accounts_contacts', $this->contact->id);

        $this->assertSame(0, AuditLog::count());
    }
}
