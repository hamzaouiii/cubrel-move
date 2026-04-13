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

class InviteController extends Controller
{
  public function __construct(private InviteService $invites) {}

  public function store(Request $request): JsonResponse
  {
    $data = $request->validate([
      'email' => 'required|email|unique:users,email',
      'role'  => 'sometimes|in:admin,user',
    ]);

    $invite = $this->invites->create($data['email'], $request->user()->id, $data['role'] ?? 'user');

    return response()->json([
      'invite_url' => route('invites.show', $invite->token),
    ]);
  }

  public function show(string $token): InertiaResponse
  {
    $invite = UserInvite::where('token', $token)->firstOrFail();

    abort_if($invite->isExpired(), 410);
    abort_if(!$invite->isPending(), 409);

    return Inertia::render('Users/AcceptInvite', [
      'email' => $invite->email,
      'token' => $token,
    ]);
  }


  public function accept(string $token, Request $request): RedirectResponse
  {
    $data = $request->validate([
      'name'                  => 'required|string|max:255',
      'username'                  => 'required|string|max:255|unique:users',
      'password'              => 'required|confirmed|min:8',
    ]);

    $user = $this->invites->accept($token, $data);

    Auth::login($user);

    return redirect('/');
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
}
