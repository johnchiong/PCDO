<?php

use App\Http\Controllers\CoopProgramController;
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

    Route::get('coop_program', [CoopProgramController::class, 'index'])->name('coop_program.index');
    Route::get('coop_program/create', [CoopProgramController::class, 'create'])->name('coop_program.create');
    Route::post('coop_program', [CoopProgramController::class, 'store'])->name('coop_program.store');

    Route::get('coop_program/{coop_program}/document', [CoopProgramController::class, 'document'])->name('coop_program.document');
    Route::post('coop_program/{coop_program}/upload', [CoopProgramController::class, 'storeUpload'])->name('checklist.upload');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
