<?php

use App\Http\Controllers\ActiviteController;
use App\Http\Controllers\AdherentController;
use App\Http\Controllers\AdherentFormulaireController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BackOfficeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DossierActiviteController;
use App\Http\Controllers\InscriptionController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StatistiqueController;
use App\Livewire\EditUser;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/inscription/{token}', [InscriptionController::class, 'show'])->name('inscription');
Route::post('/inscription/{token}', [InscriptionController::class, 'complete'])->name('inscription.complete');

Route::get('/connexion', [AuthController::class, 'showLogin'])->name('login');
Route::post('/connexion', [AuthController::class, 'login'])->name('login.submit');

Route::get('/mot-de-passe-oublie', [PasswordResetController::class, 'showForgot'])->name('password.forgot');
Route::post('/mot-de-passe-oublie', [PasswordResetController::class, 'sendReset'])->name('password.send');
Route::get('/reinitialiser/{token}/{email}', [PasswordResetController::class, 'showReset'])->name('password.reset');
Route::post('/reinitialiser', [PasswordResetController::class, 'reset'])->name('password.update');

Route::get('/adhesion', [AdherentFormulaireController::class, 'index'])->name('adhesion.index');
Route::post('/adhesion/recup-numero', [AdherentFormulaireController::class, 'envoyerCodeRecup'])->name('adhesion.recup');
Route::get('/adhesion/{token}',  [AdherentFormulaireController::class, 'show'])->name('adhesion.show');
Route::post('/adhesion/{token}', [AdherentFormulaireController::class, 'next'])->name('adhesion.next');
Route::get('/adhesion/{token}/helloasso/{status}', [AdherentFormulaireController::class, 'helloassoReturn'])->name('adhesion.helloasso.return');
Route::post('/adhesion/{token}/helloasso2', [AdherentFormulaireController::class, 'helloassoCheckout2'])->name('adhesion.helloasso2.checkout');
Route::get('/adhesion/{token}/helloasso2/{status}', [AdherentFormulaireController::class, 'helloassoReturn2'])->name('adhesion.helloasso2.return');
Route::post('/adhesion/{token}/verifier-cotisation', [AdherentFormulaireController::class, 'verifierCotisation'])->name('adhesion.verifier.cotisation');
Route::post('/adhesion/helloasso/webhook', [AdherentFormulaireController::class, 'helloassoWebhook'])->name('adhesion.helloasso.webhook');


Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/seances/{seance}/appel', [DashboardController::class, 'enregistrerAppel'])->name('seances.appel');
    Route::post('/seances/{seance}/terminer', [DashboardController::class, 'terminerSeance'])->name('seances.terminer');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/backoffice', [BackOfficeController::class, 'index'])->name('backoffice');
    Route::get('/backoffice/user/{user}/edit', EditUser::class)->name('user.edit');
    Route::delete('backoffice/bulk', [BackofficeController::class, 'destroyMultiple'])->name('backoffice.destroyMultiple');
    Route::delete('/backoffice/users/{user}', [BackOfficeController::class, 'destroy'])->name('backoffice.destroy');

    Route::get('/adherents', [AdherentController::class, 'index'])->name('adherents.index');
    Route::get('/adherents/{adherent}', [AdherentController::class, 'show'])->name('adherents.show');
    Route::post('/adherents/{adherent}/commentaire', [AdherentController::class, 'commentaire'])->name('adherents.commentaire');
    Route::post('/adherents/{adherent}/valider', [AdherentController::class, 'valider'])->name('adherents.valider');
    Route::get('/adherents/{adherent}/pdf', [AdherentController::class, 'downloadPdf'])->name('adherents.pdf');

    Route::get('/structures/{structure}', [AdherentController::class, 'showStructure'])->name('structures.show');
    Route::post('/structures/{structure}/valider', [AdherentController::class, 'validerStructure'])->name('structures.valider');
    Route::get('/structures/{structure}/pdf', [AdherentController::class, 'downloadPdfStructure'])->name('structures.pdf');

    Route::get('/activites', [ActiviteController::class, 'index'])->name('activites.index');
    Route::get('/activites/create', [ActiviteController::class, 'create'])->name('activites.create');
    Route::post('/activites', [ActiviteController::class, 'store'])->name('activites.store');
    Route::get('/activites/{activite}', [ActiviteController::class, 'show'])->name('activites.show');
    Route::delete('/activites/{activite}/seances/{seance}/annuler', [ActiviteController::class, 'annulerSeance'])->name('seances.annuler');
    Route::post('/activites/{activite}/seances/{seance}/presences', [ActiviteController::class, 'storePresences'])->name('activites.presences.store');
    Route::get('/activites/{activite}/edit', [ActiviteController::class, 'edit'])->name('activites.edit');
    Route::put('/activites/{activite}', [ActiviteController::class, 'update'])->name('activites.update');
    Route::post('/activites/{activite}/toggle-archive', [ActiviteController::class, 'toggleArchive'])->name('activites.toggleArchive');
    Route::post('/activites/{activite}/adherents/{adherent}/abandon', [ActiviteController::class, 'abandonner'])->name('activites.abandonner');

    Route::post('/dossiers-activite', [DossierActiviteController::class, 'store'])->name('dossiers.store');
    Route::delete('/dossiers-activite/{dossier}', [DossierActiviteController::class, 'destroy'])->name('dossiers.destroy');

    Route::get('/profil/modifier', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profil/logs-synchronisation', [ProfileController::class, 'logs'])->name('profile.logs');

    Route::get('/statistiques', [StatistiqueController::class, 'index'])->name('statistiques.index');

    Route::get('/users/search', [ActiviteController::class, 'searchUsers'])->name('users.search');

});
