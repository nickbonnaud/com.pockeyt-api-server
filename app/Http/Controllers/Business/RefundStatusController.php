<?php

namespace App\Http\Controllers\Business;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Refund\RefundStatus;
use App\Http\Resources\Business\StatusResource;

class RefundStatusController extends Controller {
  
  public function __construct() {
  	$this->middleware('auth:business');
  }

  public function index(Request $request) {
  	return StatusResource::collection(RefundStatus::all());
  }
}
