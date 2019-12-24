<?php

namespace App\Models\Business;

use Illuminate\Database\Eloquent\Model;

class BusinessMessage extends Model {

	//////////////////// Traits ////////////////////

	use \BinaryCabin\LaravelUUID\Traits\HasUUID;

	//////////////////// Attribute Mods/Helpers ////////////////////

	protected $fillable = ['title', 'body', 'sent_by_business', 'read', 'unread_reply', 'read_by_admin'];
	protected $hidden = ['id', 'business_id'];
	protected $uuidFieldName = 'identifier';
	protected $casts = ['sent_by_business' => 'boolean', 'read' => 'boolean', 'unread_reply' => 'boolean', 'read_by_admin' => 'boolean'];

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

	public function updateMessage($messageData) {
		if ($this->unread_reply && $messageData['unread_reply'] == false) {
			$this->update(['unread_reply' => false, 'read' => true]);
			$this->markRepliesAsRead();
		} elseif (!$this->read && $messageData['read']) {
			$this->update($messageData);
		}

		return $this;
	}

	public function markRepliesAsRead() {
		$this->replies()
			->where('read', false)
			->where('sent_by_business', false)
			->update(['read' => true]);
	}

	public function addReply($replyData) {
		if (!$replyData['sent_by_business']) {
			$this->update(['unread_reply' => true]);
		}
		return $this->replies()->create($replyData);
	}

	public function updateUnreadReply($reply) {
		$hasOtherUnreadReplies = $this->replies()
			->where('id', '!=', $reply->id)
			->where('read', false)
			->where('sent_by_business', false)
			->exists();

		if (!$hasOtherUnreadReplies) {
			$this->update(['unread_reply' => false]);
		}
	}
}
