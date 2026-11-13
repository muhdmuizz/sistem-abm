<?php

use App\Http\Controllers\GoogleSignInController;
use App\Http\Controllers\ReferenceController;
use App\Http\Controllers\PenggunaController;
use App\Http\Controllers\AksesPeruntukanController;
use App\Http\Middleware\EnsureAbmSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route(Auth::check() ? 'home' : 'login'));
Route::get('/login', [GoogleSignInController::class, 'login'])->name('login');
Route::get('/auth/google', [GoogleSignInController::class, 'redirect'])->middleware('throttle:10,1')->name('google.redirect');
Route::get('/auth/google/callback', [GoogleSignInController::class, 'callback'])->middleware('throttle:20,1')->name('google.callback');
Route::post('/logout', [GoogleSignInController::class, 'logout'])->middleware('auth')->name('logout');
Route::middleware(['auth', EnsureAbmSession::class])->group(function () {
    Route::get('/pentadbiran/aktiviti-kewangan', [AksesPeruntukanController::class, 'index'])->name('permissions.index');
    Route::get('/pentadbiran/aktiviti-kewangan/{kind}/{key}', [AksesPeruntukanController::class, 'edit'])
        ->whereIn('kind', ['kategori', 'ptj'])->where('key', '[A-Za-z0-9-]+')->name('permissions.edit');
    Route::post('/pentadbiran/aktiviti-kewangan/{kind}/{key}/aktiviti/{activity}', [AksesPeruntukanController::class, 'save'])
        ->whereIn('kind', ['kategori', 'ptj'])->whereNumber('activity')->where('key', '[A-Za-z0-9-]+')
        ->middleware('throttle:30,1')->name('permissions.save');
    Route::get('/pentadbiran/pengguna', [PenggunaController::class, 'index'])->name('users.index');
    Route::get('/pentadbiran/pengguna/tambah', [PenggunaController::class, 'form'])->name('users.create');
    Route::post('/pentadbiran/pengguna', [PenggunaController::class, 'save'])->middleware('throttle:30,1')->name('users.store');
    Route::get('/pentadbiran/pengguna/{user}/pinda', [PenggunaController::class, 'form'])->whereNumber('user')->name('users.edit');
    Route::put('/pentadbiran/pengguna/{user}', [PenggunaController::class, 'save'])->whereNumber('user')->middleware('throttle:30,1')->name('users.update');
    Route::post('/pentadbiran/pengguna/{user}/tamatkan-sesi', [PenggunaController::class, 'revoke'])->whereNumber('user')->middleware('throttle:10,1')->name('users.revoke');
    Route::post('/sesi/keluar-semua', [PenggunaController::class, 'logoutAll'])->middleware('throttle:10,1')->name('session.logout-all');
    Route::view('/utama', 'abm.home')->name('home');
    Route::get('/pentadbiran/rujukan/{section}', [ReferenceController::class, 'index'])
        ->whereIn('section', ['ptj', 'aktiviti', 'tempat-bertugas', 'os'])->name('references');
    Route::post('/sesi/teruskan', fn () => redirect()->route('home'))->name('session.continue');
});
