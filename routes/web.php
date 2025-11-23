<?php

  use Inertia\Inertia;
  use Illuminate\Foundation\Application;
  use Illuminate\Support\Facades\Route;

  use App\Http\Controllers\ContactMessageController;
  use App\Http\Controllers\ProfileController;
  use App\Http\Controllers\AuthController;
  use App\Http\Controllers\AdminModuleController;
  use App\Http\Controllers\AdminModuleRecordController;
  use App\Http\Controllers\ModuleManagerController;




  Route::middleware(['auth'])
  ->group(function () {

    Route::get('/', fn () => Inertia::render('Dashboard'))
        ->name('dashboard'); 
      Route::get('/modules', [ModuleManagerController::class, 'index'])
          ->name('modules.index');

      Route::get('/{module}/{recordId}', AdminModuleRecordController::class);
      
      Route::put('/{module}/{record}', [AdminModuleRecordController::class,'update'])
      ->name('modules.records.update');

      Route::get('/{module}', AdminModuleController::class)
        ->where('module', '^(?!login$|logout$).+')
        ->name('modules.index');
      

      Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout'); 
  });



Route::middleware(['guest'])->group(function () {
 
    Route::get('/login', [AuthController::class, 'index'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});
