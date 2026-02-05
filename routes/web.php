<?php

use App\Http\Controllers\LanguageController;
use App\Http\Controllers\PdfController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

// Route pour changer de langue
Route::get('/language/{locale}', [LanguageController::class, 'switch'])
    ->name('language.switch');

// Routes PDF (protégées par authentification)
Route::middleware(['auth'])->group(function () {
    Route::get('/pdf/permanence/{permanence}/download', [PdfController::class, 'downloadPermanence'])
        ->name('pdf.permanence.download');
    Route::get('/pdf/permanence/{permanence}/stream', [PdfController::class, 'streamPermanence'])
        ->name('pdf.permanence.stream');
});
