<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

// Auth routes (login, register, etc.)
require __DIR__.'/auth.php';

// Routes protected by authentication
Route::middleware(['auth'])->group(function () {

    // Dashboard + document list
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Handle uploads from dashboard form
    Route::post('/dashboard/upload', [DashboardController::class, 'upload'])->name('dashboard.upload');

    // Breeze profile routes (edit profile, update password, delete account)
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [\App\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');
});
