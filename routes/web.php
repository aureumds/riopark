<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DailyClosingController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::check()) {
        $user = Auth::user();

        if ($user->isSuperAdmin()) {
            return redirect()->route('super.dashboard');
        }

        if ($user->isCompanyAdmin()) {
            return redirect()->route('admin.dashboard');
        }
    }

    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::view('/operador', 'operator')->name('operator');

Route::middleware(['auth', 'role:super_admin'])->prefix('super')->name('super.')->group(function () {
    Route::get('/dashboard', \App\Livewire\Super\Dashboard::class)->name('dashboard');
    Route::get('/companies', \App\Livewire\Super\Companies::class)->name('companies');
    Route::get('/plans', \App\Livewire\Super\Plans::class)->name('plans');
});

Route::middleware(['auth', 'role:company_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', \App\Livewire\Admin\Dashboard::class)->name('dashboard');
    Route::get('/parking-lots', \App\Livewire\Admin\ParkingLots::class)->name('parking-lots');
    Route::get('/operators', \App\Livewire\Admin\Operators::class)->name('operators');
    Route::get('/tariff', \App\Livewire\Admin\Tariff::class)->name('tariff');
    Route::get('/settings', \App\Livewire\Admin\Settings::class)->name('settings');
    Route::get('/shifts', \App\Livewire\Admin\Shifts::class)->name('shifts');
    Route::get('/closing', \App\Livewire\Admin\Closing::class)->name('closing');

    Route::post('/closing', [DailyClosingController::class, 'store'])->name('closing.store');
    Route::get('/closing/{date}/pdf', [DailyClosingController::class, 'pdf'])->name('closing.pdf');
    Route::get('/sessions/{session}/ticket', [DailyClosingController::class, 'ticket'])->name('sessions.ticket');
});
