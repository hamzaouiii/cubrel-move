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


  Route::get('/', fn () => Inertia::render('Home'))->name('home');
  Route::get('/impressum', fn () => Inertia::render('Impressum'))->name('impressum');
  Route::get('/datenschutz', fn () => Inertia::render('Datenschutz'))->name('datenschutz');
  Route::post('/contact', [ContactMessageController::class, 'store'])->name('contact.store');

  Route::middleware(['auth'])
  ->prefix('ar-admin')
  ->name('admin.')
  ->group(function () {

    Route::get('/', fn () => Inertia::render('Admin/Dashboard'))
        ->name('dashboard'); 
      Route::get('/modules', [ModuleManagerController::class, 'index'])
          ->name('admin.modules.index');

      Route::get('/{module}/{recordId}', AdminModuleRecordController::class);
      
      Route::put('/{module}/{record}', [AdminModuleRecordController::class,'update'])
      ->name('admin.modules.records.update');

      Route::get('/{module}', AdminModuleController::class)
        ->where('module', '^(?!login$|logout$).+')
        ->name('modules.index');
      

      Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout'); 
  });



Route::middleware(['guest'])->group(function () {
 
    Route::get('/ar-admin/login', [AuthController::class, 'index'])->name('login');
    Route::post('/ar-admin/login', [AuthController::class, 'login']);
});
