<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Services\SupabaseService;

// Auth routes (login, register, etc.)
require __DIR__.'/auth.php';

// Routes protected by authentication
Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Upload routes
    Route::get('/upload', function () {
        return view('upload');
    })->name('upload');

    Route::post('/upload', function (Request $request, SupabaseService $supabase) {
        $request->validate([
            'document' => 'required|file|max:10240', // max 10MB
        ]);

        $file = $request->file('document');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $supabase->uploadFile($file->getPathname(), $fileName);

        $url = $supabase->getFileUrl($fileName);
        return redirect()->route('upload')->with('success', 'File uploaded! Public URL: ' . $url);
    });

    // Breeze profile routes (edit profile, update password, delete account)
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [\App\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');
});
