<?php

use App\Http\Controllers\CooperativeController;
use App\Http\Controllers\PaymentsController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
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
    Route::get('/cooperative/uploads/{upload}/download', [CooperativeController::class, 'download'])->name('cooperative.uploads.download');
    Route::delete('/cooperative/uploads/{upload}', [CooperativeController::class, 'destroyUpload'])->name('cooperative.uploads.destroy');

    Route::get('payments', [PaymentsController::class, 'index'])->name('payments.index');
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
