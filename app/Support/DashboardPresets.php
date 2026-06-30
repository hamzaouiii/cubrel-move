<?php

namespace App\Support;

use Illuminate\Support\Str;

class DashboardPresets
{
    /**
     * Resolve a layout array for the given user type.
     * Reads from config/dashboard_presets.php and stamps fresh instanceIds onto
     * every instance object.  String items ('my-records') pass through unchanged.
     */
    public static function layout(string $userType): array
    {
        $presets = config('dashboard_presets', []);
        $items   = $presets[$userType] ?? $presets['read_only'] ?? [];

        return array_values(array_filter(array_map(
            fn ($item) => is_string($item)
                ? $item
                : array_merge(['instanceId' => (string) Str::uuid()], $item),
            $items
        )));
    }

    /**
     * Determine which preset key applies to a given user.
     * Falls back to 'admin' for any is_admin user whose type isn't explicitly
     * defined in the config, and to 'read_only' for everyone else.
     */
    public static function presetType(object $user): string
    {
        $type    = $user->type ?? 'read_only';
        $presets = config('dashboard_presets', []);

        if (isset($presets[$type])) {
            return $type;
        }

        // Any admin-flagged user with no matching type gets the admin preset.
        if ($user->isAdmin()) {
            return 'admin';
        }

        return 'read_only';
    }
}
