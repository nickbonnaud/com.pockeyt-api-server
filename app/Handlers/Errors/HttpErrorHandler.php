<?php

namespace App\Handlers\Errors;

class HttpErrorHandler {

	public function handle($error, $receivingUser) {
		// Use socket to push failure
		// use callback to check client recieved
		// Maybe send new push token back ..??

		// if still fail send via phone
	}
}