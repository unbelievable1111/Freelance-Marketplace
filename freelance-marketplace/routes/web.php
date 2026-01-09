<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

#Auth
Auth::routes();
Route::middleware('auth')->group(function () {
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])
        ->name('profile.password.update');
});

#HomeController
Route::get('/', [HomeController::class, 'index'])->name('home.index');

#ProfileController
Route::get('/my-profile', [ProfileController::class, 'index'])->name('profile.index');
