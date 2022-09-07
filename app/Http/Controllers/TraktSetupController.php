<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TraktSetupController extends Controller
{

	public function index(){

	}

	public function create(){
		return view('trakt/setup');
	}

	public function store(Request $request){

		$input = $request->all();

		Cache::put('trakt', json_encode([
			'username' => $input['username'],
			'password' => $input['password'],
										]));

		// username
		// password

	}
}
