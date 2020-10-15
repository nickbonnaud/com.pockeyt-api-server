<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\ApproveAdminRequest;
use App\Models\Admin\Role;
use App\Models\Admin\Admin;

class MasterAdminController extends Controller {

	public function __construct() {
  	$this->middleware('auth:admin');
  }

  public function approve(ApproveAdminRequest $request) {
  	if (!$this->isMasterAdmin()) {
  		return response()->json([
				'message' => "Invalid Permissions.",
				'errors' => ['admin' => array('Master Admin privileges required.')]
			], 422); 
  	}

  	Admin::getAdminByIdentifier($request->identifier)->updateAdmin($request->only('approved'));

  	return response()->json([
			'message' => "Success."
		], 200);
  }

  private function isMasterAdmin() {
  	return auth('admin')->user()->role->id == Role::where('code', 0)->first()->id;
  }
}
