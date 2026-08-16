<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\ModuleCategory;
use App\Models\Settings\SettingValue;
use App\Support\Settings;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SidebarManagerController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('Settings/Sidebar/Index');
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'sort_by_category' => ['required', 'boolean'],
            'sections' => ['required', 'array'],
            'sections.*.category_id' => ['nullable', 'string', 'exists:module_categories,id'],
            'sections.*.modules' => ['required', 'array'],
            'sections.*.modules.*' => ['required', 'string', 'exists:modules,slug'],
        ]);

        SettingValue::updateOrCreate(
            ['key' => 'sidebar_sort_by_category'],
            [
                'setting_item' => 'sidebar',
                'value' => $validated['sort_by_category'] ? 1 : 0,
                'type' => 'bool',
                'autoload' => 1,
            ]
        );
        Settings::clearCache();

        $moduleOrder = 1;
        foreach ($validated['sections'] as $categoryIndex => $section) {
            if ($validated['sort_by_category'] && ! empty($section['category_id'])) {
                ModuleCategory::where('id', $section['category_id'])
                    ->update(['sort_order' => $categoryIndex + 1]);
            }

            foreach ($section['modules'] as $slug) {
                Module::where('slug', $slug)->update(['sort_order' => $moduleOrder]);
                $moduleOrder++;
            }
        }

        return back()->with('success', __('settings.sidebar.save_success'));
    }
}
