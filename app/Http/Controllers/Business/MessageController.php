<?php

namespace App\Http\Controllers\Business;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Business\Business;
use App\Models\Business\BusinessMessage;
use App\Http\Resources\Business\MessageResource;
use App\Http\Requests\Business\StoreMessageRequest;
use App\Http\Requests\Business\UpdateMessageRequest;

class MessageController extends Controller {
  
  public function __construct() {
  	$this->middleware('auth:business');
  }

  public function index(Request $request) {
  	$business = Business::getAuthBusiness();
  	return MessageResource::collection($business->messages()->orderBy('updated_at', 'desc')->paginate()->appends($request->except('page')));
  }

  public function store(StoreMessageRequest $request) {
  	$business = Business::getAuthBusiness();
    $messageData = $request->validated();
    $messageData['read'] = true;
  	$message = $business->messages()->create($messageData);
  	return new MessageResource($message);
  }

  public function update(UpdateMessageRequest $request, BusinessMessage $businessMessage) {
  	if ($businessMessage->business_id != Business::getAuthBusiness()->id) {
  		return response()->json(['errors' => 'Permission denied.'], 403);
  	}
  	return new MessageResource($businessMessage->updateMessage($request->validated()));
  }
}
