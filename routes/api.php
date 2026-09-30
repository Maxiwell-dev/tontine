<?php
use App\Http\Controllers\Api\CotisationController;
use App\Http\Controllers\Api\DistributionController;
use App\Http\Controllers\Api\MembreController;
use Illuminate\Support\Facades\Route;

// --- ESPACE MEMBRE (STANDARD) ---
Route::post('/cotisations/soumettre', [CotisationController::class, 'soumettreCotisation']);
Route::get('/mes-cotisations/{id_membre}', [CotisationController::class, 'mesCotisations']);

// --- ESPACE ADMIN ---
// Notification & Validation des cotisations
Route::get('/admin/cotisations/en-attente', [CotisationController::class, 'cotisationsEnAttente']);
Route::patch('/admin/cotisations/{id_cotisation}/valider', [CotisationController::class, 'validerCotisation']);

// Suivi global & Désignation du bénéficiaire
Route::get('/cotisations/statut', [CotisationController::class, 'statutMoisGlobal']);
Route::post('/distributions/designer', [DistributionController::class, 'designerBeneficiaire']);

// Gestion des membres
Route::get('/membres', [MembreController::class, 'index']);
Route::post('/membres', [MembreController::class, 'store']);