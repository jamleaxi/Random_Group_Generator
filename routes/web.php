<?php

use App\Http\Controllers\BatchController;
use App\Http\Controllers\SettingController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/batches');

Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');

Route::get('/batches', [BatchController::class, 'index'])->name('batches.index');
Route::get('/batches/create', [BatchController::class, 'create'])->name('batches.create');
Route::post('/batches', [BatchController::class, 'store'])->name('batches.store');
Route::get('/batches/{batch}', [BatchController::class, 'show'])->name('batches.show');
Route::delete('/batches/{batch}', [BatchController::class, 'destroy'])->name('batches.destroy');
Route::patch('/batches/{batch}/lock', [BatchController::class, 'toggleLock'])->name('batches.lock');
Route::post('/batches/{batch}/participants', [BatchController::class, 'storeParticipants'])->name('batches.participants.store');
