<?php

use Inertia\Inertia;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ContactMessageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminModuleController;

/*
|--------------------------------------------------------------------------
| Admin (authenticated)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])
    ->prefix('ar-admin')
    ->name('admin.')
    ->group(function () {
        // Dashboard
        Route::get('/', fn () => Inertia::render('Admin/Dashboard'))
            ->name('dashboard'); // admin.dashboard

        Route::get('/{module}', AdminModuleController::class)
            ->where('module', '^(?!login$|logout$).+')
            ->name('modules.index');

        // Logout
        Route::post('/logout', [AuthController::class, 'logout'])
            ->name('logout'); // admin.logout
    });

/*
|--------------------------------------------------------------------------
| Public (guest)
|--------------------------------------------------------------------------
*/

Route::middleware(['guest'])->group(function () {
    Route::get('/', fn () => Inertia::render('Home'))->name('home');
    Route::get('/impressum', fn () => Inertia::render('Impressum'))->name('impressum');
    Route::get('/datenschutz', fn () => Inertia::render('Datenschutz'))->name('datenschutz');

    Route::post('/contact', [ContactMessageController::class, 'store'])->name('contact.store');

    Route::get('/ar-admin/login', [AuthController::class, 'index'])->name('login');
    Route::post('/ar-admin/login', [AuthController::class, 'login']);
});
