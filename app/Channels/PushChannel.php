<?php

namespace App\Channels;

use Illuminate\Support\Arr;
use App\Helpers\TestHelpers;
use Illuminate\Notifications\Notification;
use App\Handlers\Errors\PushErrorHandler;
use Edujugon\PushNotification\PushNotification;
use App\Models\Transaction\TransactionNotification;

class PushChannel {

	public function send($notifiable, Notification $notification) {
		$message = $notification->toPush($notifiable);
		$response = $this->sendPush($message, $notifiable);
		$success = $this->checkSuccessPush($response);
		$this->handleSuccess($success, $message, $notifiable);
	}

	public function sendPush($message, $notifiable) {
		$serviceType = 'fcm';
		$token = $notifiable->pushToken->token;

		if (env('APP_ENV') == 'testing') {
			return TestHelpers::fakePush();
		} else {
			$push = new PushNotification($serviceType);
			return $push
				->setMessage($message)
				->setDevicesToken($token)
				->setApiKey(env('FCM_SERVER_KEY'))
				->send()
				->getFeedback();
		}
	}

	public function checkSuccessPush($response) {
		return $response->success;
	}

	public function handleSuccess($success, $message, $notifiable) {
		if (!$success) {
			$this->handlePushError($message, $notifiable);
		} else {
			$this->handlePushSuccess($message, $notifiable);
		}
	}

	public function handlePushSuccess($message, $notifiable) {
		$transactionIdentifier = strtolower($notifiable->pushToken->device) == "ios" ? 
			Arr::get($message, 'data.transaction_id') : 
			Arr::get($message, 'data.custom.transaction_id');
		$type = Arr::get($message, 'data.category');
		TransactionNotification::storeNewNotification($transactionIdentifier, $type);
	}

	public function handlePushError($message, $notifiable) {
		$transactionIdentifier = strtolower($notifiable->pushToken->device) == "ios" ? 
			Arr::get($message, 'data.transaction_id') : 
			Arr::get($message, 'data.custom.transaction_id');

		$type = Arr::get($message, 'data.category');
		(new PushErrorHandler())->handle($type, $transactionIdentifier);
	}
}