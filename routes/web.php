<?php

use Inertia\Inertia;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ListController;
use App\Http\Controllers\RecordController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ModuleManagerController;
use App\Http\Controllers\LayoutManagerController;
use App\Http\Controllers\FieldsManagerController;


Route::middleware(['auth'])->group(function () {

  /**
   * Independent routes
   */
  Route::get('/', fn() => Inertia::render('Dashboard'))->name('dashboard');
  Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

  /**
   * Settings routes
   */
  Route::prefix('settings/')->name('settings.')->group(function () {

    // module manager
    Route::resource('modules', ModuleManagerController::class)->names('modules');

    // Fields Manager
    Route::resource('fields', FieldsManagerController::class)
      ->names('fields')
      ->except('edit');
    Route::get('fields/{module}/create', [FieldsManagerController::class, 'create'])->name('fields.create');
    Route::get('fields/{module}/{field}/edit', [FieldsManagerController::class, 'edit'])->name('fields.edit');


    // Layout Manager
    Route::get('layouts', [LayoutManagerController::class, 'index'])->name('layouts.index');
    Route::get('layouts/{module}', [LayoutManagerController::class, 'show'])->name('layouts.show');
    Route::get('layouts/{module}/{layoutType}', [LayoutManagerController::class, 'edit'])->name('layouts.edit');
    Route::post('layouts/{module}/{layoutType}', [LayoutManagerController::class, 'store'])->name('layouts.store');
  });


  //System Settings
  // Route::get('/settings/system/style', [SystemSettingsController::class, 'style'])->name('settings.system.style');
  Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
  Route::put('/settings/{item}', [SettingsController::class, 'update'])->name('settings.update');
  Route::get('/settings/{category}/{item}', [SettingsController::class, 'show'])->name('settings.show');


  /**
   * Modules routes
   */
  Route::get('{module}/create', [RecordController::class, 'create'])->name('record.create');
  Route::post('{module}', [RecordController::class, 'store'])->name('record.store');


  Route::get('/{module}/{recordId}', RecordController::class)->name('modules.record.show');
  Route::put('/{module}/{record}', [RecordController::class, 'update'])->name('modules.records.update');
  Route::delete('/{module}', [RecordController::class, 'destroyMany'])->name('modules.records.destroyMany');
  Route::delete('/{module}/{record}', [RecordController::class, 'destroy'])->name('modules.records.destroy');

  Route::get('/{module}', ListController::class)->where('module', '^(?!login$|logout$).+')->name('modules.index');
});



Route::middleware(['guest'])->group(function () {
  Route::get('/login', [AuthController::class, 'index'])->name('login');
  Route::post('/login', [AuthController::class, 'login']);
});
