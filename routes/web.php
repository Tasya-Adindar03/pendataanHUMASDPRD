<?php

use App\Http\Controllers\AdvertisingPlanController;
use App\Http\Controllers\AdvertorialController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DashboardTwoController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\MediaController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard-2', [DashboardTwoController::class, 'index'])->name('dashboard.two');

Route::prefix('advertorials')->name('advertorials.')->group(function () {
    Route::get('/', [AdvertorialController::class, 'index'])->name('index');
    Route::get('/media', [AdvertorialController::class, 'media'])->name('media');
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
    Route::get('/{medium}/edit', [MediaController::class, 'edit'])->name('edit');
    Route::put('/{medium}', [MediaController::class, 'update'])->name('update');
    Route::delete('/{medium}', [MediaController::class, 'destroy'])->name('destroy');
});

Route::prefix('plans')->name('plans.')->group(function () {
    Route::get('/', [AdvertisingPlanController::class, 'index'])->name('index');
    Route::get('/create', [AdvertisingPlanController::class, 'create'])->name('create');
    Route::post('/', [AdvertisingPlanController::class, 'store'])->name('store');
    Route::get('/{plan}', [AdvertisingPlanController::class, 'show'])->name('show');
    Route::get('/{plan}/edit', [AdvertisingPlanController::class, 'edit'])->name('edit');
    Route::put('/{plan}', [AdvertisingPlanController::class, 'update'])->name('update');
    Route::delete('/{plan}', [AdvertisingPlanController::class, 'destroy'])->name('destroy');
});

Route::prefix('export')->name('export.')->group(function () {
    Route::get('/pdf', [ExportController::class, 'pdf'])->name('pdf');
    Route::get('/excel', [ExportController::class, 'excel'])->name('excel');
});