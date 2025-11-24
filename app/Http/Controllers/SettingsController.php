<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Contracts\ModuleHandler;
use Illuminate\Http\Request;
use App\Models\Modules\Settings;

class SettingsController extends Controller
{
    public function index(){
    $settings = Settings::with('items')
    ->orderBy('created_at')->get();
      return Inertia::render('Settings', [
        'settings'     => $settings]);
    }
}
