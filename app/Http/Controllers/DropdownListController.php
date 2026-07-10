<?php

namespace App\Http\Controllers;

use App\Models\DropdownList;
use App\Models\Settings\Settings;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DropdownListController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    $settingsItem = Settings::getItem('customisation', 'dropdowns');
    $dropdowns = DropdownList::all();
    return Inertia::render('Settings/Dropdowns/List', [
      'list' => $dropdowns,
      'item'  => $settingsItem
    ]);
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create()
  {

    $settingsItem = Settings::getItem('customisation', 'dropdowns');
    return Inertia::render('Settings/Dropdowns/Create', [
      'item'  => $settingsItem

    ]);
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    $data = $request->validate([
      'key' => 'required|string',
      'values' => 'required',
      'is_draft' => 'required',
      'is_status' => 'sometimes|boolean',
    ]);
    $dd = DropdownList::create($data);
    return redirect()
      ->route('settings.dropdowns.show', $dd->id)
      ->with('success', __('layouts.layout_update_success'));
  }

  /**
   * Display the specified resource.
   */
  public function show(String $dropdown_list_id)
  {
    $dropdown = DropdownList::find($dropdown_list_id);

    $settingsItem = Settings::getItem('customisation', 'dropdowns');
    return Inertia::render('Settings/Dropdowns/Record', [
      'dropdown' => $dropdown,
      'item'  => $settingsItem

    ]);
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(DropdownList $dropDownList)
  {
    //
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, string $dropdown_list_id)
  {

    $dropdown = DropdownList::findOrFail($dropdown_list_id);

    $data = $request->validate([
      'values' => 'required',
      'is_status' => 'sometimes|boolean',
    ]);

    $dropdown->update($data);

    return back()->with('success', __('settings.dropdown.update_success'));
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(DropdownList $dropDownList)
  {
    //
  }

  public function api(Request $request)
  {
    $query = DropdownList::query();

    if ($search = $request->input('q')) {
      $query->where('key', 'like', '%' . $search . '%');
    }
    $list = $query->get();

    return response()->json([
      'list' => $list
    ]);
  }

  public function storeAndAttach(Request $request)
  {
    $data = $request->validate([
      'key' => 'required|string',
      'values' => 'required',
      'is_draft' => 'required',
      'is_status' => 'sometimes|boolean',
    ]);
    $dropdown = DropdownList::create($data);
    return response()->json($dropdown);
  }
}
