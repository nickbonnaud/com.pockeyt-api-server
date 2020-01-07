<?php

namespace App\Models\Refund;

use Illuminate\Support\Arr;
use App\Models\Refund\RefundStatus;
use Illuminate\Database\Eloquent\Model;
use App\Models\Transaction\Transaction;

class Refund extends Model {

	//////////////////// Traits ////////////////////

	use \BinaryCabin\LaravelUUID\Traits\HasUUID;

	//////////////////// Attribute Mods/Helpers ////////////////////

	protected $guarded = ['identifier'];
	protected $fillable = ['transaction_id', 'status_id', 'total', 'pos_refund_id'];
	protected $hidden = [ 'id', 'transaction_id', 'status_id', 'payment_refund_id', 'pos_refund_id'];
	protected $uuidFieldName = 'identifier';

	//////////////////// Routing ////////////////////

	public function getRouteKeyName() {
		return 'identifier';
	}

	//////////////////// Relationships ////////////////////

	public function transaction() {
		return $this->belongsTo('App\Models\Transaction\Transaction');
	}

	public function status() {
		return $this->belongsTo('App\Models\Refund\RefundStatus');
	}

	//////////////////// Core Methods ////////////////////

	public static function createRefundFromSquare($transactionResponse) {
		foreach (Arr::get($transactionResponse, 'transaction.refunds') as $refund) {
			$transaction = Transaction::where('pos_transaction_id', Arr::get($transactionResponse, 'transaction.id'))->first();
			if (self::where('pos_refund_id', $refund['id'])->doesntExist() && $transaction) {
			 	self::create([
			 		'transaction_id' => $transaction->id,
			 		'status_id' => (RefundStatus::where('name', 'refund_pending')->first())->id,
			 		'total' => Arr::get($refund, 'amount_money.amount'),
			 		'pos_refund_id' => $refund['id']
			 	]);
			}
		}
	}

	public static function createRefundFromClover($order, $transaction, $tenderId) {
		foreach ($order['refunds']['elements'] as $refund) {
			if ($refund['payment']['tender']['id'] == $tenderId && self::where('pos_refund_id', $refund['id'])->doesntExist()) {
				self::create([
			 		'transaction_id' => $transaction->id,
			 		'status_id' => (RefundStatus::where('name', 'refund_pending')->first())->id,
			 		'total' => $refund['amount'],
			 		'pos_refund_id' => $refund['id']
			 	]);
			}
		}
	}

	public static function createRefundFromLightspeedRetail($lightspeedRefund, $transaction) {
		self::create([
	 		'transaction_id' => $transaction->id,
	 		'status_id' => (RefundStatus::where('name', 'refund_pending')->first())->id,
	 		'total' => abs($lightspeedRefund['totalDue']),
	 		'pos_refund_id' => $lightspeedRefund['saleID']
	 	]);
	}

	public static function createRefundFromShopify($refundData, $transaction) {
		self::create([
	 		'transaction_id' => $transaction->id,
	 		'status_id' => (RefundStatus::where('name', 'refund_pending')->first())->id,
	 		'total' => $refundData['transactions'][0]['amount'] * 100,
	 		'pos_refund_id' => $refundData['id']
	 	]);
	}

	public static function createRefundFromVend($refundData, $transaction) {
		self::create([
			'transaction_id' => $transaction->id,
	 		'status_id' => (RefundStatus::where('name', 'refund_pending')->first())->id,
	 		'total' => abs($refundData->totals->total_payment) * 100,
	 		'pos_refund_id' => $refundData->id
		]);
	}

	public function scopeFilter($query, $filters) {
		return $filters->apply($query);
	}
}
