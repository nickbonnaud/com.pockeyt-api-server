<?php

use App\Models\Transaction;
use App\Models\Customer;
use App\Models\Business;
use Faker\Generator as Faker;

$factory->define(Transaction\Transaction::class, function (Faker $faker) {
	$netSales = $faker->numberBetween($min = 500, $max = 10000);
	$tax = round(0.075 * $netSales);
	$tip = $faker->boolean ? round(($netSales + $tax) * ($faker->numberBetween($min = 5, $max = 25) / 100)) : 0;

	return [
		'customer_id' => function() {
			return factory(Customer\Customer::class)->create()->id;
		},
		'business_id' => function() {
			return factory(Business\PosAccount::class)->create()->business_id;
		},
		'status_id' => Transaction\TransactionStatus::where('code', 100)->first()->id,
		'pos_transaction_id' => 'vcbdsigy72r2oibfw9ibf',
		'tax' => $tax,
		'tip' => $tip,
		'net_sales' => $netSales,
		'total' => $netSales + $tax + $tip,
		'bill_created_at' => now()
	];
});

$factory->define(Transaction\PurchasedItem::class, function (Faker $faker) {
	return [
		'transaction_id' => function() {
			return factory(Transaction\Transaction::class)->create()->id;
		},
		'item_id' => function() {
			return factory(Business\ActiveItem::class)->create()->id;
		}
	];
});

$factory->define(Transaction\UnassignedTransaction::class, function (Faker $faker) {
	$netSales = $faker->numberBetween($min = 500, $max = 10000);
	$tax = round(0.075 * $netSales);

	return [
		'business_id' => function() {
			return factory(Business\PosAccount::class)->create()->business_id;
		},
		'status_id' => Transaction\TransactionStatus::first()->id,
		'pos_transaction_id' => 'vcbdsigy72r2oibfw9ibf',
		'tax' => $tax,
		'net_sales' => $netSales,
		'total' => $netSales + $tax,
	];
});

$factory->define(Transaction\UnassignedPurchasedItem::class, function (Faker $faker) {
	return [
		'unassigned_transaction_id' => function() {
			return factory(Transaction\UnassignedTransaction::class)->create()->id;
		},
		'item_id' => function() {
			return factory(Business\ActiveItem::class)->create()->id;
		}
	];
});

$factory->define(Transaction\TransactionNotification::class, function (Faker $faker) {
	return [
		'transaction_id' => function() {
			return factory(Transaction\Transaction::class)->create()->id;
		},
		'last' => 'auto_pay_sent',
		'auto_pay_sent' => true,
		'time_auto_pay_sent' => now()
	];
});

$factory->define(Transaction\TransactionIssue::class, function (Faker $faker) {
	return [
		'transaction_id' => function() {
			return factory(Transaction\Transaction::class)->create()->id;
		},
		'type' => 'wrong_bill',
		'issue' => $faker->sentence,
		'resolved' => false,
		'prior_status_code' => 100
	];
});


