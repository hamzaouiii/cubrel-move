<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Module;
use App\Models\Layout;
use Inertia\Inertia;
use App\Models\Settings\SettingItem;

class LayoutListManagerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index( Request $request)
    {
          $modules = Module::query()
        ->with([
            'layouts' => function ($q) {
                $q->orderBy('type')->orderBy('name');
            },
        ])
        ->orderBy('id')
        ->get();
      $item = SettingItem::where('path', 'like', '%' . $request->path())->first();

 
      return Inertia::render('Settings/Layouts/Edit', [
        'item'     => $item,
        'setting_modules' => $modules,
        'type'  => 'list'
      ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
