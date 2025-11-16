<?php

use Inertia\Inertia;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ContactMessageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AuthController;



Route::middleware(['auth'])->group(function () {
  Route::get('/ar-admin', fn () => Inertia::render('Admin/Dashboard'))->name('AdminDashboard');
  Route::post('/ar-admin/logout',  [AuthController::class, 'logout']);
});


Route::middleware(['auth'])
    ->prefix('ar-admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', fn () => Inertia::render('Admin/Dashboard'))->name('dashboard');
        $modules = ['accounts', 'contacts', 'leads', 'cases', 'emails', 'quotes', 'invoices'];

         foreach ($modules as $module) {
            Route::get("/{$module}", function () use ($module) {
                return Inertia::render('Admin/Modules/List', [
                    'module' => ucwords($module),   
                ]);
            })->name("{$module}.index");
        }

        // Route::get('/accounts', fn () => Inertia::render('Admin/Modules/List'))->name('accounts.index');
        // Route::get('/contacts', fn () => Inertia::render('Admin/Modules/Contacts/List'))->name('contacts.index');
        // Route::get('/leads', fn () => Inertia::render('Admin/Modules/Leads/List'))->name('leads.index');
        // Route::get('/cases', fn () => Inertia::render('Admin/Modules/Cases/List'))->name('cases.index');
        // Route::get('/emails', fn () => Inertia::render('Admin/Modules/Emails/List'))->name('emails.index');
        // Route::get('/quotes', fn () => Inertia::render('Admin/Modules/Quotes/List'))->name('quotes.index');
        // Route::get('/invoices', fn () => Inertia::render('Admin/Modules/Invoices/List'))->name('invoices.index');
    });

Route::middleware(['guest'])->group(function () {
  Route::get('/', fn () => Inertia::render('Home'))->name('home');
  Route::get('/impressum', fn () => Inertia::render('Impressum'))->name('impressum');
  Route::get('/datenschutz', fn () => Inertia::render('Datenschutz'))->name('datenschutz');

  Route::post('/contact', [\App\Http\Controllers\ContactMessageController::class, 'store'])->name('contact.store');
  Route::get('/ar-admin/login', [AuthController::class, 'index'])->name('login');
  Route::post('/ar-admin/login', [AuthController::class, 'login']);
});

