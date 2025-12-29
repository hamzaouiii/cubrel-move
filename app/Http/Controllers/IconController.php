<?php

namespace App\Http\Controllers;

use App\Models\Icon;
use Illuminate\Http\Request;

class IconController extends Controller
{
  public function index(Request $request)
  {
    $query = Icon::query();

    if ($search = $request->input('q')) {
      $query->where('name', 'like', '%' . $search . '%');
    }

    if ($style = $request->input('style')) {
      $query->where('style', $style);
    }

    $icons = $query
      ->inRandomOrder()
      ->paginate(16);

    return response()->json($icons);
  }
}
