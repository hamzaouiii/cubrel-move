<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ImpersonationSession;
use App\Handlers\Modules\UserModuleHandler;
use App\Notifications\SetPasswordNotification;
use Illuminate\Http\Request;
use App\Models\Module;
use Inertia\Inertia;
use App\Support\Settings;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;


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
            $params = request()->all();
        $params['perPage'] = $params['perPage'] ?? Settings::getPersonal('list_view_limit');
    $props = $handler->getListData($moduleModel, $params);

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

    return Inertia::render('Users/Create', array_merge([
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

  /**
   * Store a newly created user record (admin "create user" page).
   * There's no password field on this form — real onboarding goes through
   * the invite flow, so we just seed a random password here to satisfy the
   * NOT NULL column; the admin fields (avatar/type/status/is_admin) go
   * through forceFill like User::createFromAccountForm does.
   */
  public function store(Request $request)
  {
    $validated = $request->validate([
      'username'    => ['required', 'string', 'max:64', 'unique:users,username'],
      'first_name'  => ['nullable', 'string'],
      'last_name'   => ['nullable', 'string'],
      'email'       => ['nullable', 'email', 'unique:users,email'],
      'phone'       => ['nullable', 'string'],
      'mobile'      => ['nullable', 'string'],
      'title'       => ['nullable', 'string'],
      'description' => ['nullable', 'string'],
      'avatar'      => ['nullable', 'string'],
      'type'        => ['nullable', 'string'],
      'status'      => ['nullable', 'string'],
      'is_admin'    => ['required'],
    ]);

    $user = User::createFromAccountForm(
      array_merge($validated, ['password' => Str::random(32)]),
      collect($validated)->only(['avatar', 'phone', 'mobile', 'title', 'description', 'type', 'status', 'is_admin'])->all()
    );

    return redirect("/users/{$user->id}")->with('success', 'Record created successfully.');
  }

  public function update(Request $request, string $user_id)
  {
    $user = User::findOrFail($user_id);

    $validated = $request->validate([
      'username'    => ['required', 'string', 'max:64', 'unique:users,username,' . $user->id],
      'first_name'  => ['nullable', 'string'],
      'last_name'   => ['nullable', 'string'],
      'email'       => ['nullable', 'email', 'unique:users,email,' . $user->id],
      'phone'       => ['nullable', 'string'],
      'mobile'      => ['nullable', 'string'],
      'title'       => ['nullable', 'string'],
      'description' => ['nullable', 'string'],
      'avatar'      => ['nullable', 'string'],
      'type'        => ['nullable', 'string'],
      'status'      => ['nullable', 'string'],
      'is_admin'    => ['boolean'],
    ]);

    $user->forceFill($validated)->save();

    return back()->with('success', 'Record updated successfully.');
  }

  public function getUsersForLinking()
  {
    $limit = Settings::getPersonal('linking_panel_limit');

    return User::getRecordsForLinking($limit);
  }

  /**
   * Search users for record selector (owner field).
   * GET /users/search?q=&page=
   */
  public function search(Request $request): \Illuminate\Http\JsonResponse
  {
    $perPage    = Settings::getPersonal('linking_panel_limit');
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

  public function impersonate(Request $request, User $user)
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

      $session = ImpersonationSession::create([
          'impersonator_id' => $currentUser->id,
          'target_user_id' => $user->id,
          'ip_address' => $request->ip(),
          'started_at' => now(),
      ]);
      Session::put('impersonation_session_id', $session->id);

      return redirect()->route('dashboard');
  }

  public function sendPasswordResetEmail(User $user)
  {
      if (!$user->email) {
          return back()->with('error', 'This user has no email address on file.');
      }

      Password::sendResetLink(['email' => $user->email]);

      return back()->with('success', 'Password reset email sent.');
  }

  /**
   * Used right after admin-creating a user (no password set yet), as
   * opposed to sendPasswordResetEmail() which is for existing users who
   * already have a working password.
   */
  public function sendSetPasswordEmail(User $user)
  {
      if (!$user->email) {
          return back()->with('error', 'This user has no email address on file.');
      }

      Password::sendResetLink(
          ['email' => $user->email],
          fn ($user, $token) => $user->notify(new SetPasswordNotification($token))
      );

      return back()->with('success', 'Set password email sent.');
  }
  public function leaveImpersonation()
  {
      $impersonatorId = session('impersonator_id');

      if (!$impersonatorId) {
          abort(403);
      }
      if ($sessionId = session('impersonation_session_id')) {
          ImpersonationSession::whereKey($sessionId)->update(['ended_at' => now()]);
      }

      $originalUser = User::findOrFail($impersonatorId);

      Auth::login($originalUser);
      session()->forget(['impersonator_id', 'impersonation_session_id']);

      return redirect()->route('dashboard');
  }
}
