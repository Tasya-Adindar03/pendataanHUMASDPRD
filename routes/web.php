<?php

use App\Http\Controllers\AdvertisingPlanController;
use App\Http\Controllers\AdvertorialController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\AdminOnly;
use App\Http\Middleware\SyncOverdueStatus;
use Illuminate\Support\Facades\Route;

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:5,1');

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth', SyncOverdueStatus::class])->group(function () {

    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('advertorials')->name('advertorials.')->group(function () {
        Route::get('/', [AdvertorialController::class, 'index'])->name('index');
        Route::get('/create', [AdvertorialController::class, 'create'])->name('create');
        Route::post('/', [AdvertorialController::class, 'store'])->name('store');
        Route::get('/{advertorial}/edit', [AdvertorialController::class, 'edit'])->name('edit');
        Route::put('/{advertorial}', [AdvertorialController::class, 'update'])->name('update');
        Route::delete('/{advertorial}', [AdvertorialController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('companies')->name('companies.')->group(function () {
        Route::get('/', [CompanyController::class, 'index'])->name('index');
        Route::get('/create', [CompanyController::class, 'create'])->name('create');
        Route::post('/', [CompanyController::class, 'store'])->name('store');
        Route::get('/{company}/edit', [CompanyController::class, 'edit'])->name('edit');
        Route::put('/{company}', [CompanyController::class, 'update'])->name('update');
        Route::delete('/{company}', [CompanyController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('media')->name('media.')->group(function () {
        Route::get('/', [MediaController::class, 'index'])->name('index');
        Route::get('/create', [MediaController::class, 'create'])->name('create');
        Route::post('/', [MediaController::class, 'store'])->name('store');

        // Kuota (menggantikan halaman Planning Iklan)
        Route::post('/{medium}/kuota', [AdvertisingPlanController::class, 'store'])->name('kuota.store');
        Route::put('/kuota/{plan}', [AdvertisingPlanController::class, 'update'])->name('kuota.update');
        Route::delete('/kuota/{plan}', [AdvertisingPlanController::class, 'destroy'])->name('kuota.destroy');

        Route::get('/{medium}', [MediaController::class, 'show'])->name('show');
        Route::get('/{medium}/edit', [MediaController::class, 'edit'])->name('edit');
        Route::put('/{medium}', [MediaController::class, 'update'])->name('update');
        Route::delete('/{medium}', [MediaController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('export')->name('export.')->group(function () {
        Route::get('/pdf', [ExportController::class, 'pdf'])->name('pdf');
        Route::get('/excel', [ExportController::class, 'excel'])->name('excel');
    });

    // Khusus Admin
    Route::middleware(AdminOnly::class)->group(function () {
        Route::resource('users', UserController::class)->except(['show']);
    });
});