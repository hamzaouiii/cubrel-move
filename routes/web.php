<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ListController;
use App\Http\Controllers\RecordController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ModuleManagerController;
use App\Http\Controllers\LayoutManagerController;
use App\Http\Controllers\FieldsManagerController;
use App\Http\Controllers\DropdownListController;
use App\Http\Controllers\RelationshipLinkController;

Route::middleware(['auth'])->group(function () {

  // Independent routes
  Route::get('/', fn() => Inertia::render('Dashboard'))->name('dashboard');
  Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

  // module Manager
  Route::prefix('settings')->name('settings.')->group(function () {

    // Module manager
    Route::resource('modules', ModuleManagerController::class)
      ->names('modules');

    // Module scoped resources
    Route::prefix('modules/{module}')
      ->group(function () {

        // Fields
        Route::get('fields', [FieldsManagerController::class, 'show'])
          ->name('modules.fields.index');

        Route::get('fields/create', [FieldsManagerController::class, 'create'])
          ->name('modules.fields.create');

        Route::get('fields/{field}', [FieldsManagerController::class, 'edit'])
          ->name('modules.fields.edit');

        Route::post('fields/create', [FieldsManagerController::class, 'store'])
          ->name('modules.fields.store');

        Route::put('fields/{field}', [FieldsManagerController::class, 'update'])
          ->name('modules.fields.update');

        // Route::delete('fields/{field}', [FieldsManagerController::class, 'destroy'])
        //   ->name('modules.fields.destroy');


        // Layouts
        Route::get('layouts',  [LayoutManagerController::class, 'show'])
          ->name('modules.layouts.show');

        Route::get('layouts/{layoutType}', [LayoutManagerController::class, 'edit'])
          ->name('modules.layouts.edit');

        Route::post('layouts/{layoutType}', [LayoutManagerController::class, 'store'])
          ->name('modules.layouts.store');
      });

    Route::get('modulebuilder', [ModuleManagerController::class, 'create']);

    Route::get('dropdowns', [DropdownListController::class, 'index']);
    Route::get('dropdowns/create', [DropdownListController::class, 'create']);
    Route::post('dropdowns', [DropdownListController::class, 'store']);
    Route::put('dropdowns/{dropdown_key}', [DropdownListController::class, 'update']);
    Route::post('dropdowns_in_fields', [DropdownListController::class, 'storeAndAttach']);
    Route::get('dropdowns/{dropdown_key}', [DropdownListController::class, 'show'])
      ->name('dropdowns.show');
  });

  // System Settings
  Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
  Route::put('/settings/{item}', [SettingsController::class, 'update'])->name('settings.update');
  Route::get('/settings/{category}/{item}', [SettingsController::class, 'show'])->name('settings.show');


  // Modules routes
  Route::get('{module}/create', [RecordController::class, 'create'])->name('record.create');
  Route::post('{module}', [RecordController::class, 'store'])->name('record.store');
  Route::get('/modules/{module}/{record_id}/relationships/{relationship}/available', [RelationshipLinkController::class, 'getRecordsForLinking'])->name('relationships.available');
  Route::get('/modules/{module}/{record_id}/relationships/{relationship}/single_link', [RelationshipLinkController::class, 'getRecordsForUpdateSingleLinking'])->name('relationships.single_link');
  Route::post('/modules/{module}/{record_id}/relationships/{relationship}', [RelationshipLinkController::class, 'linkRecords'])->name('relationships.link');
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
