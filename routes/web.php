<?php

use App\Http\Controllers\GoogleSignInController;
use App\Http\Controllers\ReferenceController;
use App\Http\Controllers\PenggunaController;
use App\Http\Controllers\AksesPeruntukanController;
use App\Http\Controllers\KitaranController;
use App\Http\Controllers\BidaPeruntukanController;
use App\Http\Middleware\EnsureAbmSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route(Auth::check() ? 'home' : 'login'));
Route::get('/login', [GoogleSignInController::class, 'login'])->name('login');
Route::get('/auth/google', [GoogleSignInController::class, 'redirect'])->middleware('throttle:10,1')->name('google.redirect');
Route::get('/auth/google/callback', [GoogleSignInController::class, 'callback'])->middleware('throttle:20,1')->name('google.callback');
Route::post('/logout', [GoogleSignInController::class, 'logout'])->middleware('auth')->name('logout');
Route::middleware(['auth', EnsureAbmSession::class])->group(function () {
    Route::get('/bida-peruntukan', [BidaPeruntukanController::class, 'index'])->name('bids.index');
    Route::get('/bida-peruntukan/kitaran/{cycle}/ptj/{ptj}/aktiviti/{activity}/aktifkan', [BidaPeruntukanController::class, 'bidForm'])->whereNumber(['cycle','ptj','activity'])->name('bids.activate-form');
    Route::post('/bida-peruntukan/kitaran/{cycle}/ptj/{ptj}/aktiviti/{activity}/aktifkan', [BidaPeruntukanController::class, 'activateBid'])->whereNumber(['cycle','ptj','activity'])->middleware('throttle:30,1')->name('bids.activate');
    Route::get('/bida-peruntukan/{bid}', [BidaPeruntukanController::class, 'show'])->whereNumber('bid')->name('bids.show');
    Route::get('/bida-peruntukan/{bid}/os/{os}/aktifkan', [BidaPeruntukanController::class, 'osForm'])->whereNumber(['bid','os'])->name('bids.os-form');
    Route::post('/bida-peruntukan/{bid}/os/{os}/aktifkan', [BidaPeruntukanController::class, 'activateOs'])->whereNumber(['bid','os'])->middleware('throttle:30,1')->name('bids.os-activate');
    Route::get('/pentadbiran/kitaran', [KitaranController::class, 'index'])->name('cycles.index');
    Route::get('/pentadbiran/kitaran/tambah', [KitaranController::class, 'create'])->name('cycles.create');
    Route::post('/pentadbiran/kitaran', [KitaranController::class, 'store'])->middleware('throttle:30,1')->name('cycles.store');
    Route::get('/pentadbiran/kitaran/{cycle}/fasa/tambah', [KitaranController::class, 'phaseForm'])->whereNumber('cycle')->name('phases.create');
    Route::post('/pentadbiran/kitaran/{cycle}/fasa', [KitaranController::class, 'phaseSave'])->whereNumber('cycle')->middleware('throttle:30,1')->name('phases.store');
    Route::get('/pentadbiran/kitaran/{cycle}/fasa/{phase}/pinda', [KitaranController::class, 'phaseForm'])->whereNumber(['cycle','phase'])->name('phases.edit');
    Route::put('/pentadbiran/kitaran/{cycle}/fasa/{phase}', [KitaranController::class, 'phaseSave'])->whereNumber(['cycle','phase'])->middleware('throttle:30,1')->name('phases.update');
    Route::get('/pentadbiran/kitaran/{cycle}/fasa/{phase}/tindakan/{action}', [KitaranController::class, 'actionForm'])->whereNumber(['cycle','phase'])->name('phases.action');
    Route::post('/pentadbiran/kitaran/{cycle}/fasa/{phase}/tindakan/{action}', [KitaranController::class, 'actionSave'])->whereNumber(['cycle','phase'])->middleware('throttle:30,1')->name('phases.execute');
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
