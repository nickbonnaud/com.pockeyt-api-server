<?php

namespace App\Http\Controllers\Admin;

use App\Filters\HelpTicketFilters;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin\Admin;
use App\Models\Customer\HelpTicket;
use App\Http\Resources\Admin\HelpTicketResource;
use App\Http\Requests\Admin\UpdateHelpTicketRequest;

class HelpTicketController extends Controller {

	public function __construct() {
  	$this->middleware('auth:admin');
  }

  public function index(Request $request, HelpTicketFilters $filters) {
  	$admin = Admin::getAuthAdmin();
    if ($admin->role->code == 0) {
      $helpTickets = HelpTicket::filter($filters)->paginate(10)->appends($request->except('page'));
    } else {
      $helpTickets = $admin->helpTickets()->filter($filters)->paginate(10)->appends($request->except('page'));
    }
  	return HelpTicketResource::collection($helpTickets);
  }

  public function update(UpdateHelpTicketRequest $request, HelpTicket $helpTicket) {
  	$admin = Admin::getAuthAdmin();
    if ($helpTicket->admin_id != $admin->id && $admin->role->code != 0) {
  		return response()->json(['errors' => 'Permission denied.'], 403);
  	}
  	$helpTicket->updateTicket($request->validated());
  	return new HelpTicketResource($helpTicket->fresh());
  }
}
