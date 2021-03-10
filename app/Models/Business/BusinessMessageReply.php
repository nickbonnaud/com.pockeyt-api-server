<?php

namespace App\Models\Business;

use Illuminate\Database\Eloquent\Model;

class BusinessMessageReply extends Model {

	//////////////////// Traits ////////////////////

	use \BinaryCabin\LaravelUUID\Traits\HasUUID;

	//////////////////// Attribute Mods/Helpers ////////////////////

	protected $fillable = ['body', 'sent_by_business', 'read'];
	protected $hidden = ['id', 'business_message_id'];
	protected $uuidFieldName = 'identifier';
	protected $casts = ['sent_by_business' => 'boolean', 'read' => 'boolean'];
	protected $touches = ['message'];

	//////////////////// Routing ////////////////////

	public function getRouteKeyName() {
		return 'identifier';
	}

	//////////////////// Relationships ////////////////////

	public function message() {
		return $this->belongsTo('App\Models\Business\BusinessMessage', 'business_message_id');
	}
}
