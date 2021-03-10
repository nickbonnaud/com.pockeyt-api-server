<?php

namespace App\Http\Controllers\Business;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Transaction\TransactionStatus;
use App\Http\Resources\Business\StatusResource;

class TransactionStatusController extends Controller {

  public function __construct() {
  	$this->middleware('auth:business');
    $this->middleware('csrf');
  }

  public function index(Request $request) {
  	return StatusResource::collection(TransactionStatus::all());
  }
}
