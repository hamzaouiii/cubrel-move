<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
  public function index() {
    return \Inertia\Inertia::render('Login'); 
  }

  public function login(Request $request) {
    $data = $request->validate([
      'username' => ['required','string'],
      'password' => ['required','string'],
      'remember' => ['sometimes','boolean'],
    ]);

    // Try to authenticate by username & password
    if (! Auth::attempt(['username' => $data['username'], 'password' => $data['password']], $data['remember'] ?? false)) {
        return back()->withErrors(['username' => 'Invalid credentials.'])->onlyInput('username');
    }

    // Ensure the user is an admin
    if (! Auth::user()->is_admin) {
        Auth::logout();
        return back()->withErrors(['username' => 'You do not have admin access.']);
    }

    $request->session()->regenerate();
    return redirect()->to('/');
  }

  public function logout(Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect()->to('/login');
  }

}
