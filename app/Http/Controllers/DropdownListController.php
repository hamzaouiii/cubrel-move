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
  public function show(DropDownList $dropDownList)
  {
    //
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
