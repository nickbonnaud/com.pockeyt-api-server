<?php

namespace App\Models\Business;

use Illuminate\Database\Eloquent\Model;

class BusinessMessage extends Model {

	//////////////////// Traits ////////////////////

	use \BinaryCabin\LaravelUUID\Traits\HasUUID;

	//////////////////// Attribute Mods/Helpers ////////////////////

	protected $fillable = ['title', 'body', 'sent_by_business', 'read', 'unread_reply', 'latest_reply'];
	protected $hidden = ['id', 'business_id'];
	protected $uuidFieldName = 'identifier';
	protected $casts = ['sent_by_business' => 'boolean', 'read' => 'boolean', 'unread_reply' => 'boolean'];

	//////////////////// Routing ////////////////////

	public function getRouteKeyName() {
		return 'identifier';
	}

	//////////////////// Relationships ////////////////////

	public function business() {
		return $this->belongsTo('App\Models\Business\Business');
	}

	public function replies() {
		return $this->hasMany('App\Models\Business\BusinessMessageReply', 'business_message_id');
	}

	//////////////////// Core Methods ////////////////////

	public static function getMessage($identifier) {
		return self::where('identifier', $identifier)->first();
	}

	public function updateMessage($fromBusiness) {
		$this->markAllRepliesAsRead($fromBusiness);
		$this->setHasUnreadReply();
		$this->update(['read' => true]);
		return $this;
	}

	public function addReply($replyData) {
		$this->update(['latest_reply' => now(), 'unread_reply' => true]);
		return $this->replies()->create($replyData);
	}


	public function markAllRepliesAsRead($fromBusiness) {
		$this->replies()
			->where('read', false)
			->where('sent_by_business', !$fromBusiness)
			->update(['read' => true]);
	}

	public function setHasUnreadReply() {
		$hasUnread = $this->replies()->where('read', false)->exists();
		if (!$hasUnread) {
			$this->update(['unread_reply' => false]);
		}
	}

	public function scopeFilter($query, $filters) {
		return $filters->apply($query);
	}
}
