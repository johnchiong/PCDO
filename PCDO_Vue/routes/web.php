<?php

use App\Http\Controllers\CooperativeController;
use App\Http\Controllers\PaymentsController;
use App\Http\Controllers\NotificationController;
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
    //Cooperative Routes
    Route::get('cooperative', [CooperativeController::class, 'index'])->name('cooperative.index');
    Route::get('cooperative/create', [CooperativeController::class, 'create'])->name('cooperative.create');
    Route::post('cooperative', [CooperativeController::class, 'store'])->name('cooperative.store');
    Route::get('cooperative/{cooperative}/document', [CooperativeController::class, 'document'])->name('cooperative.document');
    Route::post('cooperative/{cooperative}/upload', [CooperativeController::class, 'storeUpload'])->name('checklist.upload');
    Route::get('/cooperative/uploads/{upload}/download', [CooperativeController::class, 'download'])->name('cooperative.uploads.download');
    Route::delete('/cooperative/uploads/{upload}', [CooperativeController::class, 'destroyUpload'])->name('cooperative.uploads.destroy');
    Route::post('/cooperative/{cooperative}/loan', [CooperativeController::class, 'storeLoan'])->name('cooperative.loan.store');

    //Payment Routes
    Route::get('payments', [PaymentsController::class, 'index'])->name('payments.index');
    Route::get('/payments/{loanId}/amortization', [PaymentsController::class, 'amortization'])->name('payments.amortization');
    Route::post('/schedules/{scheduleId}/penalty', [PaymentsController::class, 'penalty'])->name('schedules.penalty');
    Route::post('/schedules/{scheduleId}/mark-paid', [PaymentsController::class, 'markPaid'])->name('schedules.markPaid');
    Route::post('/loans/{loanId}/send-notif', [PaymentsController::class, 'sendNotification'])->name('loans.sendNotification');

    //Notification Routes
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
