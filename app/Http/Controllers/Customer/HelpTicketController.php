<?php

namespace App\Http\Controllers\Customer;

use App\Filters\HelpTicketFilters;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer\Customer;
use App\Models\Customer\HelpTicket;
use App\Http\Resources\Customer\HelpTicketResource;
use App\Http\Requests\Customer\StoreHelpTicketRequest;

class HelpTicketController extends Controller {

	public function __construct() {
  	$this->middleware('auth:customer');
  }

  public function index(Request $request, HelpTicketFilters $filters) {
  	$customer = Customer::getAuthCustomer();
  	return HelpTicketResource::collection($customer->helpTickets()->filter($filters)->paginate(10)->appends($request->except('page')));
  }

  public function store(StoreHelpTicketRequest $request) {
  	$helpTicket = Customer::getAuthCustomer()->helpTickets()->make($request->validated());
  	$helpTicket = $helpTicket->assignAdmin();
  	return new HelpTicketResource($helpTicket->fresh());
  }

  public function destroy(Request $request, HelpTicket $helpTicket) {
  	if ($helpTicket->customer_id != Customer::getAuthCustomer()->id) {
  		return response()->json(['errors' => 'Permission denied.'], 403);
  	}
  	$helpTicket->delete();
  	return response()->json(['data' => ['deleted' => true]], 200);
  }
}
