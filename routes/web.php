<?php

use App\Http\Controllers\ActiviteController;
use App\Http\Controllers\AdherentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BackOfficeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InscriptionController;
use App\Http\Controllers\PasswordResetController;
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

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/backoffice', [BackOfficeController::class, 'index'])->name('backoffice');
    Route::get('/backoffice/user/{user}/edit', EditUser::class)->name('user.edit');
    Route::delete('backoffice/bulk', [BackofficeController::class, 'destroyMultiple'])->name('backoffice.destroyMultiple');
    Route::delete('/backoffice/users/{user}', [BackOfficeController::class, 'destroy'])->name('backoffice.destroy');

    Route::get('/adherents', [AdherentController::class, 'index'])->name('adherents.index');
    Route::get('/adherents/{adherent}', [AdherentController::class, 'show'])->name('adherents.show');
    Route::post('/adherents/{adherent}/commentaire', [AdherentController::class, 'commentaire'])->name('adherents.commentaire');
    Route::post('/adherents/{adherent}/valider', [AdherentController::class, 'valider'])->name('adherents.valider');

    Route::get('/activites', [ActiviteController::class, 'index'])->name('activites.index');
    Route::get('/activites/create', [ActiviteController::class, 'create'])->name('activites.create');
    Route::post('/activites', [ActiviteController::class, 'store'])->name('activites.store');
    Route::get('/activites/{activite}', [ActiviteController::class, 'show'])->name('activites.show');
    Route::post('/activites/{activite}/seances/{seance}/presences', [ActiviteController::class, 'storePresences'])->name('activites.presences.store');
    Route::get('/activites/{activite}/edit', [ActiviteController::class, 'edit'])->name('activites.edit');
    Route::put('/activites/{activite}', [ActiviteController::class, 'update'])->name('activites.update');
    Route::post('/activites/{activite}/toggle-archive', [ActiviteController::class, 'toggleArchive'])->name('activites.toggleArchive');
});
