<?php

namespace App\Models\Transaction;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use App\Models\Business\ActiveItem;
use App\Models\Transaction\TransactionStatus;
use App\Models\Transaction\PurchasedItem;

class Transaction extends Model {
  
  //////////////////// Traits ////////////////////

	use \BinaryCabin\LaravelUUID\Traits\HasUUID;

	//////////////////// Attribute Mods/Helpers ////////////////////

	protected $guarded = ['identifier'];
	protected $hidden = [ 'id', 'customer_id', 'business_id', 'status_id', 'payment_transaction_id', 'pos_transaction_id', 'created_at'];
	protected $uuidFieldName = 'identifier';

	//////////////////// Routing ////////////////////

	public function getRouteKeyName() {
		return 'identifier';
	}

	//////////////////// Relationships ////////////////////

	public function status() {
		return $this->belongsTo('App\Models\Transaction\TransactionStatus');
	}

	public function customer() {
		return $this->belongsTo('App\Models\Customer\Customer');
	}

	public function business() {
		return $this->belongsTo('App\Models\Business\Business');
	}

	public function employee() {
		return $this->belongsTo('App\Models\Business\Employee', 'employee_id', 'external_id');
	}

	public function notification() {
		return $this->hasOne('App\Models\Transaction\TransactionNotification');
	}

	public function refunds() {
		return $this->hasMany('App\Models\Refund\Refund');
	}

	public function purchasedItems() {
		return $this->hasMany('App\Models\Transaction\PurchasedItem');
	}

	public function activeCustomerLocation() {
  	return $this->hasOne('App\Models\Location\ActiveLocation');
  }

  public function historicCustomerLocation() {
  	return $this->hasOne('App\Models\Location\HistoricLocation');
  }

	//////////////////// Core Methods ////////////////////

	public static function createTransaction($unassignedTransaction) {
		$billCreatedAt = $unassignedTransaction->created_at;
		$unassignedTransaction = $unassignedTransaction->toArray();
		$unassignedTransaction['bill_created_at'] = $billCreatedAt;
		return self::create($unassignedTransaction);
	}

	public function addPurchasedItems($unassignedTransaction) {
		$unassignedTransaction->purchasedItems->each(function($item, $key) {
			$this->purchasedItems()->save(new PurchasedItem($item->toArray()));
			$item->delete();
		});
	}

	public static function createTransactionFromSquare($activeLocation, $paymentResponse, $transactionResponse, $squareAccount) {
		
		return self::create([
			'customer_id' => $activeLocation->customer_id,
			'business_id' => $activeLocation->location->business_id,
			'pos_transaction_id' => $transactionResponse['transaction']['id'],
			'employee_id' => Arr::get($paymentResponse, 'tender.0.employee_id', null),
			'tax' => $paymentResponse['tax_money']['amount'],
			'tip' => $squareAccount->posAccount->takes_tips ? $paymentResponse['tip_money']['amount'] : 0,
			'net_sales' => $paymentResponse['net_sales_money']['amount'],
			'total' => $paymentResponse['net_total_money']['amount'],
			'bill_created_at' => $paymentResponse['created_at'],
			'status_id' => (TransactionStatus::where('name', 'closed')->first())->id
		]);
	}

	public function storePurchasedItemsSquare($paymentResponse, $business) {
		foreach ($paymentResponse['itemizations'] as $item) {
			$activeItem = ActiveItem::findOrCreateItemSquare($item, $business);
			$this->saveActiveItem($activeItem);
		}
	}

	public function updateTransactionClover($order, $netSales, $payments) {
		$this->update([
			'employee_id' => Arr::get($order, 'employee.id', null),
			'tax' => $order['total'] - $netSales,
			'net_sales' => $netSales,
			'total' => $order['total'],
			'partial_payment' => $payments
		]);
	}

	public function storePurchasedItemsClover($order, $business) {
		if ($this->purchasedItems->count() > 0) {
			$this->purchasedItems()->delete();
		}

		foreach ($order['lineItems']['elements'] as $item) {
			$activeItem = ActiveItem::findOrCreateItemClover($item, $business);
			$this->saveActiveItem($activeItem);
		}
	}

	public static function createTransactionFromLightspeedRetail($lightspeedSale, $customer, $business) {
		return self::create([
			'customer_id' => $customer->id,
			'business_id' => $business->id,
			'pos_transaction_id' => $lightspeedSale['saleID'],
			'employee_id' => $lightspeedSale['employeeID'],
			'tax' => $lightspeedSale['calcTax1'] + $lightspeedSale['calcTax2'],
			'net_sales' => $lightspeedSale['totalDue'] - ($lightspeedSale['calcTax1'] + $lightspeedSale['calcTax2']),
			'total' => $lightspeedSale['totalDue'],
			'bill_created_at' => $lightspeedSale['createTime'],
			'status_id' => (TransactionStatus::where('name', 'closed')->first())->id
		]);
	}

	public function storePurchasedItemsLightspeedRetail($lightspeedSale, $business) {
		foreach ($lightspeedSale['SaleLines']['SaleLine'] as $item) {
			$activeItem = ActiveItem::findOrCreateItemLightspeedRetail($item, $business);
			$this->saveActiveItem($activeItem);
		}
	}

	public function saveActiveItem($activeItem) {
		$this->purchasedItems()->save(new PurchasedItem(['item_id' => $activeItem->id]));
	}

	public function removeActiveItem($activeItem) {
		foreach ($this->purchasedItems as $item) {
			if ($item->item_id == $activeItem->id) {
				$item->delete();
				break;
			}
		}
	}

	public function formatMoney($amount) {
		return number_format(round($amount / 100, 2), 2);
	}

	public static function createTransactionFromShopify($customer, $orderData, $businessId) {
		return self::create([
			'customer_id' => $customer->id,
			'business_id' => $businessId,
			'pos_transaction_id' => $orderData['id'],
			'tax' => $orderData['total_tax'] * 100,
			'net_sales' => $orderData['subtotal_price'] * 100,
			'tip' => $orderData['total_tip_received'] * 100,
			'total' => $orderData['total_price'] * 100,
			'bill_created_at' => $orderData['created_at'],
			'status_id' => (TransactionStatus::where('name', 'closed')->first())->id
		]);
	}

	public function storePurchasedItemsShopify($orderData, $business) {
		foreach ($orderData['line_items'] as $item) {
			$i = 0;
			while ($i < $item['quantity']) {
				$activeItem = ActiveItem::findOrCreateItemShopify($item, $business);
				$this->saveActiveItem($activeItem);
				$i++;
			}
		}
	}

	public function updateTransactionShopify($orderData, $refundData) {
		if ($orderData['order']['total_price'] * 100 == $refundData['transactions'][0]['amount'] * 100) {
			$this->delete();
		} else {
			$taxRefund = 0;
			$netSalesRefund = 0;
			foreach ($refundData['refund_line_items'] as $refundedItem) {
				$taxRefund = $taxRefund + $refundedItem['total_tax'] * 100;
				$netSalesRefund = $netSalesRefund + $refundedItem['subtotal'] * 100;
				$this->removePurchasedItems($refundedItem['line_item']['product_id'], $refundedItem['quantity'], $refundedItem['line_item']['variant_id']);
			}
			$this->update([
				'tax' => $this->tax - $taxRefund,
				'net_sales' => $this->net_sales - $netSalesRefund,
				'total' => $this->total - $taxRefund - $netSalesRefund
			]);
		}
	}

	private function removePurchasedItems($mainId, $quantity, $subId) {
		$activeItem = ActiveItem::where('inventory_id', $this->business->inventory->id)
			->where('main_id', $mainId)
			->where('sub_id', $subId)
			->first();

		$activeItem->purchasedItems()->where('transaction_id', $this->id)->take($quantity)->delete();
	}

	public static function createTransactionFromVend($customer, $saleData, $businessId) {
		return self::create([
			'customer_id' => $customer->id,
			'business_id' => $businessId,
			'pos_transaction_id' => $saleData->id,
			'employee_id' => $saleData->user_id,
			'tax' => $saleData->totals->total_tax * 100,
			'net_sales' => $saleData->totals->total_price * 100,
			'total' => $saleData->totals->total_payment * 100,
			'bill_created_at' => $saleData->created_at,
			'status_id' => (TransactionStatus::where('name', 'closed')->first())->id
		]);
	}

	public function updateTransactionVend($saleData) {
		if (($saleData->totals->total_payment * 100 ) + $this->total == 0) {
			$this->delete();
		} else {
			$this->update([
				'tax' => $this->tax + ($saleData->totals->total_tax * 100),
				'net_sales' => $this->net_sales + ($saleData->totals->total_price * 100),
				'total' => $this->total + ($saleData->totals->total_payment * 100),
			]);
			$this->storePurchasedItemsVend($saleData, $this->business, $this->business->posAccount->vendAccount);
		}
	}

	public function storePurchasedItemsVend($saleData, $business, $vendAccount) {
		foreach ($saleData->register_sale_products as $item) {
			$i = 0;
			while ($i < abs($item->quantity)) {
				$activeItem = ActiveItem::findItemVend($item, $business);
				if (is_null($activeItem)) {
					$productData = $vendAccount->fetchProduct($item);
					$activeItem = ActiveItem::createItemVend($productData, $business);
				}
				if ($item->price_total < 0) {
					$this->removeActiveItem($activeItem);
				} else {
					$this->saveActiveItem($activeItem);
				}
				$i++;
			}
		}
	}

	public function scopeFilter($query, $filters) {
		return $filters->apply($query);
	}
}
