<?php

namespace App\Http\Controllers;

use App\Models\DropdownList;
use App\Models\Settings\Settings;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DropDownListController extends Controller
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
    // request sent now validate and save
    $data = $request->validate([
      'name' => 'required|string',
      'field_key' => 'nullable|string',
      'values' => 'nullable|string',
    ]);
    dd($data);
  }

  /**
   * Display the specified resource.
   */
  public function show(String $dropDownList)
  {
    $dropdown = DropdownList::query()
      ->where('key', $dropDownList)
      ->first();
    $settingsItem = Settings::getItem('customisation', 'dropdowns');
    return Inertia::render('Settings/Dropdowns/Record', [
      'dropdown' => $dropdown,
      'item'  => $settingsItem

    ]);
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(DropDownList $dropDownList)
  {
    //
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, DropDownList $dropDownList)
  {
    //
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(DropDownList $dropDownList)
  {
    //
  }
}
