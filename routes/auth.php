<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

/*
|--------------------------------------------------------------------------
| AUTH ROUTES - PRIVATE ERP
|--------------------------------------------------------------------------
| Application interne uniquement
| - Pas d'inscription publique
| - Pas de reset password public
| - Pas de vérification email
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {

    Volt::route('login', 'pages.auth.login')
        ->name('login');

});


Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | LOGOUT (si nécessaire)
    |--------------------------------------------------------------------------
    */
    Route::post('logout', function () {
        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login');
    })->name('logout');

});