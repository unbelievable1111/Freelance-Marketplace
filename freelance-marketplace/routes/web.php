<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () { return view('welcome'); });

//example
//Route::get('/yt-profiles', [YtProfileController::class, 'index'])->name('yt-profiles.index');