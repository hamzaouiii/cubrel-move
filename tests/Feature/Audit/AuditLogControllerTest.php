<?php

namespace Tests\Feature\Audit;

use App\Models\AuditLog;
use App\Models\Modules\Account;
use App\Models\Modules\Contact;
use App\Models\Settings\SettingValue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\InteractsWithDashboardFixtures;
use Tests\TestCase;

/**
 * Covers AuditLogController — the admin-gated global Audit Trail page. See
 * docs/audit-trail-implementation.md §6.1, and §5.4 for the N+1 /
 * field-collapse regression this specifically guards against.
 */
class AuditLogControllerTest extends TestCase
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

        $this->get('/settings/audit-trail')->assertForbidden();
    }

    public function test_admin_can_view_and_filter_the_audit_trail(): void
    {
        $accountsModule = $this->makeModule([
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

        $admin = $this->makeUser(['is_admin' => true]);
        $this->actingAs($admin);

        $account = Account::create(['name' => 'Acme']);
        $contact = Contact::create(['name' => 'Jane Doe']);
        AuditLog::query()->delete();

        $account->update(['name' => 'Acme Updated']);
        $contact->update(['name' => 'Jane Updated']);

        $response = $this->get('/settings/audit-trail?module=accounts');

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Settings/AuditTrail/Index')
            ->has('logs', 1)
            ->where('logs.0.module_slug', 'accounts')
        );
    }

    /**
     * Regression test for docs/audit-trail-implementation.md §5.4: building
     * fields_by_module via Field::query()->get() without selecting 'id'
     * collapsed every module's field list into a single entry, because
     * Eloquent Collection::merge() dedupes by primary key.
     */
    public function test_fields_by_module_resolves_distinct_labels_per_module(): void
    {
        $accountsModule = $this->makeModule([
            'slug' => 'accounts',
            'name' => 'Accounts',
            'path' => '/accounts',
            'model_class' => Account::class,
            'table_name' => 'accounts',
        ]);
        $contactsModule = $this->makeModule([
            'slug' => 'contacts',
            'name' => 'Contacts',
            'path' => '/contacts',
            'model_class' => Contact::class,
            'table_name' => 'contacts',
        ]);

        $this->makeField($accountsModule, [
            'name' => 'email',
            'key' => 'accounts.email',
            'label' => 'modules.accounts.fields.email',
        ]);
        $this->makeField($contactsModule, [
            'name' => 'email',
            'key' => 'contacts.email',
            'label' => 'modules.contacts.fields.email',
        ]);

        $this->actingAs($this->makeUser(['is_admin' => true]));

        $response = $this->get('/settings/audit-trail');

        $response->assertInertia(fn (Assert $page) => $page
            ->has('fields_by_module.accounts', 1)
            ->has('fields_by_module.contacts', 1)
            ->where('fields_by_module.accounts.0.name', 'email')
            ->where('fields_by_module.accounts.0.label', 'modules.accounts.fields.email')
            ->where('fields_by_module.contacts.0.name', 'email')
            ->where('fields_by_module.contacts.0.label', 'modules.contacts.fields.email')
        );
    }

    /**
     * fields_by_module needs to carry everything HistoryModal.vue relies on
     * (type/related_module/dropdown_list), not just {name, label} — a row
     * click on the global list opens that same modal, reusing its full
     * field-aware rendering (dropdown labels, record-type labels, date
     * formatting) instead of just showing which field names changed.
     */
    public function test_fields_by_module_carries_history_modal_metadata(): void
    {
        $accountsModule = $this->makeModule([
            'slug' => 'accounts',
            'name' => 'Accounts',
            'path' => '/accounts',
            'model_class' => Account::class,
            'table_name' => 'accounts',
        ]);

        $this->makeField($accountsModule, [
            'name' => 'owner_id',
            'key' => 'accounts.owner_id',
            'type' => 'record',
            'related_module' => 'users',
        ]);

        $this->actingAs($this->makeUser(['is_admin' => true]));

        $response = $this->get('/settings/audit-trail');

        $response->assertInertia(fn (Assert $page) => $page
            ->where('fields_by_module.accounts.0.type', 'record')
            ->where('fields_by_module.accounts.0.related_module', 'users')
        );
    }
}
