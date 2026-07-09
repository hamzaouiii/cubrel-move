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

        
        
        
        
        Settings::clearCache();

        
        
        BaseModule::clearCustomFieldCache();
    }
}
