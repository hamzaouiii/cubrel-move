<?php

namespace Tests\Concerns;

use App\Models\Modules\Deal;
use App\Models\Modules\Lead;
use App\Models\Modules\Order;
use App\Models\Modules\Task;
use App\Models\Module;
use App\Models\Relationship;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

trait InteractsWithApiFixtures
{
    use InteractsWithDashboardFixtures;

    /**
     * Authenticates the current test as a token with the given abilities,
     * the same way a partner's Bearer token would resolve via auth:sanctum.
     */
    protected function apiActingAs(array $abilities = ['*']): User
    {
        $user = $this->makeUser();

        Sanctum::actingAs($user, $abilities);

        return $user;
    }

    protected function makeLeadsModule(array $overrides = []): Module
    {
        return $this->makeModule(array_merge([
            'slug' => 'leads',
            'name' => 'Leads',
            'path' => '/leads',
            'model_class' => Lead::class,
            'table_name' => 'leads',
            'has_activity' => true,
        ], $overrides));
    }

    protected function makeTasksModule(array $overrides = []): Module
    {
        return $this->makeModule(array_merge([
            'slug' => 'tasks',
            'name' => 'Tasks',
            'path' => '/tasks',
            'model_class' => Task::class,
            'table_name' => 'tasks',
            'is_activity' => true,
        ], $overrides));
    }

    protected function makeDealsModule(array $overrides = []): Module
    {
        return $this->makeModule(array_merge([
            'slug' => 'deals',
            'name' => 'Deals',
            'path' => '/deals',
            'model_class' => Deal::class,
            'table_name' => 'deals',
        ], $overrides));
    }

    protected function makeOrdersModule(array $overrides = []): Module
    {
        return $this->makeModule(array_merge([
            'slug' => 'orders',
            'name' => 'Orders',
            'path' => '/orders',
            'model_class' => Order::class,
            'table_name' => 'orders',
        ], $overrides));
    }

    protected function makeRelationship(string $name, string $left, string $right, string $type = 'many-to-many'): Relationship
    {
        return Relationship::create([
            'name' => $name,
            'label' => $name,
            'left_module' => $left,
            'right_module' => $right,
            'type' => $type,
            'is_system' => true,
        ]);
    }
}
