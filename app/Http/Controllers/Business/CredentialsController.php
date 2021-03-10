<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CredentialsController extends Controller {
  
  public function __construct() {
  	$this->middleware('auth:business');
    $this->middleware('csrf');
  }

  public function index() {
  	return response()->json([
  		'data' => [
  			'google_key' => env('GOOGLE_API_KEY')
  		]
  	]);
  }
}
