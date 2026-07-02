<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;

class AuthController extends Controller
{
  public function index()
  {

    return \Inertia\Inertia::render('Login');
  }

  public function login(Request $request)
  {
    $data = $request->validate([
      'username' => ['required', 'string'],
      'password' => ['required', 'string'],
      'remember' => ['sometimes', 'boolean'],
    ]);


    if (! Auth::attempt(['username' => $data['username'], 'password' => $data['password']], $data['remember'] ?? false)) {
      return back()->withErrors([
        'general' => __('globals.login.invalid_credentials')
      ]);
    }
    if (Auth::user()->status === 'inactive') {
      Auth::logout();
      return back()->withErrors(['general' =>  __('globals.login.user_inactive')]);
    }
    $request->session()->regenerate();
    return redirect()->intended('/');
  }

  public function logout(Request $request)
  {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect()->to('/login');
  }

  public function forgot(Request $request)
  {
    $request->validate([
      'email' => ['required', 'email'],
      [
        'email.required' => __('globals.login.invalid_email'),
        'email.email'    => __('globals.login.invalid_email'),
      ]
    ]);
    Password::sendResetLink($request->only('email'));

    return back()->with('status', __('passwords.sent'));
  }

  public function resetForm(Request $request, string $token)
  {
    return Inertia::render('ResetPassword', [
      'token'       => $token,
      'email'       => $request->query('email'),
    ]);
  }

  public function reset(Request $request)
  {
    $request->validate([
      'token'    => ['required'],
      'email'    => ['required', 'email'],
      'password' => ['required', 'min:8', 'confirmed'],
    ]);

    $status = Password::reset(
      $request->only('email', 'password', 'password_confirmation', 'token'),
      function (User $user, string $password) {
        $user->forceFill([
          'password' => Hash::make($password),
        ])->setRememberToken(Str::random(60));

        $user->save();

        event(new PasswordReset($user));
      }
    );

    if ($status === Password::PASSWORD_RESET) {
      return redirect()->route('login')->with('status', __($status));
    }

    return back()->withErrors([
      'email' => __($status),
    ]);
  }
}
