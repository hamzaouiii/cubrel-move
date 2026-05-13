<?php

namespace App\Services\Users;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Illuminate\Support\Facades\Schema;

class OwnershipService
{
    /**
     * How long to cache the module table map (null = forever).
     * Bust this cache when modules are created/deleted.
     */
    protected const CACHE_TTL = null;
    protected const CACHE_KEY = 'ownership_service.module_table_map';


    /**
     * Get all records owned by a user across all modules.
     *
     * Returns a Collection keyed by module name, each value being
     * a Collection of stdObjects with at minimum: id, module, table_name.
     *
     * Example:
     *   $results->get('opportunities') // Collection of opportunity rows
     *
     * @param  string       $userId
     * @param  array|null   $onlyModules  Optionally restrict to specific module names
     * @param  array        $columns      Extra columns to select per record (beyond id)
     * @return Collection<string, Collection>
     */
    public function getRecordsByUser(
        string $userId,
        ?array $onlyModules = null,
        array $columns = []
    ): Collection {
        $modules = $this->getModuleTableMap();

        if ($onlyModules !== null) {
            $modules = $modules->filter(
                fn($m) => in_array($m->name, $onlyModules, strict: true)
            );
        }

        if ($modules->isEmpty()) {
            return collect();
        }
        $moduleCount = $modules->count();
        $bindings = array_fill(0, $moduleCount, $userId);
        $sql = $this->buildUnionQuery($modules, $userId, $columns);

        $rows = collect(DB::select($sql, $bindings));

        return $rows->groupBy('module');
    }

    /**
     * Get a flat count of owned records per module.
     *
     * Returns: Collection<string, int>  e.g. ['opportunities' => 12, 'leads' => 4]
     *
     * @param  string      $userId
     * @param  array|null  $onlyModules
     * @return Collection<string, int>
     */
    public function getCountsByUser(string $userId, ?array $onlyModules = null): Collection
    {
        $modules = $this->getModuleTableMap();

        if ($onlyModules !== null) {
            $modules = $modules->filter(
                fn($m) => in_array($m->name, $onlyModules, strict: true)
            );
        }

        if ($modules->isEmpty()) {
            return collect();
        }

        $sql = $this->buildCountUnionQuery($modules);
$moduleCount = $modules->count();
$bindings = array_fill(0, $moduleCount, $userId);
        $rows = DB::select($sql, $bindings);

        return collect($rows)->pluck('total', 'module')->map(fn($v) => (int) $v);
    }

    /**
     * Get all records across all users, grouped by user then module.
     * Useful for admin/reporting views.
     *
     * @param  array|null  $onlyModules
     * @return Collection<string, Collection<string, Collection>>
     */
    public function getAllGroupedByUser(?array $onlyModules = null): Collection
    {
        $modules = $this->getModuleTableMap();

        if ($onlyModules !== null) {
            $modules = $modules->filter(
                fn($m) => in_array($m->name, $onlyModules, strict: true)
            );
        }

        if ($modules->isEmpty()) {
            return collect();
        }

        $sql = $this->buildUnionQuery($modules, null, []);

        // No binding needed — no WHERE clause in this variant
        $rows = collect(DB::select($sql));

        return $rows->groupBy('owner_id')->map(fn($group) => $group->groupBy('module'));
    }

    /**
     * Bust the module map cache — call this from a Module observer
     * whenever a module is created, updated, or deleted.
     */
    public function flushModuleCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    // -------------------------------------------------------------------------
    // Internals
    // -------------------------------------------------------------------------

    /**
     * Load and cache the module → table_name map, excluding system modules.
     *
     * @return Collection<stdClass>  each with ->name and ->table_name
     */
      protected function getModuleTableMap(): Collection
      {
          $loader = fn() => DB::table('modules')
              ->select('name', 'table_name', 'label', 'slug')
              ->where('has_owner', true)
              ->get();

          return self::CACHE_TTL === null
              ? Cache::rememberForever(self::CACHE_KEY, $loader)
              : Cache::remember(self::CACHE_KEY, self::CACHE_TTL, $loader);
      }
      protected function tableHasOwnerColumn(string $table): bool
      {
          return Cache::rememberForever("ownership_service.has_owner_col.{$table}", fn() =>
              Schema::hasColumn($table, 'owner_id')
          );
      }

    /**
     * Build a UNION ALL query selecting records for a specific user.
     * Uses a single positional binding (?) for owner_id, repeated per module.
     *
     * When $userId is null, the WHERE clause is omitted (for getAllGroupedByUser).
     */
    protected function buildUnionQuery(
        Collection $modules,
        ?string $userId,
        array $extraColumns
    ): string {
        $this->validateTableNames($modules);

        $selectExtras = empty($extraColumns)
            ? ''
            : ', ' . implode(', ', array_map(fn($c) => "`{$c}`", $extraColumns));

        $parts = $modules->filter(
          fn($m) => $this->tableHasOwnerColumn($m->table_name)
          )->map(function ($module) use ($userId, $selectExtras) {
            $table  = $module->table_name;
            $name   = addslashes($module->name);
            $label   = addslashes($module->label);
            $slug   = addslashes($module->slug);
            $where  = $userId !== null ? 'WHERE owner_id = ?' : '';

            return "SELECT id, owner_id, '{$name}' AS module,  '{$label}' AS label, '{$slug}' AS slug, '{$table}' AS table_name{$selectExtras}
                    FROM `{$table}` {$where}";
        });

        return $parts->implode("\nUNION ALL\n");
    }

    /**
     * Build a UNION ALL count query, then wrap in a subquery to sum per module.
     */
    protected function buildCountUnionQuery(Collection $modules): string
    {
        $this->validateTableNames($modules);

        $parts = $modules->map(function ($module) {
            $table = $module->table_name;
            $name  = addslashes($module->name);

            return "SELECT '{$name}' AS module, COUNT(*) AS total
                    FROM `{$table}`
                    WHERE owner_id = ?";
        });

        return $parts->implode("\nUNION ALL\n");
    }

    /**
     * Whitelist-validate all table names against the modules table.
     * Throws if any name from our own DB is somehow malformed (defence in depth).
     */
    protected function validateTableNames(Collection $modules): void
    {
        foreach ($modules as $module) {
            if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $module->table_name)) {
                throw new RuntimeException(
                    "OwnershipService: unsafe table name detected: [{$module->table_name}]"
                );
            }
        }
    }
}