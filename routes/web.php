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




  Route::middleware(['auth'])
  ->group(function () {

    Route::get('/', fn () => Inertia::render('Dashboard'))
        ->name('dashboard'); 
      Route::get('/settings/customisation/modules', [ModuleManagerController::class, 'index'])
          ->name('settings.index');

      Route::get('/settings', [SettingsController::class, 'index'])
        ->name('settings.index');

      Route::get('/settings/{category}/{item}', [SettingsController::class, 'show'])
        ->name('settings.show');

      Route::get('/{module}/{recordId}', RecordController::class);
      
      Route::put('/{module}/{record}', [RecordController::class,'update'])
      ->name('modules.records.update');

      Route::get('/{module}', ListController::class)
        ->where('module', '^(?!login$|logout$).+')
        ->name('modules.index');
    
      Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout'); 
  });



Route::middleware(['guest'])->group(function () {
 
    Route::get('/login', [AuthController::class, 'index'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});
