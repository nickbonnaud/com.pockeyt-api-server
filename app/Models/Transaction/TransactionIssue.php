<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class TransactionIssue extends Model {

	//////////////////// Traits ////////////////////

	use \BinaryCabin\LaravelUUID\Traits\HasUUID;

	//////////////////// Attribute Mods/Helpers ////////////////////

	protected $fillable = ['type', 'issue', 'prior_status_code', 'resolved'];
	protected $hidden = ['id', 'transaction_id', 'created_at', 'prior_status_code'];
	protected $uuidFieldName = 'identifier';
	protected $casts = ['resolved' => 'boolean'];

	//////////////////// Routing ////////////////////

	public function getRouteKeyName() {
		return 'identifier';
	}

	//////////////////// Relationships ////////////////////

	public function transaction() {
		return $this->belongsTo('App\Models\Transaction\Transaction');
	}
}
