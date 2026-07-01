<?php

namespace Tests\Feature\Modules;

use App\Models\Module;
use App\Models\Modules\Account;
use App\Models\Modules\Contact;
use App\Models\Modules\ContactMessage;
use App\Models\Modules\Deal;
use App\Models\Modules\Invoice;
use App\Models\Modules\Lead;
use App\Models\Modules\Order;
use App\Models\Modules\Product;
use App\Models\Modules\Quote;
use App\Models\Modules\SupportCase;
use App\Models\Settings\SettingValue;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\InteractsWithDashboardFixtures;
use Tests\TestCase;

/**
 * CRUD coverage for the 10 generic CRM modules (all served by the shared
 * RecordController/ListController pair) plus Users, which has its own
 * dedicated UserController and required fields, so it doesn't fit the
 * data-provider loop and gets its own methods below.
 */
class ModuleCrudTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithDashboardFixtures;

    protected function setUp(): void
    {
        parent::setUp();

        $this->completeOnboarding();

        // ListController::getListData() falls back to this setting for
        // pagination when no perPage param is given; it's normally seeded by
        // SettingValuesSeeder, which RefreshDatabase-only tests don't run.
        SettingValue::create(['setting_item' => 'preferences', 'key' => 'list_view_limit', 'value' => 25]);
    }

    public static function moduleProvider(): array
    {
        return [
            'leads'     => ['leads', Lead::class, true],
            'accounts'  => ['accounts', Account::class, true],
            'contacts'  => ['contacts', Contact::class, true],
            'deals'     => ['deals', Deal::class, true],
            'quotes'    => ['quotes', Quote::class, true],
            'orders'    => ['orders', Order::class, true],
            'invoices'  => ['invoices', Invoice::class, true],
            'products'  => ['products', Product::class, true],
            'cases'     => ['cases', SupportCase::class, true],
            'inquiries' => ['inquiries', ContactMessage::class, false],
        ];
    }

    /**
     * Registers the module under test and returns an authenticated user.
     * Also ensures 'line_items' and 'products' module rows exist, since
     * RecordController::__invoke() unconditionally looks both up (to seed
     * line-item UI metadata) on every single record-show request, regardless
     * of which module is being viewed.
     */
    protected function setUpGenericModule(string $slug, string $modelClass, bool $hasOwner): User
    {
        $this->makeModule([
            'slug' => $slug,
            'name' => ucfirst($slug),
            'path' => "/{$slug}",
            'has_owner' => $hasOwner,
            'model_class' => $modelClass,
        ]);

        if (! Module::withoutGlobalScopes()->where('slug', 'line_items')->exists()) {
            $this->makeModule([
                'slug' => 'line_items', 'name' => 'Line Items', 'path' => '/line_items',
                'has_owner' => false, 'model_class' => \App\Models\Modules\LineItem::class,
            ]);
        }

        if (! Module::withoutGlobalScopes()->where('slug', 'products')->exists()) {
            $this->makeModule([
                'slug' => 'products', 'name' => 'Products', 'path' => '/products',
                'has_owner' => true, 'model_class' => Product::class,
            ]);
        }

        $user = $this->makeUser(['is_admin' => false, 'type' => 'sales_rep']);
        $this->actingAs($user);

        return $user;
    }

    #[DataProvider('moduleProvider')]
    public function test_index_lists_records(string $slug, string $modelClass, bool $hasOwner): void
    {
        $this->setUpGenericModule($slug, $modelClass, $hasOwner);
        $modelClass::create(['name' => 'Record One']);

        $response = $this->get("/{$slug}");

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Modules/List')
            ->has('items', 1)
        );
    }

    #[DataProvider('moduleProvider')]
    public function test_create_form_is_reachable(string $slug, string $modelClass, bool $hasOwner): void
    {
        $this->setUpGenericModule($slug, $modelClass, $hasOwner);

        $response = $this->get("/{$slug}/create");

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page->component('Modules/Create'));
    }

    #[DataProvider('moduleProvider')]
    public function test_store_creates_record(string $slug, string $modelClass, bool $hasOwner): void
    {
        $this->setUpGenericModule($slug, $modelClass, $hasOwner);

        $response = $this->post("/{$slug}", ['name' => 'New Record']);

        $response->assertRedirect();
        $this->assertDatabaseHas((new $modelClass)->getTable(), ['name' => 'New Record']);
    }

    #[DataProvider('moduleProvider')]
    public function test_show_displays_record(string $slug, string $modelClass, bool $hasOwner): void
    {
        $this->setUpGenericModule($slug, $modelClass, $hasOwner);
        $record = $modelClass::create(['name' => 'Viewable Record']);

        $response = $this->get("/{$slug}/{$record->id}");

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Modules/Record')
            ->where('record.name', 'Viewable Record')
        );
    }

    #[DataProvider('moduleProvider')]
    public function test_update_modifies_record(string $slug, string $modelClass, bool $hasOwner): void
    {
        $this->setUpGenericModule($slug, $modelClass, $hasOwner);
        $record = $modelClass::create(['name' => 'Original Name']);

        $response = $this->put("/{$slug}/{$record->id}", ['name' => 'Updated Name']);

        $response->assertRedirect();
        $this->assertDatabaseHas((new $modelClass)->getTable(), [
            'id' => $record->id,
            'name' => 'Updated Name',
        ]);
    }

    #[DataProvider('moduleProvider')]
    public function test_destroy_deletes_record(string $slug, string $modelClass, bool $hasOwner): void
    {
        $this->setUpGenericModule($slug, $modelClass, $hasOwner);
        $record = $modelClass::create(['name' => 'Doomed Record']);

        $response = $this->delete("/{$slug}/{$record->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing((new $modelClass)->getTable(), ['id' => $record->id]);
    }

    // ── Users ───────────────────────────────────────────────────────────
    // Served by UserController (dedicated validation, admin-only middleware),
    // not the generic RecordController. There's also no delete route for
    // users, so this covers Create/Read/Update only.

    protected function setUpUsersModule(): User
    {
        $this->makeModule(['slug' => 'users', 'name' => 'Users', 'path' => '/users', 'has_owner' => false]);

        $admin = $this->makeUser(['is_admin' => true, 'type' => 'admin']);
        $this->actingAs($admin);

        return $admin;
    }

    public function test_users_index_lists_records(): void
    {
        $this->setUpUsersModule();

        $response = $this->get('/users');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page->component('Users/List'));
    }

    public function test_users_create_form_is_reachable(): void
    {
        $this->setUpUsersModule();

        $response = $this->get('/users/create');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page->component('Users/Create'));
    }

    public function test_users_store_creates_user(): void
    {
        $this->setUpUsersModule();

        // first_name/last_name must be present (even empty) — the real
        // Users/Create.vue form always submits them, but UserController::store()
        // -> User::createFromAccountForm() reads $data['first_name'] directly with
        // no fallback, so a payload that omits the keys entirely (rather than
        // sending '') would 500 instead of failing validation.
        $response = $this->post('/users', [
            'username' => 'new.user',
            'first_name' => '',
            'last_name' => '',
            'is_admin' => false,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', ['username' => 'new.user']);
    }

    public function test_users_show_displays_user(): void
    {
        $this->setUpUsersModule();
        $target = $this->makeUser(['username' => 'viewable.user']);

        $response = $this->get("/users/{$target->id}");

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Users/Record')
            ->where('record.username', 'viewable.user')
        );
    }

    public function test_users_update_modifies_user(): void
    {
        $this->setUpUsersModule();
        $target = $this->makeUser(['username' => 'original.username']);

        $response = $this->put("/users/{$target->id}", [
            'username' => 'updated.username',
            'is_admin' => false,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', ['id' => $target->id, 'username' => 'updated.username']);
    }
}
