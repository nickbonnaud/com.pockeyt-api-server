<?php

use App\Models\Customer;
use App\Models\Business;
use App\Models\Admin;
use Illuminate\Support\Str;
use Faker\Generator as Faker;

$factory->define(Customer\Customer::class, function (Faker $faker) {
	return [
		'email' => $faker->unique()->safeEmail,
		'email_verified_at' => now(),
		'password' => $faker->password,
		'remember_token' => Str::random(10),
	];
});

$factory->define(Customer\CustomerProfile::class, function (Faker $faker) {
	return [
		'customer_id' => function() {
			return factory(Customer\Customer::class)->create()->id;
		},
		'first_name' => $faker->firstName,
		'last_name' => $faker->lastName
	];
});

$factory->define(Customer\CustomerPhoto::class, function() {
	$name = 'logo-' . time() . Str::random(5) . '.png';
	return [
		'name' => $name,
		'small_url' => 'images/photos/sm-' . $name,
		'large_url' => 'images/photos/lg-' . $name
	];
});

$factory->define(Customer\CustomerProfilePhoto::class, function() {
	return [
		'customer_profile_id' => function() {
			return factory(Customer\CustomerProfile::class)->create()->id;
		},
		'avatar_id' => function() {
			return factory(Customer\CustomerPhoto::class)->create()->id;
		}
	];
});

$factory->define(Customer\PushToken::class, function(Faker $faker) {
	return [
		'customer_id' => function() {
			return factory(Customer\Customer::class)->create()->id;
		},
		'device' => 'ios',
		'token' => 'bcbhdbchscngr82yf82bchudsy348gr8f2uycbdiuvcsvac82fgye8vy8vccvvcwvhucvdhvh'
	];
});

$factory->define(Customer\CustomerAccount::class, function(Faker $faker) {
	return [
		'customer_id' => function() {
			return factory(Customer\Customer::class)->create()->id;
		},
		'tip_rate' => 15,
		'primary' => 'ach'
	];
});

$factory->define(Customer\AchCustomer::class, function(Faker $faker) {
	return [
		'customer_account_id' => function() {
			return factory(Customer\CustomerAccount::class)->create()->id;
		},
		'customer_url' => "https://api-sandbox.dwolla.com/customers/FC451A7A-AE30-4404-AB95-E3553FCD733F",
		'funding_source_url' => 'https://api-sandbox.dwolla.com/funding-sources/375c6781-2a17-476c-84f7-db7d2f6ffb31'
	];
});

$factory->define(Customer\CardCustomer::class, function(Faker $faker) {
	return [
		'customer_account_id' => function() {
			return factory(Customer\CustomerAccount::class)->create()->id;
		},
		'recurring_detail_reference' => "7219687191761347"
	];
});

$factory->define(Customer\HelpTicket::class, function(Faker $faker) {
	return [
		'customer_id' => function() {
			return factory(Customer\Customer::class)->create()->id;
		},
		'admin_id' => function() {
			return factory(Admin\Admin::class)->create()->id;
		},
		'subject' => $faker->sentence($nbWords = 15, $variableNbWords = true),
		'message' => $faker->paragraph($nbSentences = 3, $variableNbSentences = true),
	];
});

$factory->define(Customer\HelpTicketReply::class, function(Faker $faker) {
	return [
		'help_ticket_id' => function() {
			return factory(Customer\HelpTicket::class)->create()->id;
		},
		'message' => $faker->paragraph($nbSentences = 3, $variableNbSentences = true),
		'from_customer' => true
	];
});
