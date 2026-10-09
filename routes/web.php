<?php

use App\Http\Controllers\ContractController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserAccessController;
use App\Http\Controllers\ContractingController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;
Route::middleware('guest')->group(function () { Route::get('/connexion', [AuthController::class, 'create'])->name('login'); Route::post('/connexion', [AuthController::class, 'store'])->name('login.store'); });
Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/mes-taches', [DashboardController::class, 'myTasks'])->name('tasks.index');
    Route::get('/mes-taches/export', [DashboardController::class, 'exportMyTasks'])->name('tasks.export');
    Route::get('/history', [DashboardController::class, 'history'])->name('tasks.history');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread', [NotificationController::class, 'unread'])->name('notifications.unread');
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::get('/contracting', [ContractingController::class, 'index'])->name('contracting.index');
    Route::get('/contracting/export', [ContractingController::class, 'export'])->name('contracting.export');
    Route::get('/contracting/history', [ContractingController::class, 'history'])->name('contracting.history');
    Route::get('/contracting/to-configuration', [ContractingController::class, 'toConfiguration'])->name('contracting.to-configuration');
    Route::patch('/contracting/to-configuration/{platform}', [ContractingController::class, 'updateToConfiguration'])->name('contracting.to-configuration.update');
    Route::post('/contracting/hotels', [ContractingController::class, 'storeHotel'])->name('contracting.hotels.store');
    Route::post('/contracting/periods', [ContractingController::class, 'storePeriod'])->name('contracting.periods.store');
    Route::patch('/contracting/periods/{season}', [ContractingController::class, 'updatePeriod'])->name('contracting.periods.update');
    Route::delete('/contracting/periods/{season}', [ContractingController::class, 'destroyPeriod'])->name('contracting.periods.destroy');
    Route::patch('/contracting/{hotel}/{season}', [ContractingController::class, 'updateEntry'])->name('contracting.entries.update');
    Route::get('/contracting-suivi', [ContractingController::class, 'stats'])->name('contracting.stats');
    Route::prefix('api')->group(function () {
        Route::get('/contracting/entries', [ContractingController::class, 'entries']);
        Route::post('/contracting/hotels', [ContractingController::class, 'storeHotel']);
        Route::patch('/contracting/entries/{hotel}/{season}', [ContractingController::class, 'updateEntry']);
        Route::get('/admin/contracting-stats', [ContractingController::class, 'stats']);
    });
    Route::get('/mon-compte/mot-de-passe', [AuthController::class, 'editPassword'])->name('password.edit');
    Route::put('/mon-compte/mot-de-passe', [AuthController::class, 'updatePassword'])->name('password.update');
    Route::get('/contrats/nouveau', [ContractController::class, 'create'])->name('contracts.create');
    Route::post('/contrats', [ContractController::class, 'store'])->name('contracts.store');
    Route::delete('/contrats/{contract}', [ContractController::class, 'destroy'])->name('contracts.destroy');
    Route::patch('/taches/{task}', [ContractController::class, 'updateTask'])->name('tasks.update');
    Route::get('/access', [UserAccessController::class, 'index'])->name('access.index');
    Route::post('/access', [UserAccessController::class, 'store'])->name('access.store');
    Route::put('/access/{user}', [UserAccessController::class, 'update'])->name('access.update');
    Route::post('/deconnexion', [AuthController::class, 'destroy'])->name('logout');
});
