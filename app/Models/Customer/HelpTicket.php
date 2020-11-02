<?php

namespace App\Models\Customer;

use App\Models\Admin\Admin;
use Illuminate\Support\Facades\DB;
use App\Models\Customer\HelpTicket;
use Illuminate\Database\Eloquent\Model;

class HelpTicket extends Model {

	//////////////////// Traits ////////////////////

	use \BinaryCabin\LaravelUUID\Traits\HasUUID;

	//////////////////// Attribute Mods/Helpers ////////////////////

	protected $fillable = ['subject', 'message', 'read', 'resolved'];
	protected $hidden = ['id', 'customer_id', 'admin_id', 'created_at'];
	protected $uuidFieldName = 'identifier';
	protected $casts = ['read' => 'boolean', 'resolved' => 'boolean'];

	//////////////////// Routing ////////////////////

	public function getRouteKeyName() {
		return 'identifier';
	}

	//////////////////// Relationships ////////////////////

	public function customer() {
		return $this->belongsTo('App\Models\Customer\Customer');
	}

	public function admin() {
		return $this->belongsTo('App\Models\Admin\Admin');
	}

	public function replies() {
		return $this->hasMany('App\Models\Customer\HelpTicketReply');
	}

	//////////////////// Relationship Methods ////////////////////

	public function addCustomerReply($replyData) {
		$replyData['from_customer'] = true;
		$this->touch();
		return $this->replies()->create($replyData);
	}

	public function addAdminReply($replyData) {
		$replyData['from_customer'] = false;
		$this->touch();
		return $this->replies()->create($replyData);
	}

	//////////////////// Core Methods ////////////////////

	public function fetchByIdentifier($identifier) {
		return $this->where('identifier', $identifier)->first();
	}

	public function updateTicket($updateData) {
		$this->update($updateData);
	}

	public function updateUnreadRepliesForCustomer() {
		$this->replies()->where('from_customer', false)->update(['read' => true]);
	}

	public function updateUnreadRepliesForAdmin() {
		$this->replies()->where('from_customer', true)->update(['read' => true]);
	}

	public function assignAdmin() {
		$helpTicketAdmin = HelpTicket::select('admin_id', DB::raw('count(*) as total'))
      ->groupBY('admin_id')
      ->orderBy('total')
      ->first();

    if (is_null($helpTicketAdmin)) {
    	$this->admin_id = Admin::first()->id;
    } else {
    	$this->admin_id = $helpTicketAdmin->admin_id;
    }

    $this->save();
      return $this;
	}

	public function scopeFilter($query, $filters) {
		return $filters->apply($query)->latest('updated_at');
	}
}
