<?php

namespace Tests;

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
    }
}
