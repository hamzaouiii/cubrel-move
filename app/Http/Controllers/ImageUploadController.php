<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ImageUploadController extends Controller
{
  public function store(Request $request)
  {
    $request->validate([
      'image' => ['required', 'image', 'mimes:jpeg,jpg,png,webp,gif', 'max:2048'],
    ]);

    $path = $request->file('image')->store('uploads/images', 'public');

    // Host-relative path, not Storage::url() — the 'public' disk builds an
    // absolute URL from APP_URL, which won't match the host/port the app is
    // actually being browsed on (e.g. `php artisan serve` on 127.0.0.1:8000
    // vs APP_URL=http://localhost).
    return response()->json([
      'url' => '/storage/' . $path,
    ]);
  }
}
