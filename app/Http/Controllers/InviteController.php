<?php

namespace App\Http\Controllers;

use App\Models\UserInvite;
use App\Services\Users\InviteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use App\Http\Controllers\Controller;
use App\Rules\NoPendingInvite;
use App\Models\Module;
use App\Handlers\Modules\UserInviteModuleHandler;

class InviteController extends Controller
{
  public function __construct(private InviteService $invites) {}

  public function store(Request $request): JsonResponse
  {
    $data = $request->validate(
      [
        'email' => ['required', 'email', 'unique:users,email', new NoPendingInvite()],
        'is_Admin'  => 'sometimes|boolean',
      ],
      [
        'email.required' => __('modules.users.modal.email_required'),
        'email.email' => __('modules.users.modal.email_invalid'),
        'email.unique' => __('modules.users.modal.email_exists'),
        'is_Admin.boolean' => __('modules.users.modal.is_admin_bool'),
      ]
    );

    $invite = $this->invites->create($data['email'], $request->user()->id, $data['role'] ?? 'user');

    return response()->json([
      'invite_url' => route('invites.show', $invite->token),
    ]);
  }

  public function show(string $token): InertiaResponse
  {
    $invite = UserInvite::where('token', $token)->firstOrFail();
    return Inertia::render('Users/AcceptInvite', [
      'email' => $invite->email,
      'token' => $token,
      'notValid' =>  !$invite->isPending()
    ]);
  }


  public function accept(string $token, Request $request): RedirectResponse
  {
    $data = $request->validate([
      'first_name'                  => 'required|string|max:255',
      'last_name'                  => 'required|string|max:255',
      'username'                  => 'required|string|max:255|unique:users',
      'password'              => 'required|confirmed|min:8',
    ]);

    $user = $this->invites->accept($token, $data);

    Auth::login($user);

    return redirect('/users/' . $user->id);
  }


  public function bulkStore(Request $request): JsonResponse
  {
    $data = $request->validate([
      'invites'           => 'required|array|min:1|max:20',
      'invites.*.email'   => 'required|email|unique:users,email|unique:user_invites,email',
      'invites.*.is_admin' => 'boolean',
    ]);

    $results = [];

    foreach ($data['invites'] as $entry) {
      $invite = $this->invites->create(
        email: $entry['email'],
        invitedBy: $request->user()->id,
        is_admin: $entry['is_admin'],
      );

      $results[] = [
        'email'      => $invite->email,
        'invite_url' => route('invites.show', $invite->token),
      ];
    }

    return response()->json(['invites' => $results]);
  }

  public function list(Request $request): InertiaResponse
  {

    $moduleModel = Module::query()
      ->where('slug', 'user-invites')
      ->where('is_active', true)
      ->firstOrFail();

    $handler = new UserInviteModuleHandler();
    $props = $handler->getListData($moduleModel);
    $listLayout = config("module_layouts.userinvites.list");
    $recorddropdownLists = $moduleModel->dropdownLists;
    $fields = $moduleModel->allFields();

    return Inertia::render('Invites/List', array_merge([
      'module'     => $moduleModel,
      'title'      => $moduleModel->name,
      'listLayout' => $listLayout,
      'fields'     => $fields,
      'filters'    => request()->only(['search', 'perPage']),
      'dropdownLists' => $recorddropdownLists,

    ], $props));
  }

  public function resend(UserInvite $invite): RedirectResponse
  {
    abort_if(
      !in_array($invite->status, ['pending', 'expired']),
      422,
      'Only pending or expired invites can be resent.'
    );

    $newInvite = $this->invites->create(
      email: $invite->email,
      invitedBy: Auth::id(),
      is_admin: $invite->is_admin,
    );

    return back()->with(['success']);
  }

  public function revoke(UserInvite $invite): RedirectResponse
  {
    abort_if(
      $invite->status !== 'pending',
      422,
      'Only pending invites can be revoked.'
    );

    $invite->update(['status' => 'revoked']);

    return back();
  }

  public function destroy(UserInvite $invite): RedirectResponse
  {
    abort_if(
      $invite->status === 'pending',
      422,
      'Revoke the invite before deleting it.'
    );

    $invite->delete();

    return back();
  }
}
