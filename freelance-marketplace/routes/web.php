<?php

use App\Http\Controllers\BankAccountController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

#Auth
Auth::routes();

#HomeController
Route::get('/', [HomeController::class, 'index'])->name('home.index');

#ProfileController
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
});

#BankAccountController
Route::middleware('auth')->group(function () {
    Route::get('/profile/bank-accounts', [BankAccountController::class, 'index'])->name('profile.bank-accounts');
    Route::delete('/profile/bank-accounts/delete/{bankAccount}', [BankAccountController::class, 'deleteCard'])->name('profile.bank-accounts.delete-card');
    Route::post('/profile/bank-accounts/create', [BankAccountController::class, 'createCard'])->name('profile.bank-accounts.create-card');
});

