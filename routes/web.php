<?php

use App\Http\Controllers\Auth\SessionController;
use App\Http\Controllers\BatchController;
use App\Http\Controllers\JoinController;
use App\Http\Controllers\PublicGroupsController;
use App\Http\Controllers\SettingController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::get('/login', [SessionController::class, 'create'])->name('login')->middleware('guest');
Route::post('/login', [SessionController::class, 'store'])->middleware('guest');
Route::post('/logout', [SessionController::class, 'destroy'])->name('logout');

Route::get('/join/{batch:public_token}', [JoinController::class, 'show'])->name('join.show');
Route::post('/join/{batch:public_token}', [JoinController::class, 'store'])->name('join.store');

Route::get('/groups/{batch}', [PublicGroupsController::class, 'show'])->name('groups.public');

Route::middleware('auth')->group(function () {
    Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
    Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');

    Route::get('/batches', [BatchController::class, 'index'])->name('batches.index');
    Route::get('/batches/create', [BatchController::class, 'create'])->name('batches.create');
    Route::post('/batches', [BatchController::class, 'store'])->name('batches.store');
    Route::get('/batches/{batch}', [BatchController::class, 'show'])->name('batches.show');
    Route::patch('/batches/{batch}', [BatchController::class, 'update'])->name('batches.update');
    Route::delete('/batches/{batch}', [BatchController::class, 'destroy'])->name('batches.destroy');
    Route::patch('/batches/{batch}/lock', [BatchController::class, 'toggleLock'])->name('batches.lock');
    Route::post('/batches/{batch}/link', [BatchController::class, 'openLink'])->name('batches.link.open');
    Route::delete('/batches/{batch}/link', [BatchController::class, 'closeLink'])->name('batches.link.close');
    Route::post('/batches/{batch}/participants', [BatchController::class, 'storeParticipants'])->name('batches.participants.store');
    Route::patch('/batches/{batch}/participants/{participant}/transfer/{groupTeam}', [BatchController::class, 'transferParticipant'])->name('batches.participants.transfer');
    Route::delete('/batches/{batch}/participants/{participant}', [BatchController::class, 'destroyParticipant'])->name('batches.participants.destroy');
});
