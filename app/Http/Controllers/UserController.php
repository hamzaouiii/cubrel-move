<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Handlers\Modules\UserModuleHandler;
use Illuminate\Http\Request;
use App\Models\Module;
use Inertia\Inertia;
use App\Support\Settings;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;


class UserController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    $moduleModel = Module::query()
      ->where('slug', 'users')
      ->where('is_active', true)
      ->firstOrFail();

    $handler = new UserModuleHandler();
    $props = $handler->getListData($moduleModel);

    $listLayout = config("module_layouts.users.list");
    $recorddropdownLists = $moduleModel->dropdownLists;
    $fields = $moduleModel->allFields();

    return Inertia::render('Users/List', array_merge([
      'module'     => $moduleModel,
      'title'      => $moduleModel->name,
      'listLayout' => $listLayout,
      'fields'     => $fields,
      'filters'    => request()->only(['search', 'perPage']),
      'dropdownLists' => $recorddropdownLists,

    ], $props));
  }

  /**
   * Show the form for creating a new resource.
   */

  public function create()
  {
    $module = 'users';
    $moduleModel = Module::query()
      ->where('slug', $module)
      ->where('is_active', true)
      ->firstOrFail();

    $recordLayout = config("module_layouts.users.record");

    $fields        = $moduleModel->allFields();
    $recorddropdownLists = $moduleModel->dropdownLists;

    return Inertia::render('Modules/Create', array_merge([
      'module'        => $moduleModel,
      'title'         => $moduleModel->name,
      'recordLayout'  => $recordLayout,
      'dropdownLists' => $recorddropdownLists,
      'fields'        => $fields,
    ]));
  }

  /**
   * Display the specified resource.
   */
  public function show(string $user)
  {
    $module = 'users';
    $moduleModel = Module::query()
      ->where('slug', $module)
      ->where('is_active', true)
      ->firstOrFail();

    $props = [];

    $handler = new UserModuleHandler();

    $props = $handler->getRecordData($module, $user, $moduleModel, request()->all());

    // $recordLayout  = $moduleModel->recordLayout();
    $recordLayout = config("module_layouts.users.record");

    $relatedLayout = $moduleModel->relatedLayout();
    $fields        = $moduleModel->allFields();

    return Inertia::render('Users/Record', array_merge([
      'module'         => $moduleModel,
      'title'          => $moduleModel->name,
      'overviewLayout' => $recordLayout,
      'relatedLayout'  => $relatedLayout,
      'fields'         => $fields,
    ], $props));
  }

  public function getUsersForLinking()
  {
    $limit = Settings::get('linking_panel_limit');

    return User::getRecordsForLinking($limit);
  }

  /**
   * Search users for record selector (owner field).
   * GET /users/search?q=&page=
   */
  public function search(Request $request): \Illuminate\Http\JsonResponse
  {
    $perPage    = Settings::get('linking_panel_limit');
    $search     = $request->string('q')->trim()->toString();
    $selectedId = $request->input('selected');

    $paginator = User::query()
      ->when($search, fn($q) => $q->where(function ($q) use ($search) {
        $q->where('name',  'like', "%{$search}%")
          ->orWhere('email', 'like', "%{$search}%");
      }))
      // If there is NO search query but we DO have a selected user, force them to the top
      ->when(empty($search) && $selectedId, function ($q) use ($selectedId) {
        $q->orderByRaw('CASE WHEN id = ? THEN 0 ELSE 1 END ASC', [$selectedId]);
      })
      ->orderBy('name')
      ->paginate($perPage, ['id', 'name', 'email']);

    return response()->json([
      'data'         => $paginator->items(),
      'current_page' => $paginator->currentPage(),
      'last_page'    => $paginator->lastPage(),
      'total'        => $paginator->total(),
    ]);
  }

  public function impersonate(User $user)
  {
    
      $currentUser = auth()->user();

      if (!$currentUser->isRoot()) {
          abort(403, 'Only root can impersonate users.');
      }

      if (!$user->canBeImpersonated()) {
          abort(403, 'Cannot impersonate this user.');
      }

      // store original user id
      Session::put('impersonator_id', $currentUser->id);

      // login as target user
      Auth::login($user);
      return redirect()->route('dashboard');
  }
  public function leaveImpersonation()
  {
      $impersonatorId = session('impersonator_id');

      if (!$impersonatorId) {
          abort(403);
      }

      $originalUser = User::findOrFail($impersonatorId);

      Auth::login($originalUser);
      session()->forget('impersonator_id');

      return redirect()->route('dashboard');
  }
}
