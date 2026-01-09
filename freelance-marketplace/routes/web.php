<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Auth::routes();

Route::get('/', [HomeController::class, 'index'])->name('home.index');

//example
//Route::get('/yt-profiles', [YtProfileController::class, 'index'])->name('yt-profiles.index');