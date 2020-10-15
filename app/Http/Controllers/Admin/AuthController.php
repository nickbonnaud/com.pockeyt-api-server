<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\StoreAdminRequest;
use App\Http\Requests\Admin\LoginAdminRequest;
use App\Http\Resources\Admin\AdminResource;

class AuthController extends Controller {

	public function __construct() {
  	$this->middleware('auth:admin')->only(['logout', 'refresh']);
  }

	public function register(StoreAdminRequest $request) {
		$admin = Admin::createAdmin($request->only(['email', 'password']), $request->role_code);
		$admin['token'] = $admin->createToken();
		return new AdminResource($admin);
	}

	public function login(LoginAdminRequest $request) {
		if (!$admin = Admin::login($request->validated())) {
			return response()->json([
				'message' => "The given data was invalid.",
				'errors' => ['login' => array('Incorred email or password.')]
			], 401);
		}
		return new AdminResource($admin);
	}

	public function logout() {
		Admin::logout();
		return response()->json([
			'message' => "Success."
		], 200);
	}

	public function refresh() {
		$admin = Admin::getAuthAdmin();
		$admin['token'] = $admin->updateToken();
		return new AdminResource($admin);
	}
}
