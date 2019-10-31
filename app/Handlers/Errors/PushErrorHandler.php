<?php

namespace App\Handlers\Errors;

class PushErrorHandler {

	public function handle($type, $transactionId = null) {
		// Use socket to push failure
		// use callback to check client recieved
		// Maybe send new push token back ..??

		// if still fail send via phone
	}
}