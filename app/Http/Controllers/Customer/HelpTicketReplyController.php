<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer\Customer;
use App\Models\Customer\HelpTicket;
use App\Http\Resources\Customer\HelpTicketReplyResource;
use App\Http\Resources\Customer\HelpTicketResource;
use App\Http\Requests\Customer\StoreHelpTicketReplyRequest;

class HelpTicketReplyController extends Controller {

	public function __construct() {
  	$this->middleware('auth:customer');
  }

  public function store(StoreHelpTicketReplyRequest $request) {
  	$customer = Customer::getAuthCustomer();
  	$helpTicket = $customer->helpTickets->firstWhere('identifier', $request->ticket_identifier);
  	if (is_null($helpTicket)) {
  		return response()->json(['errors' => 'Permission denied.'], 403);
  	}
  	
  	return new HelpTicketReplyResource($helpTicket->addCustomerReply($request->validated()));
  }

  public function update(Request $request, HelpTicket $helpTicket) {
  	if ($helpTicket->customer_id != Customer::getAuthCustomer()->id) {
  		return response()->json(['errors' => 'Permission denied.'], 403);
  	}
  	$helpTicket->updateUnreadRepliesForCustomer();
  	return new HelpTicketResource($helpTicket->fresh());
  }
}
