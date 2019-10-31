<?php

use App\Models\Refund;
use App\Models\Transaction;
use Faker\Generator as Faker;

$factory->define(Refund\RefundStatus::class, function (Faker $faker) {
	return [
		'name' => 'refund pending',
		'code' => 100
	];
});

$factory->define(Refund\Refund::class, function (Faker $faker) {
	return [
		'transaction_id' => function() {
			return factory(Transaction\Transaction::class)->create()->id;
		},
		'status_id' => function() {
			return factory(Refund\RefundStatus::class)->create()->id;
		},
		'total' => 2035,
		'pos_refund_id' => 'wjQNUotqMddUfHApqfn5S',
		'payment_refund_id' => 'cnu982ru32fewy'
	];
});