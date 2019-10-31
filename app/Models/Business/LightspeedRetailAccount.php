<?php

namespace App\Models\Business;

use App\Helpers\LightspeedRetailTestHelpers as TestHelpers;
use App\Models\Customer\Customer;
use App\Handlers\Http\HttpHandler;
use Illuminate\Database\Eloquent\Model;
use App\Models\Transaction\Transaction;
use App\Models\Refund\Refund;
use App\Handlers\Errors\HttpErrorHandler;

class LightspeedRetailAccount extends Model {

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
		return config('urls.lightspeed_retail.base');
	}

	public function getHeaders() {
		return [
			"Authorization" => "Bearer {$this->access_token}",
			"Content-Type" => "application/json",
			"Accept" => "application/json"
		];
	}

	public function setUrlValue($key, $value, $url) {
		return str_replace("<{$key}>", $value, $url);
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

	public function authorizeRetail($code, $business) {
		$tokenData = $this->postAccessToken($code, $business);
		$accountId = $this->getAccountId($tokenData['access_token']);
		return $this->createRetailAccount($tokenData, $accountId, $business);
	}

	public function createRetailAccount($tokenData, $accountId, $business) {
		return $this->create([
			'pos_account_id' => $business->posAccount->id,
			'access_token' => $tokenData['access_token'],
			'account_id' => $accountId,
			'refresh_token' => $tokenData['refresh_token'],
			'expiry' => $this->setTokenExpiry($tokenData['expires_in'])
		]);
	}

	public function getAccountId($accessToken) {
		$url = $this->getBaseUrl() . config('urls.lightspeed_retail.account');
		$headers = [
			"Authorization" => "Bearer {$accessToken}",
			"Content-Type" => "application/json",
			"Accept" => "application/json"
		];

		if (env('APP_ENV') == 'testing' && $accessToken == 'not_token') {
			$response = TestHelpers::fakeLightspeedRetailAccountResponse();
		} else {
			$response = $this->doHttpRequest('get', $url, $headers);
			$this->handleError($response, $this->posAccount->business);
		}

		if (env('APP_ENV') == 'testing' && $accessToken == 'not_token') {
			return (json_decode($response, true))['Account']['accountID'];
		}

		return ($this->parseHttpResponse($response))['Account']['accountID'];
	}

	public function postAccessToken($code, $business) {
		$url = config('urls.lightspeed_retail.oauth_token');
		$headers = [
			"Content-Type" => "application/json",
			"Accept" => "application/json"
		];
		$body = [
			'client_id' => env('LIGHTSPEED_RETAIL_CLIENT_ID'),
			'client_secret' => env('LIGHTSPEED_RETAIL_SECRET'),
			'code' => $code,
			'grant_type' => 'authorization_code'
		];

		if (env('APP_ENV') == 'testing') {
			$response = TestHelpers::fakeLightspeedRetailAuthResponse();
		} else {
			$response = $this->doHttpRequest('post', $url, $headers, $body);
			$this->handleError($response, $this->posAccount->business);
		}

		if (env('APP_ENV') == 'testing') {
			return json_decode($response, true);
		} else {
			return $response->json();
		}

		return $this->parseHttpResponse($response);
	}

	public function getSaleData($saleId) {
		$url = $this->setUrlValue('account_id', $this->account_id, $this->getBaseUrl() . config('urls.lightspeed_retail.sale'));
		$url = $this->setUrlValue('sale_id', $saleId, $url);
		$headers = $this->getHeaders();

		if (env('APP_ENV') == 'testing' && $this->access_token == 'not_token') {
			if ($this->refresh_token == 'refund') {
				$response = TestHelpers::fakeSalePartialRefund();
			} else {
				$response = TestHelpers::fakeLightspeedRetailSaleResponse();
			}
		} else {
			$response = $this->doHttpRequest('get', $url, $headers);
			$this->handleError($response, $this->posAccount->business);
		}
		return ($this->parseHttpResponse($response))['Sale'];
	}

	public function processTransaction($lightspeedSale, $customerIdentifier) {
		$customer = Customer::where('identifier', $customerIdentifier)->first();
		if ($lightspeedSale['totalDue'] > 0) {
			$this->processSale($lightspeedSale, $customer);
		} else {
			$this->processRefund($lightspeedSale, $customer);
		}
	}

	public function processRefund($lightspeedSale, $customer) {
		foreach ($lightspeedSale['SaleLines']['SaleLine'] as $item) {
			if (gettype($item) == 'string') {
				$saleLineId = $lightspeedSale['SaleLines']['SaleLine']['parentSaleLineID'];
			} else {
				$saleLineId = $item['parentSaleLineID'];
			}
			$saleId = $this->getParentSaleId($saleLineId);
			$transaction = Transaction::where('pos_transaction_id', $saleId)->first();
			if ($transaction->status->name == 'paid') {
				Refund::createRefundFromLightspeedRetail($lightspeedSale, $transaction);
			} else {
				$transaction->delete();
			}
			break;
		}
	}

	public function getParentSaleId($saleLineId) {
		$url = $this->setUrlValue('account_id', $this->account_id, $this->getBaseUrl() . config('urls.lightspeed_retail.sale_line'));
		$url = $this->setUrlValue('sale_line_id', $saleLineId, $url);
		$headers = $this->getHeaders();

		if (env('APP_ENV') == 'testing' && $this->access_token == 'not_token') {
			$response = TestHelpers::fakeSalePartialRefundSaleLine();
		} else {
			$response = $this->doHttpRequest('get', $url, $headers);
			$this->handleError($response, $this->posAccount->business);
		}
		return ($this->parseHttpResponse($response))['SaleLine']['saleID'];
	}

	public function processSale($lightspeedSale, $customer) {
		$transaction = Transaction::createTransactionFromLightspeedRetail($lightspeedSale, $customer, $this->posAccount->business);
		$transaction->storePurchasedItemsLightspeedRetail($lightspeedSale, $this->posAccount->business);
	}

	public function createPaymentType() {
		$url = $this->setUrlValue('account_id', $this->account_id, $this->getBaseUrl() . config('urls.lightspeed_retail.payment_type'));
		$headers = $this->getHeaders();

		$body = [
			'name' => env('BUSINESS_NAME') . " Pay",
			'requireCustomer' => false,
			'internalReserved' => false,
			'type' => 'user defined',
			'refundAsPaymentTypeID' => 0
		];

		if (env('APP_ENV') == 'testing' && $this->access_token == 'not_token') {
			// Nothing required
		} else {
			$response = $this->doHttpRequest('post', $url, $headers, $body);
			$this->handleError($response, $this->posAccount->business);
		}
	}

	public function createEmployee($employeeId) {
		$employeeData = $this->fetchEmployee($employeeId);
		$this->posAccount->business->employees()->create([
			'external_id' => $employeeId,
			'first_name' => $employeeData['firstName'],
			'last_name' => $employeeData['lastName'],
		]);
	}

	public function fetchEmployee($employeeId) {
		$url = $this->setUrlValue('account_id', $this->account_id, $this->getBaseUrl() . config('urls.lightspeed_retail.employee'));
		$url = $this->setUrlValue('employee_id', $employeeId, $url);
		$headers = $this->getHeaders();

		if (env('APP_ENV') == 'testing' && $this->access_token == 'not_token') {
			$response = TestHelpers::fakeEmployeeFetch($employeeId);
		} else {
			$response = $this->doHttpRequest('get', $url, $headers);
			$this->handleError($response, $this->posAccount->business);
		}
		$response = $this->parseHttpResponse($response);
		return $response['Employee'];
	}

	public function renewToken() {
		if ($this->expiry < time()) {
			$response = $this->refreshToken();
			$this->update([
				'access_token' => $response['access_token'],
				'expiry' => $this->setTokenExpiry($response['expires_in'])
			]);
		}
	}

	public function doHttpRequest($type, $url, $headers, $body = null) {
		$this->renewToken();
		if ($body) {
			return $this->createHttpHandler()->{$type}($url, $headers, $body);
		} else {
			return $this->createHttpHandler()->{$type}($url, $headers);
		}
	}

	public function refreshToken() {
		$url = config('urls.lightspeed_retail.oauth_token');
		$headers = [
			"Content-Type" => "application/json",
			"Accept" => "application/json"
		];
		$body = [
			'client_id' => env('LIGHTSPEED_RETAIL_CLIENT_ID'),
			'client_secret' => env('LIGHTSPEED_RETAIL_SECRET'),
			'refresh_token' => $this->refresh_token,
			'grant_type' => 'refresh_token'
		];

		if (env('APP_ENV') == 'testing' && $this->access_token == 'not_token') {
			$response = TestHelpers::fakeLightspeedRetailRefreshTokenResponse();
		} else {
			$response = $this->createHttpHandler()->post($url, $headers, $body);
			$this->handleError($response, $this->posAccount->business);
		}

		return $this->parseHttpResponse($response);
	}

	public function setTokenExpiry($timeToExpire) {
		return time() + $timeToExpire;
	}
}
