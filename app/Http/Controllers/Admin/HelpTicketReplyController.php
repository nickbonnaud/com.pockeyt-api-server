<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\Admin;
use Illuminate\Http\Request;
use App\Models\Customer\HelpTicket;
use App\Http\Resources\Admin\HelpTicketReplyResource;
use App\Http\Resources\Admin\HelpTicketResource;
use App\Http\Requests\Admin\StoreHelpTicketReplyRequest;

class HelpTicketReplyController extends Controller {
	
	public function __construct() {
  	$this->middleware('auth:admin');
  }

  public function store(StoreHelpTicketReplyRequest $request) {
  	$admin = Admin::getAuthAdmin();

    if ($admin->role->code == 0) {
      $helpTicket = HelpTicket::firstWhere('identifier', $request->ticket_identifier);
    } else {
      $helpTicket = $admin->helpTickets->firstWhere('identifier', $request->ticket_identifier);
    }

  	if (is_null($helpTicket)) {
  		return response()->json(['errors' => 'Permission denied.'], 403);
  	}

  	return new HelpTicketReplyResource($helpTicket->addAdminReply($request->validated()));
  }

  public function update(Request $request, HelpTicket $helpTicket) {
  	$admin = Admin::getAuthAdmin();
    if ($helpTicket->admin_id != $admin->id && $admin->role->code != 0) {
  		return response()->json(['errors' => 'Permission denied.'], 403);
  	}
  	$helpTicket->updateUnreadRepliesForAdmin();
  	return new HelpTicketResource($helpTicket->fresh());
  }
}
