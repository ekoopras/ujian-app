<?php

use App\Filament\App\Pages\Auth\RegisterSiswa;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['web'])
    ->get('/app/register', RegisterSiswa::class)
    ->name('filament.app.auth.register');
