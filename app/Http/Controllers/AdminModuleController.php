<?php

namespace App\Http\Controllers;

use App\Models\Module;
use Inertia\Inertia;

class AdminModuleController extends Controller
{
    public function __invoke(string $module)
    {
        $moduleModel = Module::query()
            ->where('slug', $module)
            ->where('is_active', true)
            ->firstOrFail();

        return Inertia::render('Admin/Modules/List', [
            'module' => $moduleModel->slug,
            'title'  => $moduleModel->name,
        ]);
    }
}
