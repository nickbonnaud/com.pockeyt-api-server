<?php

use App\Models\Business;
use App\Models\Location;
use Illuminate\Support\Str;
use Faker\Generator as Faker;

$factory->define(Business\Business::class, function (Faker $faker) {
	return [
		'email' => $faker->unique()->safeEmail,
		'email_verified_at' => now(),
		'password' => $faker->password,
		'remember_token' => Str::random(10),
	];
});

$factory->define(Business\Profile::class, function (Faker $faker) {
	return [
		'name' => $faker->company,
		'website' => $faker->domainName,
		'description' => $faker->paragraph($nbSentences = 3, $variableNbSentences = true),
		'business_id' => function() {
			return factory(Business\Business::class)->create()->id;
		}
	];
});

$factory->define(Business\Photo::class, function() {
	$name = 'logo-' . time() . Str::random(5) . '.png';
	return [
		'name' => $name,
		'small_url' => 'images/photos/sm-' . $name,
		'large_url' => 'images/photos/lg-' . $name
	];
});

$factory->define(Business\ProfilePhotos::class, function() {
	return [
		'profile_id' => function() {
			return factory(Business\Profile::class)->create()->id;
		},
		'logo_id' => function() {
			return factory(Business\Photo::class)->create()->id;
		},
		'banner_id' => function() {
			return factory(Business\Photo::class)->create()->id;
		}
	];
});

$factory->define(Business\AccountStatus::class, function() {
	return [
		'name' => 'incomplete',
		'code' => 0
	];
});

$factory->define(Business\Account::class, function() {
	return [
		'business_id' => function() {
			return factory(Business\Business::class)->create()->id;
		}
	];
});

$factory->define(Business\PayFacAccount::class, function() {
	return [
		'account_id' => function() {
			return factory(Business\Account::class)->create()->id;
		},
		'entity_type' => 'soleProprietorship'
	];
});

$factory->define(Business\PayFacBusiness::class, function(Faker $faker) {
	$region = factory(Location\Region::class)->create();
	return [
		'pay_fac_account_id' => function() {
			return factory(Business\PayFacAccount::class)->create()->id;
		},
		'ein' => $faker->ein,
		'business_name' => $faker->company,
		'state' => $region->state,
		'city' => $region->city,
		'zip' => $region->zip,
		'address' => $faker->streetAddress
	];
});

$factory->define(Business\PayFacOwner::class, function(Faker $faker) {
	return [
		'pay_fac_account_id' => function() {
			return factory(Business\PayFacAccount::class)->create()->id;
		},
		'state' => $faker->stateAbbr,
		'city' => $faker->city,
		'zip' => (string)($faker->randomNumber($nbDigits = 5, $strict = true)),
		'address' => $faker->streetAddress,
		'dob' => $faker->date($format = 'm/d/Y', $max = 'now'),
		'ssn' => $faker->numerify('#########'),
		'last_name' => $faker->lastName,
		'first_name' => $faker->firstName,
		'phone' => $faker->numerify('##########'),
		'email' => $faker->email,
		'primary' => false,
		'percent_ownership' => 80,
		'title' => 'CEO'
	];
});

$factory->define(Business\PayFacBank::class, function(Faker $faker) {
	return [
		'pay_fac_account_id' => function() {
			return factory(Business\PayFacAccount::class)->create()->id;
		},
		'state' => $faker->stateAbbr,
		'city' => $faker->city,
		'zip' => (string)($faker->randomNumber($nbDigits = 5, $strict = true)),
		'address' => $faker->streetAddress,
		'first_name' => $faker->firstName,
		'last_name' => $faker->firstName,
		'routing_number' => $faker->randomNumber($nbDigits = 9, $strict = true),
		'account_number' => $faker->randomNumber($nbDigits = 9, $strict = false),
		'account_type' => 'checking'
	];
});

$factory->define(Business\AchAccount::class, function() {
	return [
		'account_id' => function() {
			return factory(Business\Account::class)->create()->id;
		},
		'business_type' => 'soleProprietorship'
	];
});

$factory->define(Business\AchBusiness::class, function(Faker $faker) {
	return [
		'ach_account_id' => function() {
			return factory(Business\AchAccount::class)->create()->id;
		},
		'identifier' => $faker->uuid,
		'business_name' => $faker->company,
		'address' => $faker->streetAddress,
		'city' => $faker->city,
		'state' => $faker->stateAbbr,
		'zip' => (string)($faker->randomNumber($nbDigits = 5, $strict = true)),
		'ein' => $faker->ein
	];
});

$factory->define(Business\AchOwner::class, function(Faker $faker) {
	return [
		'ach_account_id' => function() {
			return factory(Business\AchAccount::class)->create()->id;
		},
		'identifier' => $faker->uuid,
		'first_name' => $faker->firstName,
		'last_name' => $faker->lastName,
		'title' => 'CEO',
		'email' => $faker->email,
		'dob' => $faker->date($format = 'Y-m-d', $max = 'now'),
		'address' => $faker->streetAddress,
		'city' => $faker->city,
		'state' => $faker->stateAbbr,
		'zip' => (string)($faker->randomNumber($nbDigits = 5, $strict = true)),
		'ssn' => $faker->ssn,
		'primary' => true,
	];
});

$factory->define(Business\LoyaltyProgram::class, function(Faker $faker) {
	return [
		'business_id' => function() {
			return factory(Business\Business::class)->create()->id;
		},
		'purchases_required' => 20,
		'amount_required' => 10000,
		'reward' => 'Large Ice Cream'
	];
});

$factory->define(Business\Location::class, function(Faker $faker) {
	return [
		'business_id' => function() {
			return factory(Business\Business::class)->create()->id;
		},
		'region_id' => function() {
			return factory(Location\Region::class)->create()->id;
		}
	];
});

$factory->define(Business\GeoAccount::class, function(Faker $faker) {
	return [
		'identifier' => $faker->uuid,
		'location_id' => function() {
			return factory(Business\Location::class)->create()->id;
		},
		'lat' => $faker->latitude,
		'lng' => $faker->longitude,
		'radius' => 50
	];
});

$factory->define(Business\BeaconAccount::class, function(Faker $faker) {
	return [
		'location_id' => function() {
			return factory(Business\Location::class)->create()->id;
		},
		'identifier' => $faker->uuid,
		'major' => $faker->word,
		'minor' => $faker->word
	];
});

$factory->define(Business\Inventory::class, function(Faker $faker) {
	return [
		'business_id' => function() {
			return factory(Business\Business::class)->create()->id;
		}
	];
});

$factory->define(Business\ActiveItem::class, function(Faker $faker) {
	return [
		'inventory_id' => function() {
			return factory(Business\Inventory::class)->create()->id;
		},
		'main_id' => Str::random(20),
		'name' => $faker->word,
		'category' => $faker->word,
		'price' => 2000
	];
});

$factory->define(Business\InactiveItem::class, function(Faker $faker) {
	return [
		'active_id' => $faker->numerify('#######'),
		'inventory_id' => function() {
			return factory(Business\Inventory::class)->create()->id;
		},
		'main_id' => Str::random(20),
		'name' => $faker->word,
		'category' => $faker->word,
		'price' => 2000
	];
});

$factory->define(Business\PosAccountStatus::class, function() {
	return [
		'name' => 'Connection Pending',
		'code' => 100
	];
});

$factory->define(Business\PosAccount::class, function() {
	return [
		'pos_account_status_id' => function() {
			return factory(Business\PosAccountStatus::class)->create()->id;
		},
		'business_id' => function() {
			return factory(Business\Business::class)->create()->id;
		},
		'type' => 'square',
		'takes_tips' => true,
		'allows_open_tickets' => false
	];
});

$factory->define(Business\SquareAccount::class, function(Faker $faker) {
	return [
		'pos_account_id' => function() {
			return factory(Business\PosAccount::class)->create()->id;
		},
		'access_token' => 'not_token',
		'merchant_id' => $faker->word,
		'refresh_token' => $faker->word,
		'expiry' => $faker->date($format = 'Y-m-d', $max = 'now'),
	];
});

$factory->define(Business\CloverAccount::class, function(Faker $faker) {
	return [
		'pos_account_id' => function() {
			return factory(Business\PosAccount::class)->create()->id;
		},
		'access_token' => 'not_token',
		'merchant_id' => $faker->word,
		'tender_id' => 'BPQN5844528BA'
	];
});

$factory->define(Business\LightspeedRetailAccount::class, function(Faker $faker) {
	return [
		'pos_account_id' => function() {
			return factory(Business\PosAccount::class)->create()->id;
		},
		'access_token' => 'not_token',
		'account_id' => $faker->word,
		'refresh_token' => $faker->word,
		'expiry' => time() + 3600
	];
});

$factory->define(Business\ShopifyAccount::class, function(Faker $faker) {
	return [
		'pos_account_id' => function() {
			return factory(Business\PosAccount::class)->create(['type' => 'shopify'])->id;
		},
		'access_token' => 'not_token',
		'shop_id' => 'some-shop.myshopify.com'
	];
});

$factory->define(Business\VendAccount::class, function(Faker $faker) {
	return [
		'pos_account_id' => function() {
			return factory(Business\PosAccount::class)->create()->id;
		},
		'access_token' => 'not_token',
		'domain_prefix' => $faker->word,
		'refresh_token' => $faker->word,
		'expiry' => time() + 86400
	];
});

$factory->define(Business\Employee::class, function(Faker $faker) {
	return [
		'business_id' => function() {
			return factory(Business\Business::class)->create()->id;
		},
		'external_id' => $faker->uuid,
		'first_name' => $faker->firstName,
		'last_name' => $faker->lastName,
	];
});

$factory->define(Business\BusinessMessage::class, function(Faker $faker) {
	return [
		'business_id' => function() {
			return factory(Business\Business::class)->create()->id;
		},
		'title' => $faker->sentence($nbWords = 15, $variableNbWords = true),
		'body' => $faker->paragraph($nbSentences = 3, $variableNbSentences = true),
		'sent_by_business' => false,
		'read' => false
	];
});

$factory->define(Business\BusinessMessageReply::class, function(Faker $faker) {
	return [
		'business_message_id' => function() {
			return factory(Business\BusinessMessage::class)->create()->id;
		},
		'body' => $faker->sentence($nbWords = 15, $variableNbWords = true),
		'sent_by_business' => false,
		'read' => false
	];
});




