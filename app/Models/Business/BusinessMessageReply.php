<?php

namespace App\Models\Business;

use Illuminate\Database\Eloquent\Model;

class BusinessMessageReply extends Model {

	//////////////////// Traits ////////////////////

	use \BinaryCabin\LaravelUUID\Traits\HasUUID;

	//////////////////// Attribute Mods/Helpers ////////////////////

	protected $fillable = ['body', 'sent_by_business', 'read', 'read_by_admin'];
	protected $hidden = ['id', 'business_message_id'];
	protected $uuidFieldName = 'identifier';
	protected $casts = ['sent_by_business' => 'boolean', 'read' => 'boolean', 'read_by_admin' => 'boolean'];
	protected $touches = ['message'];

	//////////////////// Routing ////////////////////

	public function getRouteKeyName() {
		return 'identifier';
	}

	//////////////////// Relationships ////////////////////

	public function message() {
		return $this->belongsTo('App\Models\Business\BusinessMessage', 'business_message_id');
	}

	//////////////////// Core Methods ////////////////////

	public function updateReply($replyData) {
		if (!$this->read) {
			$this->update($replyData);
			if (!$this->sent_by_business) {
				$this->message->updateUnreadReply($this);
			}
		}
		return $this;
	}
}
