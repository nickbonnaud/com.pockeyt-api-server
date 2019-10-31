<?php

use App\Models\Transaction;
use App\Models\Customer;
use App\Models\Business;
use Faker\Generator as Faker;

$factory->define(Transaction\TransactionStatus::class, function (Faker $faker) {
	return [
		'name' => 'open',
		'code' => 0
	];
});

$factory->define(Transaction\Transaction::class, function (Faker $faker) {
	$customerProfile = factory(Customer\CustomerProfilePhoto::class)->create()->profile;
	$customer = $customerProfile->customer;
	$business = factory(Business\Business::class)->create();
	factory(Business\AccountStatus::class)->create(['name' => 'incomplete']);
	$account = factory(Business\Account::class)->create(['business_id' => $business->id]);
	$payFacAccount = factory(Business\PayFacAccount::class)->create(['account_id' => $account->id]);
	factory(Business\PayFacBusiness::class)->create(['pay_fac_account_id' => $payFacAccount->id]);
	$posAccount = factory(Business\PosAccount::class)->create(['business_id' => $business->id]);
	factory(Business\SquareAccount::class)->create(['pos_account_id' => $posAccount->id]);
	factory(Business\CloverAccount::class)->create(['pos_account_id' => $posAccount->id]);

	$netSales = $faker->numberBetween($min = 500, $max = 10000);
	$tax = round(0.075 * $netSales);
	$tip = $faker->boolean ? round(($netSales + $tax) * ($faker->numberBetween($min = 5, $max = 25) / 100)) : 0;

	return [
		'customer_id' => $customer->id,
		'business_id' => $business->id,
		'status_id' => function() {
			return factory(Transaction\TransactionStatus::class)->create()->id;
		},
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
	factory(Transaction\TransactionStatus::class)->create(['name' => 'open']);
	$business = factory(Business\Business::class)->create();
	factory(Business\AccountStatus::class)->create(['name' => 'incomplete']);
	$account = factory(Business\Account::class)->create(['business_id' => $business->id]);
	$payFacAccount = factory(Business\PayFacAccount::class)->create(['account_id' => $account->id]);
	factory(Business\PayFacBusiness::class)->create(['pay_fac_account_id' => $payFacAccount->id]);
	$posAccount = factory(Business\PosAccount::class)->create(['business_id' => $business->id]);
	factory(Business\SquareAccount::class)->create(['pos_account_id' => $posAccount->id]);
	factory(Business\CloverAccount::class)->create(['pos_account_id' => $posAccount->id]);

	return [
		'business_id' => $business->id,
		'pos_transaction_id' => 'vcbdsigy72r2oibfw9ibf',
		'tax' => 35,
		'net_sales' => 2000,
		'total' => 2035,
	];
});

$factory->define(Transaction\UnassignedTransactionPurchasedItem::class, function (Faker $faker) {
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


