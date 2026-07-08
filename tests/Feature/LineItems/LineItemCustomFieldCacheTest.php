<?php

namespace Tests\Feature\LineItems;

use App\Models\Modules\LineItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\InteractsWithDashboardFixtures;
use Tests\TestCase;


class LineItemCustomFieldCacheTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithDashboardFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        $this->completeOnboarding();
    }

    public function test_repeated_attribute_access_only_queries_the_module_lookup_once(): void
    {
        LineItem::clearCustomFieldCache();
        $item = new LineItem();

        DB::enableQueryLog();
        for ($i = 0; $i < 10; $i++) {
            $item->name;
        }
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $moduleLookups = array_filter(
            $queries,
            fn ($q) => str_contains($q['query'], 'from `modules`')
        );

        $this->assertCount(1, $moduleLookups);
    }
}
