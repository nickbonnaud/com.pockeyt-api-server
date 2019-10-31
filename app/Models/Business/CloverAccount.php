<?php

namespace App\Models\Business;

use Illuminate\Support\Arr;
use App\Models\Refund\Refund;
use App\Helpers\CloverTestHelpers;
use App\Handlers\Http\HttpHandler;
use App\Models\Transaction\Transaction;
use App\Models\Transaction\TransactionStatus;
use App\Models\Transaction\UnassignedTransaction;
use App\Handlers\Errors\HttpErrorHandler;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\Log;

class CloverAccount extends Model {
  
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
		return config('urls.clover.base');
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

	public function authorizeClover($code, $merchantId, $business) {
		$response = $this->getCloverAccessToken($code, $business);
		return $this->createCloverAccount($response, $merchantId, $business);
	}

	public function createCloverAccount($response, $merchantId, $business) {
		return $this->create([
			'pos_account_id' => $business->posAccount->id,
			'access_token' => $response['access_token'],
			'merchant_id' => $merchantId
		]);
	}

	public function getCloverAccessToken($code, $business) {
		$url = $this->getBaseUrl() . config('urls.clover.oauth_token');
		$headers = [
			"Content-Type" => "application/json"
		];
		$queryParams = [
			'client_id' => env('CLOVER_APP_ID'),
			'client_secret' => env('CLOVER_APP_SECRET'),
			'code' => $code
		];

		if (env('APP_ENV') == 'testing') {
			$response = CloverTestHelpers::fakeCloverAuthResponse();
		} else {
			$response = $this->createHttpHandler()->get($url, $headers, $queryParams);
			$this->handleError($response, $business);
		}

		if (env('APP_ENV') == 'testing') {
			return json_decode($response, true);
		} else {
			return $response->json();
		}

		return $this->parseHttpResponse($response);
	}

	public function fetchInventoryItems($url = null) {
		$url = $url ? $url : $this->getBaseUrl() . $this->setUrlValue('merchant_id', $this->merchant_id, config('urls.clover.inventory'));
		$headers = $this->getHeaders();

		if (env('APP_ENV') == 'testing' && $this->access_token == 'not_token') {
			$response = CloverTestHelpers::fakeInventoryResponse($url);
		} else {
			$response = $this->createHttpHandler()->get($url, $headers);
			$this->handleError($response, $this->posAccount->business);
		}

		$response = $this->parseHttpResponse($response);
		$this->storeInventoryItems($response);
		if ($nextUrl = $this->paginateUrl($response)) {
			$this->fetchInventoryItems($nextUrl);
		}
	}

	public function paginateUrl($response) {
		parse_str((parse_url($response['href']))['query'], $query);
		$limit = $query['limit'];
		$offset = $query['offset'];
		if (count($response['elements']) == $limit) {
			$nextOffset = $limit + $offset;
			return str_replace("offset={$offset}", "offset={$nextOffset}", $response['href']);
		}
		return;
	}

	public function storeInventoryItems($response) {
		$inventory = $this->getInventory();
		foreach ($response['elements'] as $item) {
			$this->storeItem($item, $inventory);
		}
	}

	public function storeItem($item, $inventory) {
		$inventory->activeItems()->create([
			'main_id' => $item['id'],
			'name' => $item['name'],
			'sub_name' => $item['alternateName'] == "" ? null : $item['alternateName'],
			'price' => $item['price']
		]);
	}

	public function getInventory() {
		return Inventory::firstOrCreate(
			['business_id' => $this->posAccount->business_id],
			['business_id' => $this->posAccount->business_id]);
	}

	public function handleWebhook($merchantUpdates) {
		foreach ($merchantUpdates as $update) {
			$orderId = str_replace("O:", "", $update['objectId']);
			if ($update['type'] == "DELETE") {
				$this->webhookDeleteTransactionHandler($orderId);
			} else {
				$order = $this->fetchOrder($orderId);
				if ($update['type'] == "CREATE") {
					$this->webhookCreateTransactionHandler($order);
				} elseif ($update['type'] == "UPDATE") {
					$this->webhookUpdateTransactionHandler($order);
				}
			}
		}
	}

	public function webhookDeleteTransactionHandler($orderId) {
		$unassignedTransaction = $this->posAccount
																	->business
																	->unassignedTransactions
																	->where('pos_transaction_id', $orderId)
																	->first();

		if ($unassignedTransaction) {
			$unassignedTransaction->delete();
		} else {
			$transaction = $this->posAccount
													->business
													->transactions
													->where('pos_transaction_id', $orderId)
													->first();
			if ($transaction && $transaction->status->name != "paid") {
				$transaction->delete();
			}
		}
	}

	public function webhookUpdateTransactionHandler($order) {
		$unassignedTransaction = $this->posAccount
																	->business
																	->unassignedTransactions
																	->where('pos_transaction_id', $order['id'])
																	->first();

		if ($unassignedTransaction) {
			$this->updateUnassignedTransaction($unassignedTransaction, $order);
		} else {
			$transaction = $this->posAccount
													->business
													->transactions
													->where('pos_transaction_id', $order['id'])
													->first();
			if ($this->checkOrderForRefund($order)) {
				Refund::createRefundFromClover($order, $transaction, $this->tender_id);
			} else {
				$this->updateTransaction($transaction, $order);
			}
		}
	}

	public function updateUnassignedTransaction($unassignedTransaction, $order) {
		$payments = $this->getOrderPayments($order);
		if ($payments == $order['total']) {
			$unassignedTransaction->delete();
		} else {
			$netSales = $this->getOrderNetSales($order);
			$unassignedTransaction->updateTransactionClover($order, $netSales, $payments);
			$unassignedTransaction->storePurchasedItemsClover($order, $this->posAccount->business);
		}
	}

	public function updateTransaction($transaction, $order) {
		$payments = $this->getOrderPayments($order);
		if ($payments == $order['total']) {
			if (!in_array($this->tender_id, $this->getOrderTenderIds($order))) {
				$transaction->delete();
			}
		} else {
			$netSales = $this->getOrderNetSales($order);
			$transaction->updateTransactionClover($order, $netSales, $payments);
			$transaction->storePurchasedItemsClover($order, $this->posAccount->business);
		}
	}

	public function getOrderTenderIds($order) {
		$tenderIds = [];
		foreach ($order['payments']['elements'] as $payment) {
			array_push($tenderIds, $payment['tender']['id']);
		}
		return $tenderIds;
	}

	public function getOrderNetSales($order) {
		$netSales = 0;
		foreach ($order['lineItems']['elements'] as $item) {
			$netSales = $netSales + $item['price'];
		}
		return $netSales;
	}

	public function getOrderPayments($order) {
		$payments = 0;
		if (Arr::has($order, 'payments')) {
			foreach ($order['payments']['elements'] as $payment) {
				$payments = $payments + $payment['amount'];
			}
		}
		return $payments;
	}

	public function webhookCreateTransactionHandler($order) {
		if (Arr::has($order, 'payments')) {
			if ($order['payType'] != 'FULL') {
				$this->createUnAssignedTransaction($order);
			}
		} else {
			$this->createUnAssignedTransaction($order);
		}
	}

	public function createUnAssignedTransaction($order) {
		$unassignedTransaction = UnassignedTransaction::createTransactionFromClover($order, $this->posAccount->business_id);
		if ($unassignedTransaction) {
			$unassignedTransaction->storePurchasedItemsClover($order, $this->posAccount->business);
		}
	}

	public function fetchOrder($orderId) {
		$url = $this->setUrlValue('merchant_id', $this->merchant_id, config('urls.clover.order'));
		$url = $this->setUrlValue('order_id', $orderId, $url);
		$url = $this->getBaseUrl() . $url . "?expand=payments,lineItems,refunds";
		$headers = $this->getHeaders();

		if (env('APP_ENV') == 'testing' && $this->access_token == 'not_token') {
			$response = CloverTestHelpers::fakeOrderResponse($orderId);
		} else {
			$response = $this->createHttpHandler()->get($url, $headers);
			$this->handleError($response, $this->posAccount->business);
		}
		$response = $this->parseHttpResponse($response);
		return $response;
	}

	public function checkOrderForRefund($order) {
		if (Arr::has($order, "refunds")) {
			foreach ($order['refunds']['elements'] as $refund) {
				if ($refund['payment']['tender']['id'] == $this->tender_id) {
					return true;
				}
			}
		}
		return false;
	}

	public function closeOrder($transaction) {
		$url = $this->setUrlValue('merchant_id', $this->merchant_id, config('urls.clover.close_order'));
		$url = $this->getBaseUrl() . $this->setUrlValue('order_id', $transaction->pos_transaction_id, $url);
		$headers = $this->getHeaders();

		$body = [
			"order" => [
				'id' => $transaction->pos_transaction_id
			],
			"tender" => [
				'id' => $this->tender_id
			],
			'amount' => $transaction->total - $transaction->tip,
			'tipAmount' => $transaction->tip,
			'taxAmount' => $transaction->tax

		];

		if (env('APP_ENV') == 'testing' && $this->access_token == 'not_token') {
			$response = CloverTestHelpers::fakeOrderResponse($transaction->pos_transaction_id);
		} else {
			$response = $this->createHttpHandler()->post($url, $headers, $body);
			$this->handleError($response, $this->posAccount->business);
		}
	}

	public function addCustomerNote($customer, $transaction) {
		$url = $this->setUrlValue('merchant_id', $this->merchant_id, config('urls.clover.order'));
		$url = $this->getBaseUrl() . $this->setUrlValue('order_id', $transaction->pos_transaction_id, $url);
		$headers = $this->getHeaders();
		$body = [
			'note' => env('BUSINESS_NAME') . " customer: {$customer->profile->first_name} {$customer->profile->last_name}"
		];

		if (env('APP_ENV') == 'testing' && $this->access_token == 'not_token') {
			$response = CloverTestHelpers::fakeAddNote($customer);
		} else {
			$response = $this->createHttpHandler()->post($url, $headers, $body);
			$this->handleError($response, $this->posAccount->business);
		}
		$response = $this->parseHttpResponse($response);
	}

	public function createEmployee($employeeId) {
		$employeeData = $this->fetchEmployee($employeeId);
		if ($index = strpos($employeeData['name'], ' ')) {
			$firstName = substr($employeeData['name'], 0, $index);
			$lastName = substr($employeeData['name'], $index + 1);
		} else {
			$firstName = Arr::has($employeeData, 'nickname') && $employeeData['nickname'] != "" ? $employeeData['nickname'] : $employeeData['name'];
			$lastName = $employeeData['name'];
		}
		$this->posAccount->business->employees()->create([
			'external_id' => $employeeId,
			'first_name' => $firstName,
			'last_name' => $lastName,
			'email' => Arr::has($employeeData, 'email') ? $employeeData['email'] : null
		]);
	}

	public function fetchEmployee($employeeId) {
		$url = $this->setUrlValue('merchant_id', $this->merchant_id, config('urls.clover.employee'));
		$url = $this->getBaseUrl() . $this->setUrlValue('employee_id', $employeeId, $url);
		$headers = $this->getHeaders();


		if (env('APP_ENV') == 'testing' && $this->access_token == 'not_token') {
			$response = CloverTestHelpers::fakeEmployeeFetch($employeeId);
		} else {
			$response = $this->createHttpHandler()->get($url, $headers);
			$this->handleError($response, $this->posAccount->business);
		}
		return $this->parseHttpResponse($response);
	}
}
