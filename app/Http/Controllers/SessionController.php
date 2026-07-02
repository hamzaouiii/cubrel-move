<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SessionController extends Controller
{
  public function keepAlive(Request $request): Response
  {
    return response()->noContent();
  }
}
