<?php

namespace App\Http\Controllers;

use App\Models\Settings\SettingValue;
use App\Support\Settings;
use Database\Seeders\DashboardPresetSeeder;
use Database\Seeders\DevSeeder;
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
            try {
                // Seeding all four sets of demo data in one request comfortably
                // exceeds the default 128M CLI/web memory_limit and dies with an
                // uncatchable fatal error (blank page, nothing in the log). Only
                // this request gets the bump — not a global php.ini change.
                ini_set('memory_limit', '512M');

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
