<?php

namespace App\Models\Customer;

use Illuminate\Database\Eloquent\Model;

class HelpTicketReply extends Model {

	//////////////////// Attribute Mods/Helpers ////////////////////

	protected $fillable = ['message', 'from_customer'];
	protected $hidden = ['id', 'help_ticket_id'];
	protected $casts = ['read' => 'boolean', 'from_customer' => 'boolean'];

	//////////////////// Relationships ////////////////////

	public function helpTicket() {
		return $this->belongsTo('App\Models\Customer\HelpTicket');
	}
}
