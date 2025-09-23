<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\DashboardController;

// Public routes (accessible without auth)
Route::get('/', [WelcomeController::class, 'index'])->name('welcome');

// About page
Route::get('/about', function () {
    return view('about');
})->name('about');

// Auth routes (login, register, etc.)
require __DIR__.'/auth.php';

// Routes protected by authentication
Route::middleware(['auth'])->group(function () {

    // Dashboard + document list
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Handle uploads from dashboard form
    Route::post('/dashboard/upload', [DashboardController::class, 'upload'])->name('dashboard.upload');

    // Generate summary for a document
    Route::post('/dashboard/generate-summary', [DashboardController::class, 'generateSummary'])->name('dashboard.generate-summary');

    // Delete document from dashboard
    Route::delete('/dashboard/delete', [DashboardController::class, 'delete'])->name('dashboard.delete');

    // Breeze profile routes (edit profile, update password, delete account)
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [\App\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');

    // Debug route to test Supabase connection
    Route::get('/debug-env', function() {
        return [
            'hf_token_exists' => !empty(env('HUGGINGFACE_API_KEY')),
            'hf_token_length' => strlen(env('HUGGINGFACE_API_KEY') ?? ''),
            'hf_token_first_chars' => substr(env('HUGGINGFACE_API_KEY') ?? '', 0, 10),
        ];
    })->middleware('auth');
});
