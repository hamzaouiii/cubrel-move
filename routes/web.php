<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ContactMessageController;

Route::get('/', fn () => Inertia::render('Home'))->name('home');
Route::get('/impressum', fn () => Inertia::render('Impressum'))->name('impressum');
Route::get('/datenschutz', fn () => Inertia::render('Datenschutz'))->name('datenschutz');

Route::post('/contact', [\App\Http\Controllers\ContactMessageController::class, 'store'])
    ->name('contact.store');