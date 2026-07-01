<?php

namespace Tests;

use App\Models\BaseModule;
use App\Support\Settings;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Settings::$cache is a static array that outlives RefreshDatabase's
        // per-test transaction rollback, so a value written by one test can
        // leak into the next test in the same process. Clear it here so every
        // test starts by reading the (freshly migrated) database.
        Settings::clearCache();

        // Same leak, different cache: HasCustomFields memoizes module/custom-field
        // lookups in static arrays keyed by table name / module_id.
        BaseModule::clearCustomFieldCache();
    }
}
