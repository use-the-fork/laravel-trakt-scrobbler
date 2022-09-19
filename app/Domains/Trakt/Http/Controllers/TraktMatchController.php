<?php

namespace App\Domains\Trakt\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TraktMatchController extends Controller
{

    public function show($type)
    {
        return view('domains.trakt.match.index', ['type' => $type]);
    }
}
