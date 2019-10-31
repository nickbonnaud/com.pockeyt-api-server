<?php

namespace App\Models\Business;

use Illuminate\Support\Arr;
use App\Helpers\VendTestHelpers as TestHelpers;
use App\Handlers\Http\HttpHandler;
use App\Models\Customer\Customer;
use App\Models\Refund\Refund;
use App\Models\Transaction\Transaction;
use Illuminate\Database\Eloquent\Model;
use App\Handlers\Errors\HttpErrorHandler;

class VendAccount extends Model {

  //////////////////// Traits ////////////////////

	use \BinaryCabin\LaravelUUID\Traits\HasUUID;

	//////////////////// Attribute Mods/Helpers ////////////////////

	protected $guarded = ['id', 'identifier'];
	protected $visible = ['identifier'];
	protected $uuidFieldName = 'identifier';

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
		return $this->setUrlValue('domain_prefix', $this->domain_prefix, config('urls.vend.base'));
	}

	public function getHeaders($type = null) {
		return [
			'Content-Type' => $type == 'x' ? 'application/x-www-form-urlencoded' : 'application/json',
			'Authorization' => "Bearer {$this->getAccessToken()}"
		];
	}

	public function getAccessToken() {
		if ($this->expiry < time()) {
			return $this->refreshToken();
		}
		return $this->access_token;
	}

	public function refreshToken() {
		$url = $this->getBaseUrl() . config('urls.vend.oauth_token');
		$body = [
			'refresh_token' => $this->refresh_token,
			'client_id' => env('VEND_CLIENT_ID'),
			'client_secret' => env('VEND_SECRET'),
			'grant_type' => 'refresh_token',
		];

		$headers = [
			"Content-Type" => "application/x-www-form-urlencoded",
		];

		if (env('APP_ENV') == 'testing') {
			$response = TestHelpers::fakeAccessTokenResponse();
		} else {
			$response = $this->createHttpHandler()->postFormParams($url, $body, $headers);
			$this->handleError($response, $this->posAccount->business);
		}
		$response = $this->parseHttpResponse($response);
		$this->updateToken($response);
		return $response['access_token'];
	}

	public function updateToken($refreshTokenResponse) {
		$this->update([
			'access_token' => $refreshTokenResponse['access_token'],
			'expiry' => $refreshTokenResponse['expires'],
			'refresh_token' => Arr::has($refreshTokenResponse, 'refresh_token') ? $refreshTokenResponse['refresh_token'] : $this->refresh_token
		]);
	}

	public function createHttpHandler() {
		return new HttpHandler();
	}

	public function handleError($response, $receivingUser) {
		if (!$response->isOk()) {
			(new HttpErrorHandler())->handle($this->parseHttpResponse($response), $receivingUser);
		}
	}

	public function parseHttpResponse($response) {
		if (env('APP_ENV') == 'testing' && ($this->access_token == 'not_token')) {
			return json_decode($response, true);
		} else {
			return $response->json();
		}
	}

	public function setUrlValue($key, $value, $url) {
		return str_replace("<{$key}>", $value, $url);
	}

	public function authorizeVend($domainPrefix, $code, $business) {
		$accessToken = $this->postAccessCode($domainPrefix, $code);
		return $this->createAccount($accessToken, $domainPrefix, $business);
	}

	public function createAccount($accessToken, $domainPrefix, $business) {
		return $this->create([
			'pos_account_id' => $business->posAccount->id,
			'access_token' => $accessToken['access_token'],
			'domain_prefix' => $domainPrefix,
			'refresh_token' => $accessToken['refresh_token'],
			'expiry' => $accessToken['expires']
		]);
	}

	public function postAccessCode($domainPrefix, $code) {
		$url = $this->setUrlValue('domain_prefix', $domainPrefix, config('urls.vend.base')) . config('urls.vend.oauth_token');
		$headers = [
			"Content-Type" => "application/x-www-form-urlencoded",
		];
		$body = [
			'code' => $code,
			'client_id' => env('VEND_CLIENT_ID'),
			'client_secret' => env('VEND_SECRET'),
			'grant_type' => 'authorization_code',
			'redirect_uri' => url('/api/business/pos/vend/oauth')
		];

		if (env('APP_ENV') == 'testing') {
			$response = TestHelpers::fakeAccessTokenResponse();
		} else {
			$response = $this->createHttpHandler()->postFormParams($url, $body);
			$this->handleError($response, $this->posAccount->business);
		}

		if (env('APP_ENV') == 'testing') {
			return json_decode($response, true);
		} else {
			return $response->json();
		}

		return $this->parseHttpResponse($response);
	}

	public function createWebhook() {
		$url = $this->getBaseUrl() . config('urls.vend.webhook');
		$headers = $this->getHeaders('x');

		$body = [
			'data' => json_encode([
				'url' => env('APP_URL') . '/api/webhook/vend',
				'active' => true,
				'type' => "sale.update"
			])
		];

		if (env('APP_ENV') == 'testing') {
			$response = TestHelpers::fakeCreateWebhook();
		} else {
			$response = $this->createHttpHandler()->postFormParams($url, $body, $headers);
			$this->handleError($response, $this->posAccount->business);
		}
		$response = $this->parseHttpResponse($response);
		
		if (Arr::has($response, 'id')) {
			$this->update(['webhook_set' => true]);
		}
	}

	public function createCustomer($customer) {
		$url = $this->getBaseUrl() . config('urls.vend.create_customer');
		$body = [
			'first_name' => $customer->profile->first_name,
			'last_name' => $customer->profile->last_name,
			'email' => $customer->email,
			'note' => env('BUSINESS_NAME') . " Customer",
			'custom_field_1' => $customer->identifier
		];
		$headers = $this->getHeaders();

		if (env('APP_ENV') == 'testing') {
			$response = TestHelpers::fakeCreateCustomer();
		} else {
			$response = $this->createHttpHandler()->post($url, $headers, $body);
			$this->handleError($response, $this->posAccount->business);
		}

		$response = $this->parseHttpResponse($response);
		return $response['data']['id'];
	}

	public function destroyCustomer($billIdentifier) {
		$url = $this->getBaseUrl() . $this->setUrlValue('customer_id', $billIdentifier, config('urls.vend.destroy_customer'));
		$headers = $this->getHeaders();

		if (env('APP_ENV') == 'testing') {
			
		} else {
			$response = $this->createHttpHandler()->delete($url, $headers);
			$this->handleError($response, $this->posAccount->business);
		}
	}

	public function handleWebhook($saleData) {
		if ($customer = $this->getCustomerFromWebhook($saleData->customer)) {
			$business = $this->posAccount->business;

			if ($saleData->return_for) {
				$this->handleRefund($saleData, $customer, $business);
			} else {
				$this->handleTransaction($saleData, $customer, $business);
			}
		}
	}

	public function handleRefund($saleData, $customer, $business) {
		if (is_null(Refund::where('pos_refund_id', $saleData->id)->first())) {
			$transaction = $this->posAccount
				->business
				->transactions
				->where('pos_transaction_id', $saleData->return_for)
				->first();

			if ($transaction->status->name == 'paid') {
				Refund::createRefundFromVend($saleData, $transaction);
			} else {
				$transaction->updateTransactionVend($saleData);
			}
		}
	}

	public function handleTransaction($saleData, $customer, $business) {
		if ($transaction = Transaction::where('pos_transaction_id', $saleData->id)->first()) {
				$transaction->updateTransactionVend($saleData);
		} else {
			$transaction = Transaction::createTransactionFromVend($customer, $saleData, $business->id);
			$transaction->storePurchasedItemsVend($saleData, $business, $this);
		}
	}

	public function getCustomerFromWebhook($customerData) {
		if ($customerData->custom_field_1) {
			return Customer::where('identifier', $customerData->custom_field_1)->first();
		}
	}

	public function fetchProduct($item) {
		$url = $this->getBaseUrl() . $this->setUrlValue('product_id', $item->product_id, config('urls.vend.product'));
		$headers = $this->getHeaders();

		if (env('APP_ENV') == 'testing') {
			$response = TestHelpers::fakeFetchProductData($item->product_id);
		} else {
			$response = $this->createHttpHandler()->get($url, $headers);
			$this->handleError($response, $this->posAccount->business);
		}

		$response = $this->parseHttpResponse($response);
		return $response['data'];
	}

	public function createEmployee($employeeId) {
		$employeeData = $this->fetchEmployee($employeeId);
		if (Arr::has($employeeData, 'display_name') && $employeeData['display_name'] != "") {
			if ($index = strpos($employeeData['display_name'], ' ')) {
				$firstName = substr($employeeData['display_name'], 0, $index);
				$lastName = substr($employeeData['display_name'], $index + 1);
			} else {
				$firstName = $employeeData['display_name'];
				$lastName = $employeeData['username'];
			}
		} else {
			$firstName = $employeeData['username'];
			$lastName = $employeeData['username'];
		}
		$this->posAccount->business->employees()->create([
			'external_id' => $employeeId,
			'first_name' => $firstName,
			'last_name' => $lastName,
			'email' => Arr::has($employeeData, 'email') ? $employeeData['email'] : null
		]);
	}

	public function fetchEmployee($employeeId) {
		$url = $this->getBaseUrl() . $this->setUrlValue('user_id', $employeeId, config('urls.vend.employee'));
		$headers = $this->getHeaders();

		if (env('APP_ENV') == 'testing') {
			$response = TestHelpers::fakeEmployeeFetch($employeeId);
		} else {
			$response = $this->createHttpHandler()->get($url, $headers);
			$this->handleError($response, $this->posAccount->business);
		}

		$response = $this->parseHttpResponse($response);
		return $response['data'];
	}

}
