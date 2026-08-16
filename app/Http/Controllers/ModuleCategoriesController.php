<?php

namespace App\Http\Controllers;

use App\Models\ModuleCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ModuleCategoriesController extends Controller
{
    public function index(Request $request)
    {
        $categories = ModuleCategory::withCount('modules')
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('Settings/ModuleCategories/Index', [
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'label' => ['required', 'string', 'max:255'],
        ]);

        ModuleCategory::create([
            'label' => $validated['label'],
            'sort_order' => ModuleCategory::nextSortOrder(),
        ]);

        return back()->with('success', __('settings.module_categories.create_success'));
    }

    public function update(Request $request, ModuleCategory $moduleCategory)
    {
        $validated = $request->validate([
            'label' => ['required', 'string', 'max:255'],
        ]);

        $moduleCategory->update(['label' => $validated['label']]);

        return back()->with('success', __('settings.module_categories.update_success'));
    }

    public function destroy(ModuleCategory $moduleCategory)
    {
        if ($moduleCategory->modules()->exists()) {
            return back()->with('error', __('settings.module_categories.in_use_error'));
        }

        $moduleCategory->delete();

        return back()->with('success', __('settings.module_categories.delete_success'));
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['required', 'string', 'exists:module_categories,id'],
        ]);

        foreach ($validated['ids'] as $index => $id) {
            ModuleCategory::where('id', $id)->update(['sort_order' => $index + 1]);
        }

        return back();
    }
}
