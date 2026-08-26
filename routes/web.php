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

Route::prefix('operador-lite')->name('operator-lite.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [\App\Http\Controllers\Operator\Lite\AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [\App\Http\Controllers\Operator\Lite\AuthController::class, 'login']);
    });

    Route::middleware(['auth', 'role:operator'])->group(function () {
        Route::get('/licenca', [\App\Http\Controllers\Operator\Lite\LicenseController::class, 'show'])->name('license');
        Route::post('/licenca', [\App\Http\Controllers\Operator\Lite\LicenseController::class, 'renew']);
        Route::post('/logout', [\App\Http\Controllers\Operator\Lite\AuthController::class, 'logout'])->name('logout');
        Route::post('/sync', [\App\Http\Controllers\Operator\Lite\SyncController::class, 'push'])->name('sync');

        Route::middleware('operator.license')->group(function () {
            Route::get('/', [\App\Http\Controllers\Operator\Lite\DashboardController::class, 'index'])->name('home');
            Route::get('/entrada', [\App\Http\Controllers\Operator\Lite\SessionController::class, 'showEntry'])->name('entry');
            Route::post('/entrada', [\App\Http\Controllers\Operator\Lite\SessionController::class, 'entry']);
            Route::get('/saida', [\App\Http\Controllers\Operator\Lite\SessionController::class, 'showExit'])->name('exit');
            Route::post('/saida', [\App\Http\Controllers\Operator\Lite\SessionController::class, 'exit']);
            Route::get('/saida/preview', [\App\Http\Controllers\Operator\Lite\SessionController::class, 'preview'])->name('exit.preview');
            Route::get('/patio', [\App\Http\Controllers\Operator\Lite\SessionController::class, 'yard'])->name('yard');
            Route::get('/patio/{plate}', [\App\Http\Controllers\Operator\Lite\SessionController::class, 'vehicleDetail'])->name('yard.detail');
            Route::get('/turno', [\App\Http\Controllers\Operator\Lite\ShiftController::class, 'show'])->name('shift');
            Route::post('/turno/abrir', [\App\Http\Controllers\Operator\Lite\ShiftController::class, 'open'])->name('shift.open');
            Route::post('/turno/fechar', [\App\Http\Controllers\Operator\Lite\ShiftController::class, 'close'])->name('shift.close');
            Route::get('/fechamento', [\App\Http\Controllers\Operator\Lite\SessionController::class, 'closing'])->name('closing');
        });
    });
});

Route::middleware(['auth', 'role:super_admin'])->prefix('super')->name('super.')->group(function () {
    Route::get('/dashboard', \App\Livewire\Super\Dashboard::class)->name('dashboard');
    Route::get('/companies', \App\Livewire\Super\Companies::class)->name('companies');
    Route::get('/companies/{company}/edit', [\App\Http\Controllers\Super\CompanyController::class, 'edit'])->name('companies.edit');
    Route::put('/companies/{company}', [\App\Http\Controllers\Super\CompanyController::class, 'update'])->name('companies.update');
    Route::get('/plans', \App\Livewire\Super\Plans::class)->name('plans');
    Route::get('/licenses', \App\Livewire\Super\Licenses::class)->name('licenses');
});

Route::middleware(['auth', 'role:company_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', \App\Livewire\Admin\Dashboard::class)->name('dashboard');
    Route::get('/parking-lots', \App\Livewire\Admin\ParkingLots::class)->name('parking-lots');
    Route::get('/operators', \App\Livewire\Admin\Operators::class)->name('operators');
    Route::get('/licenses', \App\Livewire\Admin\Licenses::class)->name('licenses');
    Route::get('/tariff', \App\Livewire\Admin\Tariff::class)->name('tariff');
    Route::get('/settings', \App\Livewire\Admin\Settings::class)->name('settings');
    Route::get('/shifts', \App\Livewire\Admin\Shifts::class)->name('shifts');
    Route::get('/closing', \App\Livewire\Admin\Closing::class)->name('closing');

    Route::post('/closing', [DailyClosingController::class, 'store'])->name('closing.store');
    Route::get('/closing/{date}/pdf', [DailyClosingController::class, 'pdf'])->name('closing.pdf');
    Route::get('/sessions/{session}/ticket', [DailyClosingController::class, 'ticket'])->name('sessions.ticket');
});
