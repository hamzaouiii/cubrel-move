<?php

  use Inertia\Inertia;
  use Illuminate\Foundation\Application;
  use Illuminate\Support\Facades\Route;

  use App\Http\Controllers\ContactMessageController;
  use App\Http\Controllers\ProfileController;
  use App\Http\Controllers\AuthController;
  use App\Http\Controllers\ListController;
  use App\Http\Controllers\RecordController;
  use App\Http\Controllers\SettingsController;
  use App\Http\Controllers\ModuleManagerController;
  use App\Http\Controllers\SystemSettingsController;
  use App\Http\Controllers\SettingValueController;

  Route::middleware(['auth'])->group(function () {
    
    /**
     * Independent routes
     */
    Route::get('/', fn () => Inertia::render('Dashboard'))->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout'); 

    /**
     * Settings routes
     */
    // module manager
    Route::get('/settings/customisation/modules', [ModuleManagerController::class, 'index'])->name('settings.modules.index');
    Route::get('/settings/customisation/modules/create', [ModuleManagerController::class,'create'])->name('settings.modules.create');
    Route::post('/settings/customisation/modules/create', [ModuleManagerController::class,'store'])->name('settings.modules.store');
    Route::get('/settings/customisation/modules/{module}', [ModuleManagerController::class, 'show'])->name('settings.modules.show');
    Route::put('/settings/customisation/modules/{module}', [ModuleManagerController::class,'update'])->name('settings.modules.update');

    //System Settings
    // Route::get('/settings/system/style', [SystemSettingsController::class, 'style'])->name('settings.system.style');
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings/{item}', [SettingsController::class, 'update'])->name('settings.update');

    Route::get('/settings/{category}/{item}', [SettingsController::class, 'show'])->name('settings.show');

    /**
     * Modules routes
     */
    Route::get('/{module}/{recordId}', RecordController::class)->name('modules.record.show');
    Route::put('/{module}/{record}', [RecordController::class,'update'])->name('modules.records.update');
    Route::get('/{module}', ListController::class)->where('module', '^(?!login$|logout$).+')->name('modules.index');

  });



Route::middleware(['guest'])->group(function () {
    Route::get('/login', [AuthController::class, 'index'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});
