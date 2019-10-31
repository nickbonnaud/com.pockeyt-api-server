<?php

namespace App\Models\Business;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class AchOwner extends Model {

  //////////////////// Attribute Mods/Helpers ////////////////////

	protected $guarded = ['owner_url'];
	protected $hidden = ['id', 'ach_account_id', 'ssn', 'type', 'owner_url'];
	protected $casts = ['dob' => 'date'];

	//////////////////// Relationships ////////////////////

	public function achAccount() {
		return $this->belongsTo('App\Models\Business\AchAccount');
	}

	//////////////////// Mutator Methods ////////////////////

	public function setSsnAttribute($value) {
		if (!Str::contains($value, 'X')) {
			$this->attributes['ssn'] = encrypt($value);
		}
	}

	//////////////////// Accessor Methods ////////////////////

	public function getSsnAttribute($value) {
		return decrypt($value);
	}

	//////////////////// Core Methods ////////////////////

	public static function storeData($achData) {
		self::create($achData);
	}

	public function updateData($achData) {
		$this->update($achData);
	}
}
