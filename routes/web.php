<?php

use App\Http\Controllers\GoogleSignInController;
use App\Http\Controllers\ReferenceController;
use App\Http\Middleware\EnsureAbmSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route(Auth::check() ? 'home' : 'login'));
Route::get('/login', [GoogleSignInController::class, 'login'])->name('login');
Route::get('/auth/google', [GoogleSignInController::class, 'redirect'])->middleware('throttle:10,1')->name('google.redirect');
Route::get('/auth/google/callback', [GoogleSignInController::class, 'callback'])->middleware('throttle:20,1')->name('google.callback');
Route::post('/logout', [GoogleSignInController::class, 'logout'])->middleware('auth')->name('logout');
Route::middleware(['auth', EnsureAbmSession::class])->group(function () {
    Route::view('/utama', 'abm.home')->name('home');
    Route::get('/pentadbiran/rujukan/{section}', [ReferenceController::class, 'index'])
        ->whereIn('section', ['ptj', 'aktiviti', 'tempat-bertugas', 'os'])->name('references');
    Route::post('/sesi/teruskan', fn () => redirect()->route('home'))->name('session.continue');
});
