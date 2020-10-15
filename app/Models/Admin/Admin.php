<?php

namespace App\Models\Admin;

use App\Models\Admin\Role;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable implements JWTSubject {

	//////////////////// Traits ////////////////////

	use Notifiable;
	use \BinaryCabin\LaravelUUID\Traits\HasUUID;

	//////////////////// Attribute Mods/Helpers ////////////////////

	protected $fillable = ['email', 'password', 'role_id', 'remember_token', 'approved'];
	protected $hidden = ['password', 'remember_token', 'id', 'role_id', 'created_at', 'updated_at'];
	protected $uuidFieldName = 'identifier';

	//////////////////// Routing ////////////////////

	public function getRouteKeyName() {
		return 'identifier';
	}

	//////////////////// JWT Helpers ////////////////////

	public function getJWTIdentifier() {
		return $this->getKey();
	}

	public function getJWTCustomClaims() {
		return [];
	}

	//////////////////// Relationships ////////////////////

	public function role() {
		return $this->belongsTo('App\Models\Admin\Role');
	}

	public function helpTickets() {
		return $this->hasMany('App\Models\Customer\HelpTicket');
	}

	//////////////////// Core Methods ////////////////////

	public static function login($credentials) {
		if (!auth('admin')->validate($credentials)) {
			return null;
		}
		$admin = Admin::where('email', $credentials['email'])->first();
		$token = auth('admin')->login($admin);
		$admin['token'] = $token;
		return $admin;
	}

	public static function createAdmin($credentials, $roleCode) {
		$role = Role::where('code', $roleCode)->first();
		$credentials['role_id'] = $role->id;
		$admin = Self::create($credentials);
		return $admin;
	}

	public static function logout() {
		auth('admin')->logout();
	}

	public static function getAuthAdmin() {
		return auth('admin')->user();
	}

	public function createToken() {
		try {
			if (!$token = auth('admin')->login($this)) 
				return null;
		} catch(Exceptions\JWTException $e) {
			return null;
		}
		return $token;
	}

	public function updateToken() {
		return auth('admin')->refresh();
	}

	public static function getAdminByIdentifier($identifier) {
		return Self::where('identifier', $identifier)->first();
	}

	public function updateAdmin($updateData) {
		$updateData['approved'] ? $this->update($updateData) : $this->delete();
	}
}
