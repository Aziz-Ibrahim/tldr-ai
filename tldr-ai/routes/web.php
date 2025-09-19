<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Services\SupabaseService;


// Show upload form
Route::get('/upload', function () {
    return view('upload');
});

// Handle upload POST request
Route::post('/upload', function (Request $request, SupabaseService $supabase) {
    $request->validate([
        'document' => 'required|file|max:10240', // max 10MB
    ]);

    $file = $request->file('document');
    $fileName = time() . '_' . $file->getClientOriginalName();

    // Upload to Supabase
    $supabase->uploadFile($file->getPathname(), $fileName);

    // Get public URL
    $url = $supabase->getFileUrl($fileName);

    return redirect('/upload')->with('success', 'File uploaded! Public URL: ' . $url);
});