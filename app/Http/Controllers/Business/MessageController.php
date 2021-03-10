<?php

namespace App\Http\Controllers\Business;

use Illuminate\Http\Request;
use App\Filters\MessageFilters;
use App\Http\Controllers\Controller;
use App\Models\Business\Business;
use App\Models\Business\BusinessMessage;
use App\Http\Resources\Business\MessageResource;
use App\Http\Requests\Business\StoreMessageRequest;
use App\Http\Requests\Business\UpdateMessageRequest;

class MessageController extends Controller {

  public function __construct() {
  	$this->middleware('auth:business');
    $this->middleware('csrf');
  }

  public function index(Request $request, MessageFilters $filters) {
  	$business = Business::getAuthBusiness();
    if ($request->has('unread')) {
      $hasUnread = BusinessMessage::filter($filters)->exists();
      return response()->json(['data' => ['unread' => (bool) $hasUnread]]);
    }
  	return MessageResource::collection($business->messages()->orderBy('updated_at', 'desc')->paginate()->appends($request->except('page')));
  }

  public function store(StoreMessageRequest $request) {
  	$business = Business::getAuthBusiness();
    $messageData = $request->validated();
    $messageData['latest_reply'] = now();
    $messageData['sent_by_business'] = true;
  	$message = $business->messages()->create($messageData);
  	return new MessageResource($message->fresh());
  }

  public function update(UpdateMessageRequest $request, BusinessMessage $businessMessage) {
  	if ($businessMessage->business_id != Business::getAuthBusiness()->id) {
  		return response()->json(['errors' => 'Permission denied.'], 403);
  	}
  	return new MessageResource($businessMessage->updateMessage(true));
  }
}
