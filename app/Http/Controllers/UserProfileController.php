<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class UserProfileController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    $user = Auth::user();
    $recordLayout = config("module_layouts.users.record");

    return Inertia::render('Profile/Index', array_merge([
      'layout' => $recordLayout,
      'user'    => $user,
    ]));
  }
}
