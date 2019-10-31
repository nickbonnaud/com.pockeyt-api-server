<?php

namespace App\Models\Business;

use App\Helpers\ShopifyTestHelpers as TestHelpers;
use Illuminate\Support\Arr;
use App\Models\Customer\Customer;
use App\Handlers\Http\HttpHandler;
use App\Models\Refund\Refund;
use Illuminate\Database\Eloquent\Model;
use App\Models\Transaction\Transaction;
use App\Handlers\Errors\HttpErrorHandler;

class ShopifyAccount extends Model {
  
  //////////////////// Traits ////////////////////

	use \BinaryCabin\LaravelUUID\Traits\HasUUID;

	//////////////////// Attribute Mods/Helpers ////////////////////

	protected $guarded = ['id', 'identifier'];
	protected $visible = ['identifier'];
	protected $uuidFieldName = 'identifier';
	protected $casts = ['webhook_set' => 'boolean'];

	//////////////////// Routing ////////////////////

	public function getRouteKeyName() {
		return 'identifier';
	}

	//////////////////// Relationships ////////////////////

	public function posAccount() {
		return $this->belongsTo('App\Models\Business\PosAccount');
	}

	//////////////////// Core Methods ////////////////////

	public function getBaseUrl() {
		return $this->setUrlValue('shop_id', $this->shop_id, config('urls.shopify.base'));
	}

	public function setUrlValue($key, $value, $url) {
		return str_replace("<{$key}>", $value, $url);
	}

	public function getHeaders() {
		return [
			"X-Shopify-Access-Token" => $this->access_token,
			"Content-Type" => "application/json",
			"Accept" => "application/json"
		];
	}

	public function createHttpHandler() {
		return new HttpHandler();
	}

	public function parseHttpResponse($response) {
		if (env('APP_ENV') == 'testing' && ($this->access_token == 'not_token')) {
			return json_decode($response, true);
		} else {
			return $response->json();
		}
	}

	public function handleError($response, $receivingUser) {
		if (!$response->isOk()) {
			(new HttpErrorHandler())->handle($this->parseHttpResponse($response), $receivingUser);
		}
	}

	public function authorizeShopify($shopId, $code, $business) {
		$accessToken = $this->postAccessToken($shopId, $code);
		return $this->createAccount($accessToken, $shopId, $business);
	}

	public function createAccount($accessToken, $shopId, $business) {
		return $this->create([
			'pos_account_id' => $business->posAccount->id,
			'access_token' => $accessToken['access_token'],
			'shop_id' => $shopId
		]);
	}

	public function postAccessToken($shopId, $code) {
		$url = $this->setUrlValue('shop_id', $shopId, config('urls.shopify.oauth_token'));
		$headers = [
			"Content-Type" => "application/json",
			"Accept" => "application/json"
		];
		$body = [
			'client_id' => env('SHOPIFY_CLIENT_ID'),
			'client_secret' => env('SHOPIFY_SECRET'),
			'code' => $code
		];

		if (env('APP_ENV') == 'testing') {
			$response = TestHelpers::fakeAccessTokenResponse();
		} else {
			$response = $this->createHttpHandler()->post($url, $headers, $body);
			$this->handleError($response, $this->posAccount->business);
		}

		if (env('APP_ENV') == 'testing') {
			return json_decode($response, true);
		} else {
			return $response->json();
		}

		return $this->parseHttpResponse($response);
	}

	public function createWebHooks() {
		$orderResponse = $this->registerWebhooks('orders/paid');
		$refundResponse = $this->registerWebhooks('refunds/create');

		if (!Arr::has($orderResponse, 'errors') && !Arr::has($refundResponse, 'errors')) {
			$this->update(['webhook_set' => true]);
		}
	}

	private function registerWebhooks($type) {
		$url = $this->getBaseUrl() . config('urls.shopify.webhook');
		$body = [
			'webhook' => [
				'topic' => $type,
				'address' => env('APP_URL') . '/api/webhook/shopify',
				'format' => 'json'
			]
		];
		$headers = $this->getHeaders();
		if (env('APP_ENV') == 'testing') {
			$response = TestHelpers::fakeCreateWebHook();
		} else {
			$response = $this->createHttpHandler()->post($url, $headers, $body);
			$this->handleError($response, $this->posAccount->business);
		}
		return $this->parseHttpResponse($response);
	}

	public function getCustomerFromWebhook($noteAttributes) {
		if (count($noteAttributes) > 0) {
			foreach ($noteAttributes as $noteAttribute) {
				if ($noteAttribute['name'] == strtolower(env('BUSINESS_NAME')) . '_id') {
					return Customer::where('identifier', $noteAttribute['value'])->first();
				}
			}
		}
	}

	public function createTransaction($orderData) {
		if ($customer = $this->getCustomerFromWebhook($orderData['note_attributes'])) {
			$business =  $this->posAccount->business;
			$transaction = Transaction::createTransactionFromShopify($customer, $orderData, $business->id);
			$transaction->storePurchasedItemsShopify($orderData, $business);
		}
	}

	public function createRefund($refundData) {
		$orderData = $this->getOrder($refundData['order_id']);
		if ($customer = $this->getCustomerFromWebhook($orderData['order']['note_attributes'])) {
			$transaction = $this->posAccount
													->business
													->transactions
													->where('pos_transaction_id', $refundData['order_id'])
													->first();
			if ($transaction->status->name == 'paid') {
				Refund::createRefundFromShopify($refundData, $transaction);
			} else {
				$transaction->updateTransactionShopify($orderData, $refundData);
			}
		}
	}

	public function getOrder($orderId) {
		$url = $this->getBaseUrl() . $this->setUrlValue('order_id', $orderId, config('urls.shopify.order'));
		$headers = $this->getHeaders();

		if (env('APP_ENV') == 'testing') {
			$response = TestHelpers::fakeCreateRefundOrder();
		} else {
			$response = $this->createHttpHandler()->get($url, $headers);
			$this->handleError($response, $this->posAccount->business);
		}

		return $this->parseHttpResponse($response);
	}

	public function createEmployee($employeeId) {
		// No employees to associate
		return;
	}
}
