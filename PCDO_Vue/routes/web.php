<?php

use App\Http\Controllers\CooperativeController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('cooperative', [CooperativeController::class, 'index'])->name('cooperative.index');
    Route::get('cooperative/create', [CooperativeController::class, 'create'])->name('cooperative.create');
    Route::post('cooperative', [CooperativeController::class, 'store'])->name('cooperative.store');

    Route::get('cooperative/{cooperative}/document', [CooperativeController::class, 'document'])->name('cooperative.document');
    Route::post('cooperative/{cooperative}/upload', [CooperativeController::class, 'storeUpload'])->name('checklist.upload');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
