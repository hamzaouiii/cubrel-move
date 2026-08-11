<?php

namespace Tests;

use App\Models\BaseModule;
use App\Models\Module;
use App\Observers\AuditObserver;
use App\Services\Relationships\RelationshipService;
use App\Support\Settings;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();





        Settings::clearCache();



        BaseModule::clearCustomFieldCache();

        RelationshipService::clearCache();

        Module::clearFieldCache();

        AuditObserver::clearModuleCache();
    }
}
