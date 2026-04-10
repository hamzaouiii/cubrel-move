<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Scopes\AdminOnlyModuleScope;
use App\Models\Module;
use Illuminate\Support\Collection;
use App\Models\Field;
use Illuminate\Http\Request;

class UserProfileController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    $user = Auth::user();
    $recordLayout = config("module_layouts.users.record");

    $moduleModel = Module::withoutGlobalScope(AdminOnlyModuleScope::class)
      ->where('slug', 'users')
      ->where('is_active', true)
      ->firstOrFail();
    $fields        = $this->getFields($moduleModel->id);

    return Inertia::render('Profile/Index', array_merge([
      'layout' => $recordLayout,
      'record'    => $user,
      'fields'         => $fields,
      'module'         => $moduleModel,

    ]));
  }

  public function update(Request $request)
  {
    $user = Auth::user();
    $validated = $request->validate([
      'username'   => ['required', 'string', 'max:64'],
      'first_name'    => ['nullable', 'string'],
      'last_name'    => ['nullable', 'string'],
      'phone'    => ['nullable', 'string'],
      'mobile'    => ['nullable', 'string'],
      'email'    => ['nullable', 'email', 'unique:users,email,' . $user->id],
      'title'    => ['nullable', 'string'],
      'description'    => ['nullable', 'string'],
    ]);


    $user->name = $validated['first_name'] . " " . $validated['last_name'];
    $user->username = $validated['username'];
    $user->first_name = $validated['first_name'];
    $user->last_name  = $validated['last_name'];
    $user->email      = $validated['email'];
    $user->phone      = $validated['phone'];
    $user->mobile      = $validated['mobile'];
    $user->title      = $validated['title'];
    $user->description      = $validated['description'];

    $user->save();

    return back()->with('success', 'Record updated successfully.');
  }
  protected function getFields(string $id): Collection
  {
    return Field::query()
      ->where(function ($query) use ($id) {
        $query->where('module_id', $id)
          ->orWhere('is_global', true);
      })
      ->select([
        'id',
        'module_id',
        'dropdown_list_id',
        'name',
        'type',
        'key',
        'readonly',
        'sortable',
        'searchable',
        'label',
        'required',
        'is_draft'
      ])
      ->with('dropdown_list')
      ->get();
  }
}
