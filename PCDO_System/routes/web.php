<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CooperativesController;
// use App\Http\Controllers\CoopHistoryController;
use App\Http\Controllers\CoopMemberController;
use App\Http\Controllers\CoopProgramController;
use App\Http\Controllers\CoopDetailsController;
use App\Http\Controllers\CoopProgramChecklistController;
use App\Http\Controllers\SyncController;

Route::get('/', function () {
    return redirect()->route('dashboard');
})->name('home');


Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    // Cooperatives Routes
    Route::resource('cooperatives', CooperativesController::class);
    Route::get('cooperatives/search', [CooperativesController::class, 'search'])->name('cooperatives.search');
    Route::get('cooperatives/export', [CooperativesController::class, 'export'])->name('cooperatives.export');
    Route::post('cooperatives/import', [CooperativesController::class, 'import'])->name('cooperatives.import');

    // Cooperatives Nested Routes
    // Route::get('cooperatives/{cooperative}/history', [CoopHistoryController::class, 'index'])->name('cooperatives.history');
    // Route::get('cooperatives/{cooperative}/members', [CoopMemberController::class, 'index'])->name('cooperatives.members');
    // Route::get('cooperatives/{cooperative}/programs', [CoopProgramController::class, 'index'])->name('cooperatives.programs');
    // Route::get('cooperatives/{cooperative}/details', [CoopDetailsController::class, 'show'])->name('cooperatives.details');


    // Cooperatives Programs Routes
    Route::resource('coopPrograms', CoopProgramController::class);
    Route::get('coopPrograms/search', [CoopProgramController::class, 'search'])->name('coopPrograms.search');
    Route::get('coopPrograms/export', [CoopProgramController::class, 'export'])->name('coopPrograms.export');
    Route::post('coopPrograms/import', [CoopProgramController::class, 'import'])->name('cooprograms.import');

    // Cooperatives Program Nested Routes
    Route::resource('coopPrograms/{cooperative}/checklists', CoopProgramChecklistController::class);

    // Custom Command Routes
    Route::get('/sync', [SyncController::class, 'sync'])->name('sync');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';