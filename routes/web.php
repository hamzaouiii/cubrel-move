<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ListController;
use App\Http\Controllers\RecordController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ModuleManagerController;
use App\Http\Controllers\ModuleBuilderController;
use App\Http\Controllers\LayoutManagerController;
use App\Http\Controllers\FieldsManagerController;
use App\Http\Controllers\DropdownListController;
use App\Http\Controllers\RelationshipLinkController;
use App\Http\Controllers\RelationshipManagerController;
use App\Http\Controllers\ModuleDeploymentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserProfileController;

Route::middleware(['guest'])->group(function () {
  Route::get('/login', [AuthController::class, 'index'])->name('login');
  Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware(['auth'])->group(function () {

  // Independent routes
  Route::get('/', fn() => Inertia::render('Dashboard'))->name('dashboard');
  Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
  Route::get('/profile', [UserProfileController::class, 'index'])->name('profile.index');
  Route::put('/profile', [UserProfileController::class, 'update'])->name('profile.update');

  // module Manager
  Route::middleware(['admin'])->group(function () {



    // user routes
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/{user_id}', [UserController::class, 'show'])->name('users.show');

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

          // Relationships
          Route::resource('relationships', RelationshipManagerController::class)->names('relationships');
        });

      //moduleBuilder
      Route::get('modulebuilder', [ModuleBuilderController::class, 'create'])
        ->name('modules.builder.create');

      Route::put('modulebuilder/{module}', [ModuleBuilderController::class, 'update'])
        ->name('modules.builder.update');

      Route::post('modulebuilder/{module}/field', [ModuleBuilderController::class, 'saveDraftField'])
        ->name('modules.builder.saveDraftField');

      Route::delete('modulebuilder/{module}/field/{field}', [ModuleBuilderController::class, 'deleteDraftField'])
        ->name('modules.builder.deleteDraftField');
      Route::prefix('modulebuilder/{module}/deploy')->controller(ModuleDeploymentController::class)->group(function () {
        Route::post('/initialize', 'initialize');
        Route::post('/generate-files', 'generateFiles');
        Route::post('/create-labels', 'createLabels');
        Route::post('/activate-fields', 'activateFields');
        Route::post('/create-table', 'createTable');
        Route::post('/rollback', 'rollback');
      });

      // dropdowns
      Route::get('dropdowns', [DropdownListController::class, 'index']);
      Route::get('dropdowns/create', [DropdownListController::class, 'create']);
      Route::post('dropdowns', [DropdownListController::class, 'store']);
      Route::put('dropdowns/{dropdown_id}', [DropdownListController::class, 'update']);
      Route::post('dropdowns_in_fields', [DropdownListController::class, 'storeAndAttach']);
      Route::get('dropdowns/{dropdown_id}', [DropdownListController::class, 'show'])
        ->name('dropdowns.show');
    });
    // System Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings/{item}', [SettingsController::class, 'update'])->name('settings.update');
    Route::get('/settings/{category}/{item}', [SettingsController::class, 'show'])->name('settings.show');
  });

  // Modules routes
  Route::get('{module}/create', [RecordController::class, 'create'])->name('record.create');
  Route::post('{module}', [RecordController::class, 'store'])->name('record.store');
  Route::get('/modules/{module}/{record_id}/relationships/{relationship}/available', [RelationshipLinkController::class, 'getRecordsForLinking'])->name('relationships.available');
  Route::get('/modules/{module}/{record_id}/relationships/{relationship}/single-link', [RelationshipLinkController::class, 'getRecordsForUpdateSingleLinking'])->name('relationships.single-link');
  Route::post('/modules/{module}/{record_id}/relationships/{relationship}', [RelationshipLinkController::class, 'linkRecords'])->name('relationships.link');
  Route::delete('/modules/{module}/{record_id}/relationships/{relationship}/{relatedId}', [RelationshipLinkController::class, 'unlink'])->name('relationships.unlink');
  Route::get('/{module}/{recordId}', RecordController::class)->name('modules.record.show');
  Route::put('/{module}/{record}', [RecordController::class, 'update'])->name('modules.records.update');
  Route::delete('/{module}', [RecordController::class, 'destroyMany'])->name('modules.records.destroyMany');
  Route::put('/{module}', [RecordController::class, 'updateMany'])->name('modules.records.updateMany');
  Route::delete('/{module}/{record}', [RecordController::class, 'destroy'])->name('modules.records.destroy');

  Route::get('/{module}', ListController::class)->where('module', '^(?!login$|logout$|profile$).+')->name('modules.index');
});



Route::middleware(['guest'])->group(function () {
  Route::get('/login', [AuthController::class, 'index'])->name('login');
  Route::post('/login', [AuthController::class, 'login']);
});
