<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CredentialsController extends Controller {

	public function __construct() {
  	$this->middleware('auth:customer');
  }

  public function index() {
  	return response()->json([
  		'data' => [
  			'dwolla_key' => env('DWOLLA_API_KEY')
  		]
  	]);
  }
}
