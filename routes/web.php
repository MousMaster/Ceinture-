<?php

use App\Http\Controllers\PdfController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

// Routes PDF (protégées par authentification)
Route::middleware(['auth'])->group(function () {
    Route::get('/pdf/permanence/{permanence}/download', [PdfController::class, 'downloadPermanence'])
        ->name('pdf.permanence.download');
    Route::get('/pdf/permanence/{permanence}/stream', [PdfController::class, 'streamPermanence'])
        ->name('pdf.permanence.stream');
});
