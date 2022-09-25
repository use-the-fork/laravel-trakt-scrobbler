<?php

namespace App\Domains\Trakt\Http\Controllers;

use Illuminate\Support\Facades\Route;
use App\Domains\Trakt\Http\Livewire\Match\Page;


Route::group([
    'prefix'     => 'trakt',
], function () {
    Route::get('match/{type}', Page::class)->name('trakt.match');
});
