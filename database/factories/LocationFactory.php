<?php

use App\Models\Location;
use App\Models\Customer;
use App\Models\Business;
use App\Models\Refund;
use App\Models\Transaction;
use Faker\Generator as Faker;

$factory->define(Location\Region::class, function (Faker $faker) {
	return [
		'city' => $faker->city,
		'state' => $faker->stateAbbr,
		'zip' => (string)($faker->randomNumber($nbDigits = 5, $strict = true)),
		'center_lat' => $faker->latitude,
		'center_lng' => $faker->longitude,
	];
});

$factory->define(Location\ActiveLocation::class, function (Faker $faker) {
	return [
		'bill_identifier' => $faker->name,
		'customer_id' => function() {
			return factory(Customer\Customer::class)->create()->id;
		},
		'location_id' => function() {
			return factory(Business\Location::class)->create()->id;
		},
	];

	// $profile = factory(Customer\CustomerProfilePhoto::class)->create()->profile;
	// $customer = $profile->customer;

	// $location = factory(Business\Location::class)->create();
	// $business = $location->business;
	// factory(Business\PosAccount::class)->create(['business_id' => $business->id, 'type' => 'other']);

	// $hasBill = $faker->boolean;
	// $hasRefund = $faker->boolean;

	// if ($hasBill) {
	// 	$transaction = factory(Transaction\Transaction::class)->create(['business_id' => $business->id]);
	// 	factory(Transaction\PurchasedItem::class, 4)->create(['transaction_id' => $transaction->id]);
	// 	if ($hasRefund) {
	// 		factory(Refund\Refund::class)->create(['transaction_id' => $transaction->id]);
	// 	}
	// 	$transactionId = $transaction->id;
	// } else {
	// 	$transactionId = null;
	// }
	// return [
	// 	'bill_identifier' => $hasBill ? strtolower(env('BUSINESS_NAME')) . "_" . $customer->identifier : null,
	// 	'customer_id' => $customer->id,
	// 	'location_id' => $location->id,
	// 	'transaction_id' => $transactionId,
	// ];
});

$factory->define(Location\HistoricLocation::class, function (Faker $faker) {
	return [
		'identifier' => $faker->uuid,
		'bill_identifier' => $faker->name,
		'customer_id' => function() {
			return factory(Customer\Customer::class)->create()->id;
		},
		'location_id' => function() {
			return factory(Business\Location::class)->create()->id;
		},
	];

	// $profile = factory(Customer\CustomerProfilePhoto::class)->create()->profile;
	// $customer = $profile->customer;

	// $location = factory(Business\Location::class)->create();
	// $business = $location->business;
	// factory(Business\PosAccount::class)->create(['business_id' => $business->id, 'type' => 'other']);

	// $hasBill = $faker->boolean;
	// $hasRefund = $faker->boolean;

	// if ($hasBill) {
	// 	$transaction = factory(Transaction\Transaction::class)->create(['business_id' => $business->id]);
	// 	factory(Transaction\PurchasedItem::class, 4)->create(['transaction_id' => $transaction->id]);
	// 	if ($hasRefund) {
	// 		factory(Refund\Refund::class)->create(['transaction_id' => $transaction->id]);
	// 	}
	// 	$transactionId = $transaction->id;
	// } else {
	// 	$transactionId = null;
	// }

	// return [
	// 	'identifier' => $faker->uuid,
	// 	'bill_identifier' => strtolower(env('BUSINESS_NAME')) . "_" . $customer->identifier,
	// 	'customer_id' => $customer->id,
	// 	'location_id' => $location->id,
	// 	'transaction_id' => $transactionId,
	// ];
});

$factory->define(Location\OnStartLocation::class, function (Faker $faker) {
	return [
		'customer_id' => function() {
			return factory(Customer\Customer::class)->create()->id;
		},
		'region_id' => function() {
			return factory(Location\Region::class)->create()->id;
		},
		'lat' => $faker->latitude,
		'lng' => $faker->longitude,
	];
});
