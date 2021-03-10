<?php

namespace App\Http\Controllers\Business;

use Illuminate\Http\Request;
use App\Models\Business\Business;
use App\Http\Controllers\Controller;
use App\Models\Business\BusinessMessage;
use App\Models\Business\BusinessMessageReply;
use App\Http\Resources\Business\ReplyResource;
use App\Http\Requests\Business\StoreReplyRequest;
use App\Http\Requests\Business\UpdateReplyRequest;

class ReplyController extends Controller {

  public function __construct() {
  	$this->middleware('auth:business');
		$this->middleware('csrf');
  }

  public function store(StoreReplyRequest $request) {
  	$message = BusinessMessage::getMessage($request->message_identifier);
  	if (Business::getAuthBusiness()->id != $message->business_id) {
  		return response()->json(['errors' => 'Permission denied.'], 403);
  	}

  	$replyData = $request->except('message_identifier');
		$replyData['sent_by_business'] = true;
		$reply = $message->addReply($replyData);
  	return new ReplyResource($reply->fresh());
  }
}
