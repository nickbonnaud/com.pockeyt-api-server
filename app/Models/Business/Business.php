<?php

namespace App\Models\Business;

use Illuminate\Support\Str;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use App\Notifications\Business\ResetPassword;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Business extends Authenticatable implements JWTSubject {

	//////////////////// Traits ////////////////////

	use Notifiable;
	use \BinaryCabin\LaravelUUID\Traits\HasUUID;

	//////////////////// Attribute Mods/Helpers ////////////////////

	protected $fillable = ['email', 'password', 'remember_token'];
	protected $hidden = ['password', 'remember_token', 'email_verified_at', 'id'];
	protected $casts = ['email_verified_at' => 'datetime'];
	protected $uuidFieldName = 'identifier';

	//////////////////// Routing ////////////////////

	public function getRouteKeyName() {
		return 'identifier';
	}

	//////////////////// Relationships ////////////////////

	public function profile() {
		return $this->hasOne('App\Models\Business\Profile');
	}

	public function account() {
		return $this->hasOne('App\Models\Business\Account');
	}

	public function location() {
		return $this->hasOne('App\Models\Business\Location');
	}

	public function inventory() {
		return $this->hasOne('App\Models\Business\Inventory');
	}

	public function posAccount() {
		return $this->hasOne('App\Models\Business\PosAccount');
	}

	public function transactions() {
		return $this->hasMany('App\Models\Transaction\Transaction');
	}

	public function unassignedTransactions() {
		return $this->hasMany('App\Models\Transaction\UnassignedTransaction');
	}

	public function employees() {
		return $this->hasMany('App\Models\Business\Employee');
	}

	public function messages() {
		return $this->hasMany('App\Models\Business\BusinessMessage');
	}

	//////////////////// Relationship Methods ////////////////////

	public function storeProfile($profile) {
		$this->profile()->save($profile);
		return $this->profile;
	}

	//////////////////// JWT Helpers ////////////////////

	public function getJWTIdentifier() {
		return $this->getKey();
	}

	public function getJWTCustomClaims() {
		return [];
	}

	//////////////////// Core Methods ////////////////////

	public static function register($credentials) {
		$business = Self::create($credentials);
		Self::login($credentials);
		return $business;
	}

	public static function login($credentials) {
		return auth('business')->claims(['csrf-token' => Self::createCsrfToken()])->attempt($credentials);
	}

	public static function logout() {
		auth('business')->logout();
	}

	public static function refreshToken($business) {
		auth('business')->invalidate();
		return auth('business')->claims(['csrf-token' => Self::createCsrfToken()])->login($business);
	}

	private static function createCsrfToken() {
		return Str::uuid();
	}

	public static function getAuthBusiness() {
		return auth('business')->user();
	}

	public function scopeFilter($query, $filters) {
		return $filters->apply($query);
	}

	//////////////////// Inherited Overrides Methods ////////////////////

	public function sendPasswordResetNotification($token){
    $this->notify(new ResetPassword($token));
	}
}
