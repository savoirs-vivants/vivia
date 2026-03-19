<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InscriptionController;
use App\Http\Controllers\PasswordResetController;
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
});
