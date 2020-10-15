<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class Role extends Model {

	//////////////////// Attribute Mods/Helpers ////////////////////

	protected $fillable = ['name', 'code'];
	protected $hidden = ['id', 'created_at', 'updated_at'];

	//////////////////// Relationships ////////////////////

	public function admins() {
		return $this->hasMany('App\Models\Admin\Admin');
	}
}
