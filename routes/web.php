<?php

use Inertia\Inertia;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ContactMessageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AuthController;
use App\Models\Module;


Route::middleware(['auth'])->group(function () {
  Route::get('/ar-admin', fn () => Inertia::render('Admin/Dashboard'))->name('AdminDashboard');
  Route::post('/ar-admin/logout',  [AuthController::class, 'logout']);
});


Route::middleware(['auth'])
    ->prefix('ar-admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', fn () => Inertia::render('Admin/Dashboard'))->name('dashboard');
        
        $modules = Module::query()
            ->where('is_active', true)
            ->pluck('slug');

         foreach ($modules as $module) {
            Route::get("/{$module}", function () use ($module) {
                return Inertia::render('Admin/Modules/List', [
                    'module' => ucwords($module),   
                ]);
            })->name("{$module}.index");
        }
    });

Route::middleware(['guest'])->group(function () {
  Route::get('/', fn () => Inertia::render('Home'))->name('home');
  Route::get('/impressum', fn () => Inertia::render('Impressum'))->name('impressum');
  Route::get('/datenschutz', fn () => Inertia::render('Datenschutz'))->name('datenschutz');

  Route::post('/contact', [\App\Http\Controllers\ContactMessageController::class, 'store'])->name('contact.store');
  Route::get('/ar-admin/login', [AuthController::class, 'index'])->name('login');
  Route::post('/ar-admin/login', [AuthController::class, 'login']);
});

