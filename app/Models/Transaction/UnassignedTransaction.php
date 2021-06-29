<?php

namespace App\Models\Transaction;

use Illuminate\Support\Arr;
use App\Models\Business\ActiveItem;
use Illuminate\Database\Eloquent\Model;
use App\Models\Transaction\UnassignedPurchasedItem;

class UnassignedTransaction extends Model {

	//////////////////// Traits ////////////////////

	use \BinaryCabin\LaravelUUID\Traits\HasUUID;

	//////////////////// Attribute Mods/Helpers ////////////////////

	protected $guarded = ['identifier'];
	protected $hidden = [ 'id'];
	protected $uuidFieldName = 'identifier';
	protected $casts = ['tax' => 'integer', 'net_sales' => 'integer', 'total' => 'integer'];

	//////////////////// Routing ////////////////////

	public function getRouteKeyName() {
		return 'identifier';
	}

	//////////////////// Relationships ////////////////////

	public function business() {
		return $this->belongsTo('App\Models\Business\Business');
	}

	public function purchasedItems() {
		return $this->hasMany('App\Models\Transaction\UnassignedPurchasedItem');
	}

	public function employee() {
		return $this->belongsTo('App\Models\Business\Employee', 'employee_id', 'external_id');
	}

	//////////////////// Core Methods ////////////////////

	public static function createTransactionFromClover($order, $businessId) {
		$netSales = 0;
		foreach ($order['lineItems']['elements'] as $item) {
			$netSales = $netSales + $item['price'];
		}
		$partialPayment = 0;
		if (Arr::has($order, 'payments')) {
			foreach ($order['payments']['elements'] as $payment) {
				$partialPayment = $partialPayment + $payment['amount'];
			}
		}

		if ($partialPayment != $order['total']) {
			return self::create([
				'business_id' => $businessId,
				'pos_transaction_id' => $order['id'],
				'employee_id' => Arr::get($order, 'employee.id', null),
				'tax' => $order['total'] - $netSales,
				'net_sales' => $netSales,
				'total' => $order['total'],
				'partial_payment' => $partialPayment
			]);
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
			$this->purchasedItems()->save(new UnassignedPurchasedItem(['item_id' => $activeItem->id]));
		}
	}

	public function assignCustomer($customer, $status) {
		$this->update([
			'customer_id' => $customer->id,
			'status_id' => $status->id
		]);
	}

	public function scopeFilter($query, $filters) {
		return $filters->apply($query);
	}

	public function formattedPurchashedItems() {
		$purchasedItems = $this->purchasedItems->map(function($item, $k) {
			return $item->getInventoryItem();
		});
		$uniquePurchasedItems = $purchasedItems->unique(function($item) {
			return $item['main_id'].$item['sub_id'];
		});
		return $uniquePurchasedItems->map(function($item, $k) use ($purchasedItems) {
			$item['quantity'] = $purchasedItems
				->where('main_id', $item->main_id)
				->where('sub_id', $item->sub_id)
				->count();
			return $item;
		});
	}
}
