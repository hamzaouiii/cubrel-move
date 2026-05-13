<?php

namespace App\Observers;

use App\Models\Module;
use App\Services\Users\OwnershipService;

class ModuleObserver
{
public function saved(Module $module): void
{
    app(OwnershipService::class)->flushModuleCache();
}

public function deleted(Module $module): void
{
    app(OwnershipService::class)->flushModuleCache();
}
}
