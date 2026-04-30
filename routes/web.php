<?php

use App\Http\Controllers\ActiviteController;
use App\Http\Controllers\AdherentController;
use App\Http\Controllers\RessourcerieController;
use App\Http\Controllers\AdherentFormulaireController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BackOfficeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DossierActiviteController;
use App\Http\Controllers\InscriptionController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StatistiqueController;
use App\Livewire\EditUser;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HelloAssoController;
use App\Http\Controllers\RechercheController;
use Illuminate\Support\Facades\Artisan;

Route::get('/inscription/{token}', [InscriptionController::class, 'show'])->name('inscription');
Route::post('/inscription/{token}', [InscriptionController::class, 'complete'])->name('inscription.complete');

Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/', [AuthController::class, 'login'])->name('login.submit');

Route::get('/mot-de-passe-oublie', [PasswordResetController::class, 'showForgot'])->name('password.forgot');
Route::post('/mot-de-passe-oublie', [PasswordResetController::class, 'sendReset'])->name('password.send');
Route::get('/reinitialiser/{token}/{email}', [PasswordResetController::class, 'showReset'])->name('password.reset');
Route::post('/reinitialiser', [PasswordResetController::class, 'reset'])->name('password.update');

Route::get('/adhesion', [AdherentFormulaireController::class, 'index'])->name('adhesion.index');
Route::post('/adhesion/recup-numero', [AdherentFormulaireController::class, 'envoyerCodeRecup'])->name('adhesion.recup');
Route::get('/adhesion/{token}',  [AdherentFormulaireController::class, 'show'])->name('adhesion.show');
Route::post('/adhesion/{token}', [AdherentFormulaireController::class, 'next'])->name('adhesion.next');
Route::post('/adhesion/{token}/choix-saison', [AdherentFormulaireController::class, 'setSaisonCible'])->name('adhesion.setSaison');
Route::post('/adhesion/{token}/notifier-activite', [AdherentFormulaireController::class, 'notifierActivitePleine'])->name('adhesion.notifier.activite');

Route::get('/adhesion/{token}/helloasso/{status}', [HelloAssoController::class, 'helloassoReturn'])->name('adhesion.helloasso.return');
Route::post('/adhesion/{token}/helloasso2', [HelloAssoController::class, 'helloassoCheckout2'])->name('adhesion.helloasso2.checkout');
Route::get('/adhesion/{token}/helloasso2/{status}', [HelloAssoController::class, 'helloassoReturn2'])->name('adhesion.helloasso2.return');
Route::post('/adhesion/{token}/verifier-cotisation', [HelloAssoController::class, 'verifierCotisation'])->name('adhesion.verifier.cotisation');
Route::post('/adhesion/helloasso/webhook', [HelloAssoController::class, 'helloassoWebhook'])->name('adhesion.helloasso.webhook');

Route::post('/helloasso/webhook', [AdherentFormulaireController::class, 'helloassoWebhook']);

Route::get('/cron/bH4U$m+=vc5fpJf', function () {
    try {
        Artisan::call('schedule:run');
        return "Planificateur exécuté avec succès.";
    } catch (\Exception $e) {
        return 'Erreur : ' . $e->getMessage();
    }
})->middleware('throttle:2,1');

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/send-mail', [DashboardController::class, 'envoyerMailAdherents'])->name('dashboard.send-mail');

    Route::post('/seances/{seance}/appel', [DashboardController::class, 'enregistrerAppel'])->name('seances.appel');
    Route::post('/seances/{seance}/terminer', [DashboardController::class, 'terminerSeance'])->name('seances.terminer');


    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::middleware('role:admin')->group(function () {
        Route::get('/backoffice', [BackOfficeController::class, 'index'])->name('backoffice');
        Route::get('/backoffice/user/{user}/edit', EditUser::class)->name('user.edit');
        Route::delete('backoffice/bulk', [BackofficeController::class, 'destroyMultiple'])->name('backoffice.destroyMultiple');
        Route::delete('/backoffice/users/{user}', [BackOfficeController::class, 'destroy'])->name('backoffice.destroy');
    });

    Route::get('/adherents', [AdherentController::class, 'index'])->name('adherents.index');
    Route::get('/adherents/{adherent}', [AdherentController::class, 'show'])->name('adherents.show');
    Route::post('/adherents/{adherent}/fichiers', [AdherentController::class, 'uploaderFichiers'])->name('adherents.fichiers');
    Route::post('/adherents/{adherent}/valider', [AdherentController::class, 'valider'])->name('adherents.valider');
    Route::post('/adherents/{adherent}/versement', [AdherentController::class, 'ajouterVersement'])->name('adherents.versement');
    Route::get('/adherents/{adherent}/pdf', [AdherentController::class, 'downloadPdf'])->name('adherents.pdf');
    Route::put('/adherents/{adherent}/update-fiche', [AdherentController::class, 'updateFiche'])->name('adherents.update-fiche');

    Route::get('/structures/{structure}', [AdherentController::class, 'showStructure'])->name('structures.show');
    Route::post('/structures/{structure}/valider', [AdherentController::class, 'validerStructure'])->name('structures.valider');
    Route::get('/structures/{structure}/pdf', [AdherentController::class, 'downloadPdfStructure'])->name('structures.pdf');

    Route::get('/activites', [ActiviteController::class, 'index'])->name('activites.index');
    Route::get('/activites/create', [ActiviteController::class, 'create'])->middleware('role:admin,coordinateur')->name('activites.create');
    Route::post('/activites', [ActiviteController::class, 'store'])->middleware('role:admin,coordinateur')->name('activites.store');
    Route::get('/activites/{activite}', [ActiviteController::class, 'show'])->name('activites.show');
    Route::get('/activites/{activite}/edit', [ActiviteController::class, 'edit'])->middleware('role:admin,coordinateur')->name('activites.edit');
    Route::put('/activites/{activite}', [ActiviteController::class, 'update'])->middleware('role:admin,coordinateur')->name('activites.update');
    Route::post('/activites/{activite}/toggle-archive', [ActiviteController::class, 'toggleArchive'])->middleware('role:admin,coordinateur')->name('activites.toggleArchive');
    Route::delete('/activites/{activite}/seances/{seance}/annuler', [ActiviteController::class, 'annulerSeance'])->name('seances.annuler');
    Route::post('/activites/{activite}/seances/{seance}/presences', [ActiviteController::class, 'storePresences'])->name('activites.presences.store');
    Route::post('/activites/{activite}/adherents/{adherent}/abandon', [ActiviteController::class, 'abandonner'])->name('activites.abandonner');

    Route::get('/ressourcerie', [RessourcerieController::class, 'index'])->name('ressourcerie.index');
    Route::get('/ressourcerie/create', [RessourcerieController::class, 'create'])->middleware('role:admin,coordinateur')->name('ressourcerie.create');
    Route::post('/ressourcerie', [RessourcerieController::class, 'store'])->middleware('role:admin,coordinateur')->name('ressourcerie.store');
    Route::get('/ressourcerie/{ressourcerie}/edit', [RessourcerieController::class, 'edit'])->middleware('role:admin,coordinateur')->name('ressourcerie.edit');
    Route::put('/ressourcerie/{ressourcerie}', [RessourcerieController::class, 'update'])->middleware('role:admin,coordinateur')->name('ressourcerie.update');
    Route::post('/ressourcerie/{ressourcerie}/toggle-archive', [RessourcerieController::class, 'toggleArchive'])->middleware('role:admin,coordinateur')->name('ressourcerie.toggleArchive');

    Route::middleware('can:gerer-recherche')->group(function () {
        Route::get('/recherches', [RechercheController::class, 'index'])->name('recherches.index');
        Route::get('/recherches/create', [RechercheController::class, 'create'])->name('recherches.create');
        Route::post('/recherches', [RechercheController::class, 'store'])->name('recherches.store');
        Route::get('/recherches/{recherche}', [RechercheController::class, 'show'])->name('recherches.show');
        Route::get('/recherches/{recherche}/edit', [RechercheController::class, 'edit'])->name('recherches.edit');
        Route::put('/recherches/{recherche}', [RechercheController::class, 'update'])->name('recherches.update');
        Route::post('/recherches/{recherche}/archive', [RechercheController::class, 'toggleArchive'])->name('recherches.toggleArchive');
    });

    Route::post('/dossiers-activite', [DossierActiviteController::class, 'store'])->name('dossiers.store');
    Route::delete('/dossiers-activite/{dossier}', [DossierActiviteController::class, 'destroy'])->name('dossiers.destroy');

    Route::get('/profil/modifier', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profil/logs-synchronisation', [ProfileController::class, 'journalSync'])->middleware('role:admin')->name('profile.logs');

    Route::get('/parametres', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/parametres', [SettingController::class, 'update'])->name('settings.update');

    Route::get('/statistiques', [StatistiqueController::class, 'index'])->middleware('role:admin,comptable')->name('statistiques.index');

    Route::get('/users/search', [ActiviteController::class, 'searchUsers'])->name('users.search');
});
