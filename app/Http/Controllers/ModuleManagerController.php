<?php


namespace App\Http\Controllers;

use App\Models\Module;
use Inertia\Inertia;

class ModuleManagerController extends Controller
{
    public function index()
    {
        $modules = Module::query()
            ->with([
                // needs relations on Module model:
                // public function layouts() { return $this->hasMany(Layout::class); }
                'layouts' => function ($q) {
                    $q->orderBy('type')->orderBy('name');
                },
               
            ])
            ->orderBy('id')
            ->get();

        return Inertia::render('Modules/Manager', [
            'modules' => $modules,
        ]);
    }
}
