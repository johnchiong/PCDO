<?php

use App\Http\Controllers\AmortizationScheduleController;
use App\Http\Controllers\CooperativesController;
use App\Http\Controllers\CoopMemberController;
use App\Http\Controllers\CoopProgramChecklistController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\ResolvedController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
})->name('home');

Route::get('/ping', function () {
    try {
        DB::connection('mysql_cloud')->select('SELECT 1');

        return response()->json(['status' => 'online']);
    } catch (\Throwable $e) {
        return response()->json(['status' => 'offline'], 503);
    }
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Cooperatives Routes
    Route::resource('cooperatives', CooperativesController::class);
    Route::get('cooperatives/export/{type}', [CooperativesController::class, 'export'])->name('cooperatives.export');
    Route::post('cooperatives/import', [CooperativesController::class, 'import'])->name('cooperatives.import');

    // Cooperatives Nested Routes
    Route::resource('cooperatives.members', CoopMemberController::class);
    Route::get('cooperatives/{cooperative}/members/{member}/files/{fileId}/download', [CoopMemberController::class, 'downloadFile'])->name('cooperatives.members.files.download');
    Route::delete('cooperatives/{cooperative}/members/{member}/files/{fileId}', [CoopMemberController::class, 'deleteFile'])->name('cooperatives.members.files.delete');
    Route::get('cooperatives/{cooperative}', [CooperativesController::class, 'show'])->name('cooperatives.show');

    // Program Routes
    Route::resource('programs', ProgramController::class);

    // Nested routes for adding cooperatives to a program
    Route::get('/programs/{program}/cooperatives/create', [ProgramController::class, 'createCooperative'])->name('programs.cooperatives.create');
    Route::post('/programs/{program}/cooperatives', [ProgramController::class, 'storeCooperative'])->name('programs.cooperatives.store');

    // Nested routes for checklists under a specific program and cooperative
    Route::prefix('programs/{program}/cooperatives/{cooperative}')->group(function () {
        Route::get('checklist', [CoopProgramChecklistController::class, 'show'])->name('programs.cooperatives.checklist.show');
        Route::post('checklist/upload', [CoopProgramChecklistController::class, 'upload'])->name('programs.cooperatives.checklist.upload');
        Route::get('checklist/{file}/download', [CoopProgramChecklistController::class, 'download'])->name('programs.cooperatives.checklist.download');
        Route::delete('checklist/{file}', [CoopProgramChecklistController::class, 'delete'])->name('programs.cooperatives.checklist.delete');

        // Loan amount and Grace Period
        Route::post('finalize-loan', [ProgramController::class, 'finalizeLoan'])->name('cooperatives.finalizeLoan');
    });

    // Generating the Amortization Schedule
    Route::post('/cooperative-programs/{coopProgram}/generate-amortization', [AmortizationScheduleController::class, 'generateSchedule'])->name('cooperative-programs.generate-amortization');

    // Cooperatives Programs Routes
    // Route::resource('coopPrograms', CoopProgramController::class);
    // Route::get('coopPrograms/search', [CoopProgramController::class, 'search'])->name('coopPrograms.search');
    // Route::get('coopPrograms/export', [CoopProgramController::class, 'export'])->name('coopPrograms.export');
    // Route::post('coopPrograms/import', [CoopProgramController::class, 'import'])->name('cooprograms.import');

    // Payments Routes
    Route::get('amortizations', [AmortizationScheduleController::class, 'index'])->name('amortizations.index');
    Route::get('/amortizations/{coopProgram}', [AmortizationScheduleController::class, 'show'])->name('amortizations.show');
    Route::post('/schedules/{schedule}/mark-paid', [AmortizationScheduleController::class, 'markPaid'])->name('schedules.markPaid');
    Route::post('/schedules/{schedule}/note-payment', [AmortizationScheduleController::class, 'notePayment'])->name('schedules.notePayment');
    Route::post('/schedules/{schedule}/penalty', [AmortizationScheduleController::class, 'penalty'])->name('schedules.penalty');
    Route::post('/schedules/{schedule}/send-overdue-email', [AmortizationScheduleController::class, 'sendOverdueEmail'])->name('schedules.sendOverdueEmail');
    // Amortization Incomplete
    Route::get('/amortization/{loan}/download', [AmortizationScheduleController::class, 'downloadPdf'])->name('amortization.download');
    Route::post('/amortization/{loan}/incomplete', [AmortizationScheduleController::class, 'markIncomplete'])->name('loan.incomplete');
    Route::post('/amortization/{loan}/resolve', [AmortizationScheduleController::class, 'markResolved'])->name('loan.resolve');

    // Notification Routes
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('notifications/{id}', [NotificationController::class, 'show'])->name('notifications.show');
    Route::post('/schedules/{schedule}/send-notif', [AmortizationScheduleController::class, 'sendOverdueEmail'])->name('schedules.sendNotif');

    // Documentation Routes
    Route::get('/documentation', [DocumentationController::class, 'index'])->name('documentation.index');
    Route::get('/documentation/cooperative/{id}', [DocumentationController::class, 'show'])->name('documentation.show');
    Route::get('/documentation/{id}/amortization', [DocumentationController::class, 'amortizationFile'])->name('documentation.amortization');
    Route::get('/documentation/{id}/details', [DocumentationController::class, 'cooperativeDetailsFile'])->name('documentation.details');
    Route::get('/documentation/resolved/{id}/file', [DocumentationController::class, 'resolvedFile'])->name('documentation.resolved.file');
    Route::get('/documentation/checklist/{id}/file', [DocumentationController::class, 'checklistFile'])->name('documentation.checklist.file');

    // Resolved Routes
    Route::get('/resolved/{coopProgram}/upload', [ResolvedController::class, 'create'])->name('resolved.create');
    Route::post('/resolved/{coopProgram}', [ResolvedController::class, 'store'])->name('resolved.store');
    Route::get('/resolved/download/{id}', [ResolvedController::class, 'download'])->name('resolved.download');

    // Cooperatives Program Nested Routes
    // Route::resource('coopPrograms/{cooperative}/checklists', CoopProgramChecklistController::class);

    // Custom Command Routes
    Route::get('/sync', function () {
        Artisan::call('sync:database');

        return response()->json(['status' => 'synced']);
    });

});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
