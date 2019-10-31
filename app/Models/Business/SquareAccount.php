<?php

namespace App\Models\Business;

use App\Helpers\TestHelpers;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use App\Models\Business\Inventory;
use App\Model\Business\Employee;
use App\Models\Customer\Customer;
use App\Handlers\Http\HttpHandler;
use App\Handlers\Errors\HttpErrorHandler;
use App\Models\Transaction\Transaction;
use App\Models\Refund\Refund;
use Illuminate\Database\Eloquent\Model;

class SquareAccount extends Model {
  
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
		return config('urls.square.base');
	}

	public function getHeaders() {
		return [
			"Authorization" => "Bearer {$this->access_token}", 
			"Content-Type" => "application/json"
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

	public function authorizeSquare($code, $business) {
		$response = $this->postSquareAuthCode($code);
		return $this->createSquareAccount($response, $business);
	}

	public function createSquareAccount($response, $business) {
		return $this->create([
			'pos_account_id' => $business->posAccount->id,
			'access_token' => $response['access_token'],
			'merchant_id' => $response['merchant_id'],
			'refresh_token' => $response['refresh_token'],
			'expiry' => $response['expires_at']
		]);
	}

	public function postSquareAuthCode($code) {
		$url =  $this->getBaseUrl() . config('urls.square.oauth_token');
		$headers = [
			"Content-Type" => "application/json"
		];
		$body = [
			'client_id' => env('SQUARE_CLIENT_ID'),
			'client_secret' => env('SQUARE_SECRET'),
			'code' => $code,
			'grant_type' => 'authorization_code'
		];

		if (env('APP_ENV') == 'testing') {
			$response = TestHelpers::fakeSquareAuthResponse();
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

	public function fetchLocationId() {
		$url = $this->getBaseUrl() . config('urls.square.locations');
		$headers = $this->getHeaders();

		if (env('APP_ENV') == 'testing' && $this->access_token == 'not_token') {
			if ($this->getBusinessAddress() == "321 Broad St") {
				$response = TestHelpers::fakeSquareLocationResponseDouble();
			} else {
				$response = TestHelpers::fakeSquareLocationResponseSingle();
			}
		} else {
			$response = $this->createHttpHandler()->get($url, $headers);
			$this->handleError($response, $this->posAccount->business);
		}
		return $this->parseLocationResponseForId($this->parseHttpResponse($response));
	}

	public function parseLocationResponseForId($response) {
		if (count($response['locations']) == 1) {
      return $response['locations'][0]['id'];
    } else {
      foreach ($response['locations'] as $location) {
        if (strtolower($location['address']['address_line_1']) == strtolower($this->getBusinessAddress())) {
          return $location['id'];
        }
      }
    }
	}

	public function getBusinessAddress() {
		return $this->posAccount->business->account->payFacAccount->payFacBusiness->address;
	}

	public function fetchInventoryItems($url = null) {
		$url = $url ? $url : $this->getBaseUrl() . $this->setUrlValue('location_id', $this->location_id, config('urls.square.inventory'));
		$headers = $this->getHeaders();

		if (env('APP_ENV') == 'testing' && $this->access_token == 'not_token') {
			$response = TestHelpers::fakeSquareInventoryResponse();
		} else {
			$response = $this->createHttpHandler()->get($url, $headers);
			$this->handleError($response, $this->posAccount->business);
		}

		$this->storeInventoryItems($this->parseHttpResponse($response));
		if ($nextUrl = $this->getPaginateUrl($response)) {
			$this->fetchInventoryItems($nextUrl);
		}
	}

	public function getPaginateUrl($response) {
		if (env('APP_ENV') == 'testing' && $this->access_token == 'not_token') {
			if ($paginationHeader = TestHelpers::paginateHeader()) {
				if (strpos($paginationHeader, "rel='next'") !== false) {
					return explode('>', explode('<', $paginationHeader)[1])[0];
				}
			}
		} else {
			if ($paginationHeader = $response->header('Link')) {
				if (strpos($paginationHeader, "rel='next'") !== false) {
					return explode('>', explode('<', $paginationHeader)[1])[0];
				}
			}
		}
		return;
	}

	public function storeInventoryItems($items) {
		$inventory = $this->getInventory();
		foreach ($items as $item) {
			foreach ($item['variations'] as $variation) {
				$this->storeItem($inventory, $item, $variation);
			}
		}
	}

	public function storeItem($inventory, $item, $variation) {
		$itemName = $item['name'];
		$categoryName = Arr::has($item, 'category') ? $item['category']['name'] : null;

		$inventory->activeItems()->create([
			'main_id' => $variation['item_id'],
			'sub_id' => $variation['id'],
			'name' => $itemName,
			'sub_name' => $variation['name'],
			'category' => $categoryName,
			'price' => optional($variation['price_money'])['amount'] ? $variation['price_money']['amount'] : 0
		]);
	}

	public function getInventory() {
		return Inventory::firstOrCreate(
			['business_id' => $this->posAccount->business_id],
			['business_id' => $this->posAccount->business_id]);
	}

	public function createWebHook() {
		$cleanUrl = $this->setUrlValue('location_id', $this->location_id, config('urls.square.web_hook'));
		$url = $this->getBaseUrl() . $cleanUrl;
		$body = ["PAYMENT_UPDATED"];
		$headers = $this->getHeaders();

		if (env('APP_ENV') == 'testing') {
			$error = null; 
		} else {
			$response = $this->createHttpHandler()->put($url, $headers, $body);
			$this->handleError($response, $this->posAccount->business);
		}
	}

	public function fetchPayment($webhookData) {
		$cleanUrl = $this->setUrlValue('location_id', $this->location_id, config('urls.square.payment'));
		$cleanUrl = $this->setUrlValue('entity_id', $webhookData['entity_id'], $cleanUrl);
		$url = $this->getBaseUrl() . $cleanUrl;
		$headers = $this->getHeaders();

		if (env('APP_ENV') == 'testing' && $this->access_token == 'not_token') {
			$response = TestHelpers::fakeSquarePaymentFetch();
		} else {
			$response = $this->createHttpHandler()->get($url, $headers);
			$this->handleError($response, $this->posAccount->business);
		}

		$this->fetchTransaction($this->parseHttpResponse($response));
	}

	public function fetchTransaction($paymentResponse) {
		$transactionId = str_replace("/", "", (strrchr($paymentResponse['payment_url'], '/')));
		$cleanUrl = $this->setUrlValue('location_id', $this->location_id, config('urls.square.transaction'));
		$cleanUrl = $this->setUrlValue('transaction_id', $transactionId, $cleanUrl);
		$url = $this->getBaseUrl() . $cleanUrl;
		$headers = $this->getHeaders();

		if (env('APP_ENV') == 'testing' && $this->access_token == 'not_token') {
			$response = TestHelpers::fakeSquareTransactionFetch();
		} else {
			$response = $this->createHttpHandler()->get($url, $headers);
			$this->handleError($response, $this->posAccount->business);
		}

		$this->processTransaction($this->parseHttpResponse($response), $paymentResponse);
	}

	public function processTransaction($transactionResponse, $paymentResponse) {
		foreach ($transactionResponse['transaction']['tenders'] as $transaction) {
			if (isset($transaction['customer_id'])) {
				$refId = $this->getCustomerRefId($transaction['customer_id']);
				if ($refId) {
					$customer = $this->getCustomer($refId);
					if ($this->checkIfRefund($transactionResponse)) {
						$this->refundTransaction($transactionResponse);
					} else {
						$this->chargeTransaction($transactionResponse, $paymentResponse, $customer);
					}
				}
			}
		}
	}

	public function checkIfRefund($transactionResponse) {
		if (Arr::has($transactionResponse, 'transaction.refunds')) {
			foreach (Arr::get($transactionResponse, 'transaction.refunds') as $refund) {
				if (Refund::where('pos_refund_id', $refund['id'])->doesntExist() && Transaction::where('pos_transaction_id', Arr::get($transactionResponse, 'transaction.id'))->exists()) {
				 	return true;
				}
			}
		}
		return false;
	}

	public function refundTransaction($transactionResponse) {
		Refund::createRefundFromSquare($transactionResponse);
	}

	public function chargeTransaction($transactionResponse, $paymentResponse, $customer) {
		$activeLocation = $this->getActiveLocation($customer);
		if ($activeLocation) {
			$transaction = Transaction::createTransactionFromSquare($activeLocation, $paymentResponse, $transactionResponse, $this);
			$transaction->storePurchasedItemsSquare($paymentResponse, $this->posAccount->business);
		} else {
			$this->sendNoActiveLocationNotif($customer);
		}
	}

	public function sendNoActiveLocationNotif($customer) {
		dd('no active location');
	}

	public function getActiveLocation($customer) {
		return $customer->activeLocations()->where("location_id", $this->posAccount->business->location->id)->first();
	}

	public function getCustomer($refId) {
		return Customer::where('identifier', $refId)->first();
	}

	public function getCustomerRefId($customerId) {
		$url = $this->getBaseUrl() . $this->setUrlValue('customer_id', $customerId, config('urls.square.customer'));
		$headers = $this->getHeaders();

		if (env('APP_ENV') == 'testing' && $this->access_token == 'not_token') {
			$response = TestHelpers::fakeSquareCustomerFetch();
		} else {
			$response = $this->createHttpHandler()->get($url, $headers);
			$this->handleError($response, $this->posAccount->business);
		}

		$response = $this->parseHttpResponse($response);

		if (isset($response['customer']['reference_id']) && Str::contains($response['customer']['reference_id'], strtolower(env('BUSINESS_NAME')))) {
			return str_replace(strtolower(env('BUSINESS_NAME')) . "_", "", $response['customer']['reference_id']);
		}
		return;
	}

	public function createCustomer($customer) {
		$url = $this->getBaseUrl() . config('urls.square.create_customer');
		$headers = $this->getHeaders();
		$body = [
			'idempotency_key' => Str::random(25),
			'given_name' => $customer->profile->first_name,
			'family_name' => $customer->profile->last_name,
			'email_address' => $customer->email,
			'reference_id' => strtolower(env('BUSINESS_NAME')) . "_" . $customer->identifier,
			'note' => env('BUSINESS_NAME') . " Customer"
		];

		if (env('APP_ENV') == 'testing' && $this->access_token == 'not_token') {
			$response = TestHelpers::fakeSquareCreateCustomer($body);
		} else {
			$response = $this->createHttpHandler()->post($url, $headers, $body);
			$this->handleError($response, $this->posAccount->business);
		}
		return ($this->parseHttpResponse($response))['customer']['id'];
	}

	public function destroyCustomer($billIdentifier) {
		$url = $this->getBaseUrl() . $this->setUrlValue('customer_id', $billIdentifier, config('urls.square.destroy_customer'));
		$headers = $this->getHeaders();
		if (env('APP_ENV') == 'testing' && $this->access_token == 'not_token') {
			// do nothing
		} else {
			$response = $this->createHttpHandler()->delete($url, $headers);
			$this->handleError($response, $this->posAccount->business);
		}
	}

	public function createEmployee($employeeId) {
		$employeeData = $this->fetchEmployee($employeeId);
		$this->posAccount->business->employees()->create([
			'external_id' => $employeeId,
			'first_name' => $employeeData['first_name'],
			'last_name' => $employeeData['last_name'],
			'email' => Arr::has($employeeData, 'email') ? $employeeData['email'] : null
		]);
	}

	public function fetchEmployee($employeeId) {
		$url = $this->getBaseUrl() . $this->setUrlValue('employee_id', $employeeId, config('urls.square.employee'));
		$headers = $this->getHeaders();

		if (env('APP_ENV') == 'testing' && $this->access_token == 'not_token') {
			$response = TestHelpers::fakeSquareEmployeeFetch($employeeId);
		} else {
			$response = $this->createHttpHandler()->get($url, $headers);
			$this->handleError($response, $this->posAccount->business);
		}

		$response = $this->parseHttpResponse($response);
		return $response['employee'];
	}
}
