<?php

namespace App\Http\Controllers;

use App\Models\Settings\SettingValue;
use App\Support\Settings;
use Database\Seeders\DashboardPresetSeeder;
use Database\Seeders\DevSeeder;
use Database\Seeders\LineItemsSeeder;
use Database\Seeders\OwnerAssignmentSeeder;
use Database\Seeders\RelationshipPopulationSeeder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class OnboardingController extends Controller
{
    protected const DEMO_SEEDERS = [
        DevSeeder::class,
        LineItemsSeeder::class,
        RelationshipPopulationSeeder::class,
        OwnerAssignmentSeeder::class,
        DashboardPresetSeeder::class,
    ];

    public function show(): InertiaResponse|RedirectResponse
    {
        if (Settings::bool('onboarding_completed')) {
            return redirect()->route('dashboard');
        }

        return Inertia::render('Onboarding', [
            'steps' => ['organisation', 'demo-data', 'invite'],
        ]);
    }

    public function seedDemoData(Request $request): RedirectResponse
    {
        $data = $request->validate(['populate' => ['required', 'boolean']]);

        if ($data['populate']) {
            // Seeding all four sets of demo data in one request comfortably
            $this->ensureMinimumMemoryLimit('512M');
            try {
                foreach (self::DEMO_SEEDERS as $seeder) {
                    Artisan::call('db:seed', ['--class' => $seeder, '--force' => true]);
                }
            } catch (\Throwable $e) {
                Log::error('Onboarding demo data seeding failed: '.$e->getMessage());

                return redirect()->back()->with('error', __('globals.onboarding.demo_data_failed'));
            }
        }

        return redirect()->back();
    }

    private function ensureMinimumMemoryLimit(string $target): void
    {
        $current = ini_get('memory_limit');

        if ($current === '-1') {
            return;
        }

        if ($this->memoryLimitToBytes($current) < $this->memoryLimitToBytes($target)) {
            ini_set('memory_limit', $target);
        }
    }

    private function memoryLimitToBytes(string $value): int
    {
        $value = trim($value);
        $number = (int) $value;

        return match (strtolower(substr($value, -1))) {
            'g' => $number * 1024 * 1024 * 1024,
            'm' => $number * 1024 * 1024,
            'k' => $number * 1024,
            default => $number,
        };
    }

    public function finish(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'destination' => ['nullable', 'in:dashboard,users'],
        ]);

        SettingValue::updateOrCreate(
            ['setting_item' => 'system', 'key' => 'onboarding_completed'],
            ['value' => '1']
        );
        Settings::clearCache();

        return redirect()->route(($data['destination'] ?? 'dashboard') === 'users' ? 'users.index' : 'dashboard');
    }
}
