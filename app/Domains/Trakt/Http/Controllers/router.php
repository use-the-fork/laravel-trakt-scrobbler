<?php

namespace App\Domains\Trakt\Http\Controllers;

use Illuminate\Support\Facades\Route;

Route::group([
    'prefix'     => 'trakt',
], function () {
    Route::get('match/{type}', [
        TraktMatchController::class,
        'show'
    ])->name('trakt.match');
});
