<?php

namespace App\Models\Business;

use Carbon\Carbon;
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

	public function loyaltyProgram() {
		return $this->hasOne('App\Models\Business\LoyaltyProgram');
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

	public function storeLoyaltyProgram($loyaltyProgram) {
		$this->loyaltyProgram()->save($loyaltyProgram);
		return $this->loyaltyProgram;
	}

	//////////////////// JWT Helpers ////////////////////

	public function getJWTIdentifier() {
		return $this->getKey();
	}

	public function getJWTCustomClaims() {
		return [];
	}

	//////////////////// Core Methods ////////////////////

	public static function login($credentials) {
		if (!auth('business')->validate($credentials)) {
      return ['token' => null, 'error' => 'invalid_credentials', 'code' => 401]; 
    }
    $business = Business::where('email', $credentials['email'])->first();
    return self::createToken($business);
	}

	public static function logout() {
		auth('business')->logout();
		return ['token' => null, 'error' => null, 'code' => 200];
	}

	public static function refreshToken() {
		return auth('business')->refresh();
	}

	public static function updateToken() {
		$token = self::refreshToken();
		return ['token' => $token, 'error' => null, 'code' => 200];
	}

	public static function createToken($business) {
		try {
  		if (!$token = auth('business')->login($business))
  			return ['token' => null, 'error' => 'invalid_credentials', 'code' => 401]; 
  	} catch(Exceptions\JWTException $e) {
  		return ['token' => null, 'error' => 'could_not_create_token', 'code' => 500];
  	}
  	return ['token' => $token, 'error' => null, 'code' => 200];
	}

	public static function getAuthBusiness() {
		return auth('business')->user();
	}

	//////////////////// Formatting Methods ////////////////////

	public static function formatToken($token = null) {
		return [
      'value' => $token,
      'token_type' => 'bearer',
      'expiry' => $token ? Carbon::now()->addMinutes(auth('business')->factory()->getTTL())->timestamp : null
    ];
	}

	//////////////////// Inherited Overrides Methods ////////////////////

	public function sendPasswordResetNotification($token){
    $this->notify(new ResetPassword($token));
	}
}
