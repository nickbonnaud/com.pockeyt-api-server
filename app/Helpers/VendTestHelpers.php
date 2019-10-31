<?php

namespace App\Helpers;

use \Faker as Faker;

class VendTestHelpers {

	public static function fakeAccessTokenResponse() {
		return json_encode([
			"access_token" => "not_token",
	    "token_type" => "Bearer",
	    "expires" => time() + 86400,
	    "expires_in" => 86400,
	    "refresh_token" => "J3F62YPIQdfJjJia1xJuaHp7NoQYtm9y0WadNBTh"
		]);
	}

	public static function fakeCreateWebhook() {
		return json_encode([
			"id" => "0af7b240-ab83-11e7-eddc-7311972c4cab",
		  "retailer_id" => "b1c50056-f019-11e3-a0f5-b8ca3a64f8f4",
		  "user_id" => "bc305bf6-6183-11e4-f15a-190c6647efc2",
		  "url" => "https://12345678.ngrok.io",
		  "active" => true,
		  "type" => "sale.update"
		]);
	}

	public static function fakeCreateCustomer() {
		$faker = Faker\Factory::create();
		return json_encode([
			"data" => [
		    "id" => "0af7b240-ab83-11e7-eddc-4023c64c85e5",
		    "name" => "Tony Stark",
		    "first_name" => "Tony",
		    "last_name" => "Stark",
		    "email" => "tony@starkinc.com",
		    "note" => env('BUSINESS_NAME') . " Customer",
		    "custom_field_1" => strtolower(env('BUSINESS_NAME')) . "_" . $faker->uuid,
		    "created_at" => "2017-05-24T01:53:25+00:00",
		    "updated_at" => "2017-05-24T01:53:25+00:00",
		    "version" => 3505346597
		  ]
		]);
	}

	public static function fakeEmployeeFetch($employeeId) {
		$faker = Faker\Factory::create();
		return json_encode([
			"data" => [
		    "id" => $employeeId,
		    "username" => $faker->email,
		    "display_name" => $faker->boolean ? $faker->firstName . ' ' . $faker->lastName : $faker->lastName,
		    "email" => $faker->email,
		    "email_verified_at" => null,
		    "restricted_outlet_id" => null,
		    "restricted_outlet_ids" => [],
		    "account_type" => "admin",
		    "created_at" => "2014-06-09T21:04:50+00:00",
		    "updated_at" => "2017-05-01T20:36:40+00:00",
		    "deleted_at" => null,
		    "seen_at" => "2017-05-22T03:30:27+00:00",
		    "target_daily" => 1000,
		    "target_weekly" => 10000,
		    "target_monthly" => 50000,
		    "version" => 3348795492,
		    "permissions" => null,
		    "is_primary_user" => true,
		    "image_source" => "https://vendimageuploadcdn.global.ssl.fastly.net/50x50,q90/vend-images/user/original/1/b/1ba1099aecfbbcbdfc4aa8efb136507f99a3c0d9.jpg",
		    "images" => [
		      "ss" => "https://vendimageuploadcdn.global.ssl.fastly.net/50x50,q90/vend-images/user/original/1/b/1ba1099aecfbbcbdfc4aa8efb136507f99a3c0d9.jpg",
		      "standard" => "https://vendimageuploadcdn.global.ssl.fastly.net/350,fit,q90/vend-images/user/original/1/b/1ba1099aecfbbcbdfc4aa8efb136507f99a3c0d9.jpg",
		      "st" => "https://vendimageuploadcdn.global.ssl.fastly.net/40x40,q90/vend-images/user/original/1/b/1ba1099aecfbbcbdfc4aa8efb136507f99a3c0d9.jpg",
		      "original" => "https://vendimageuploadcdn.global.ssl.fastly.net/1920,fit/vend-images/user/original/1/b/1ba1099aecfbbcbdfc4aa8efb136507f99a3c0d9.jpg",
		      "thumb" => "https://vendimageuploadcdn.global.ssl.fastly.net/160,fit,q90/vend-images/user/original/1/b/1ba1099aecfbbcbdfc4aa8efb136507f99a3c0d9.jpg",
		      "sl" => "https://vendimageuploadcdn.global.ssl.fastly.net/150x150,q90/vend-images/user/original/1/b/1ba1099aecfbbcbdfc4aa8efb136507f99a3c0d9.jpg",
		      "sm" => "https://vendimageuploadcdn.global.ssl.fastly.net/100x100,q90/vend-images/user/original/1/b/1ba1099aecfbbcbdfc4aa8efb136507f99a3c0d9.jpg"
		    ]
		  ]
		]);
	}

	public static function fakeReceiveWebhookNoCustomer() {
		return [
			'domain_prefix' => 'pockeyttest',
			'environment' => 'prod',
			'payload' => '{"created_at":"2019-07-24 21:46:25","customer":{"balance":"0.00000","company_name":null,"contact_first_name":null,"contact_last_name":null,"created_at":"2019-07-23 19:15:30","custom_field_1":null,"custom_field_2":null,"custom_field_3":null,"custom_field_4":null,"customer_code":"WALKIN","customer_group_id":"0af7b240-abc5-11e9-fb5b-ad7e3c8823e8","date_of_birth":null,"deleted_at":null,"do_not_email":false,"email":null,"enable_loyalty":false,"fax":null,"first_name":null,"id":"0af7b240-abc5-11e9-fb5b-ad7e3c885144","last_name":null,"loyalty_balance":"0.00000","mobile":null,"note":null,"phone":null,"points":0,"sex":null,"updated_at":"2019-07-23 19:15:30","year_to_date":"0.00000"},"customer_id":"0af7b240-abc5-11e9-fb5b-ad7e3c885144","deleted_at":null,"id":"5687606e-dbc9-b0a6-11e9-ae5bfe3709e3","invoice_number":"6","note":"","outlet_id":"0af7b240-abc5-11e9-fb5b-ad7e3c8de6bb","register_id":"0af7b240-abc5-11e9-fb5b-ad7e3c8e53f5","register_sale_payments":[{"amount":503.96,"id":"5687606e-dbc9-b219-11e9-ae5c77d31579","payment_date":"2019-07-24T21:46:18Z","payment_type":{"has_native_support":false,"id":"3","name":"Credit Card"},"payment_type_id":3,"retailer_payment_type":{"config":null,"id":"b852168e-794b-4058-8dfc-497caf7807bb","name":"Heist Pay","payment_type_id":"3"},"retailer_payment_type_id":"b852168e-794b-4058-8dfc-497caf7807bb"}],"register_sale_products":[{"discount":"0.00000","id":"5687606e-dbc9-b0a6-11e9-ae5c1b028f45","is_return":false,"loyalty_value":"0.00000","note":null,"price":"269.90000","price_set":false,"price_total":"269.90000","product_id":"0af7b240-abf0-11e9-fb5b-ad7e3cb470d8","quantity":1,"tax":"20.24250","tax_id":"0af7b240-abf0-11e9-fb5b-ae206234d230","tax_total":"20.24250"},{"discount":"0.00000","id":"5687606e-dbc9-b0a6-11e9-ae5c1ca51bb8","is_return":false,"loyalty_value":"0.00000","note":null,"price":"198.90000","price_set":false,"price_total":"198.90000","product_id":"0af7b240-abf0-11e9-fb5b-ad7e3d8109eb","quantity":1,"tax":"14.91750","tax_id":"0af7b240-abf0-11e9-fb5b-ae206234d230","tax_total":"14.91750"}],"return_for":null,"sale_date":"2019-07-24T21:46:18Z","short_code":"v86d2w","source":"USER","source_id":null,"status":"CLOSED","taxes":[{"id":"6234ab34-ae20-11e9-9b5b-0af7b240abc5","name":"Sales Tax","rate":"0.07500","tax":35.16}],"totals":{"total_loyalty":"0.00000","total_payment":"503.96000","total_price":"468.80000","total_tax":"35.16000","total_to_pay":"0.00000"},"updated_at":"2019-07-24T21:46:25+00:00","user":{"created_at":"2019-07-23 19:15:30","display_name":"Nick Bonnaud","email":"nick.bonnaud@gmail.com","id":"0af7b240-abc5-11e9-fb5b-ad7e3c8ea635","name":"nick.bonnaud@gmail.com","target_daily":null,"target_monthly":null,"target_weekly":null,"updated_at":"2019-07-23 19:15:30"},"user_id":"0af7b240-abc5-11e9-fb5b-ad7e3c8ea635","version":11423830534}',
  		'retailer_id' => '0af7b240-abc5-11e9-fb5b-ad7e3c86513b',
  		'type' => 'sale.update',
		];
	}

	public static function fakeReceiveWebhookCustomer($identifier) {
		return [
			'domain_prefix' => 'pockeyttest',
		  'environment' => 'prod',
		  'payload' => '{"created_at":"2019-07-26 17:31:40","customer":{"balance":"0.01000","company_name":null,"contact_first_name":"John","contact_last_name":"Smith","created_at":"2019-07-25 20:14:07","custom_field_1":"' . $identifier . '","custom_field_2":null,"custom_field_3":null,"custom_field_4":null,"customer_code":"John-L99C","customer_group_id":"0af7b240-abc5-11e9-fb5b-ad7e3c8823e8","date_of_birth":null,"deleted_at":null,"do_not_email":false,"email":"john@pockeyt.com","enable_loyalty":true,"fax":null,"first_name":"John","id":"0af7b240-abf0-11e9-fb5b-af18c1b19c1a","last_name":"Smith","loyalty_balance":"0.00000","mobile":null,"note":" Customer","phone":null,"points":0,"sex":null,"updated_at":"2019-07-26 17:31:41","year_to_date":"1093.48999"},"customer_id":"0af7b240-abf0-11e9-fb5b-af18c1b19c1a","deleted_at":null,"id":"5687606e-dbc9-8897-11e9-af1dac63fb78","invoice_number":"11","note":"","outlet_id":"0af7b240-abc5-11e9-fb5b-ad7e3c8de6bb","register_id":"0af7b240-abc5-11e9-fb5b-ad7e3c8e53f5","register_sale_payments":[{"amount":943.21,"id":"5687606e-dbc9-8897-11e9-afcb30c283e2","payment_date":"2019-07-26T17:31:24Z","payment_type":{"has_native_support":false,"id":"3","name":"Credit Card"},"payment_type_id":3,"retailer_payment_type":{"config":null,"id":"b852168e-794b-4058-8dfc-497caf7807bb","name":"Heist Pay","payment_type_id":"3"},"retailer_payment_type_id":"b852168e-794b-4058-8dfc-497caf7807bb"}],"register_sale_products":[{"discount":"0.00000","id":"5687606e-dbc9-8897-11e9-afcb0e27b83d","is_return":false,"loyalty_value":"0.00000","note":null,"price":"69.90000","price_set":false,"price_total":"209.70000","product_id":"0af7b240-abf0-11e9-fb5b-ad7e3d5bd0b7","quantity":3,"tax":"5.24250","tax_id":"0af7b240-abf0-11e9-fb5b-ae206234d230","tax_total":"15.72750"},{"discount":"0.00000","id":"5687606e-dbc9-8897-11e9-afcb1759d881","is_return":false,"loyalty_value":"0.00000","note":null,"price":"198.90000","price_set":false,"price_total":"397.80000","product_id":"0af7b240-abf0-11e9-fb5b-ad7e3d8f16e1","quantity":2,"tax":"14.91750","tax_id":"0af7b240-abf0-11e9-fb5b-ae206234d230","tax_total":"29.83500"},{"discount":"0.00000","id":"5687606e-dbc9-8897-11e9-afcb19d2c7c5","is_return":false,"loyalty_value":"0.00000","note":null,"price":"269.90000","price_set":false,"price_total":"269.90000","product_id":"0af7b240-abf0-11e9-fb5b-ad7e3cb470d8","quantity":1,"tax":"20.24250","tax_id":"0af7b240-abf0-11e9-fb5b-ae206234d230","tax_total":"20.24250"}],"return_for":null,"sale_date":"2019-07-26T17:31:24Z","short_code":"8n3qhj","source":"USER","source_id":null,"status":"CLOSED","taxes":[{"id":"6234ab34-ae20-11e9-9b5b-0af7b240abc5","name":"Sales Tax","rate":"0.07500","tax":65.805}],"totals":{"total_loyalty":"0.00000","total_payment":"943.21000","total_price":"877.40000","total_tax":"65.80500","total_to_pay":"-0.00500"},"updated_at":"2019-07-26T17:31:40+00:00","user":{"created_at":"2019-07-23 19:15:30","display_name":"Nick Bonnaud","email":"nick.bonnaud@gmail.com","id":"0af7b240-abc5-11e9-fb5b-ad7e3c8ea635","name":"nick.bonnaud@gmail.com","target_daily":null,"target_monthly":null,"target_weekly":null,"updated_at":"2019-07-23 19:15:30"},"user_id":"0af7b240-abc5-11e9-fb5b-ad7e3c8ea635","version":11444460697}',
		  'retailer_id' => '0af7b240-abc5-11e9-fb5b-ad7e3c86513b',
		  'type' => 'sale.update',
		];
	}

	public static function fakeFetchProductData($productId) {
		$faker = Faker\Factory::create();
		return json_encode([
			"data" => [
		    "id" => $productId,
		    "source_id" => null,
		    "source_variant_id" => null,
		    "variant_parent_id" => null,
		    "name" => $faker->name,
		    "variant_name" => $faker->name,
		    "handle" => "bravo",
		    "sku" => "787878",
		    "supplier_code" => "123456",
		    "active" => true,
		    "has_inventory" => true,
		    "is_composite" => false,
		    "description" => "<p>The best product ever, except for the next one.</p>",
		    "image_url" => "https://vendimageuploadcdn.global.ssl.fastly.net/350,fit,q90/vend-images/product/original/f/0/f0ebadbc30947444d438678a4a7bd6d996f392fc.jpg",
		    "created_at" => "2014-12-01T18:34:15+00:00",
		    "updated_at" => "2017-08-21T23:50:39+00:00",
		    "deleted_at" => null,
		    "source" => "USER",
		    "account_code" => null,
		    "account_code_purchase" => null,
		    "supply_price" => 100,
		    "version" => 4284638488,
		    "type" => [
		      "id" => "b1e2babe-f019-11e3-a0f5-b8ca3a64f8f4",
		      "name" => "General",
		      "deleted_at" => null,
		      "version" => 449902
		    ],
		    "supplier" => [
		      "id" => "dc85058a-a683-11e5-ef46-08f9a926615e",
		      "name" => "Peak",
		      "source" => "",
		      "description" => "",
		      "deleted_at" => null,
		      "version" => 2089502
		    ],
		    "brand" => [
		      "id" => "0adaafb3-6583-11e5-fb60-d5b67a17df2f",
		      "name" => "Peak Performance",
		      "deleted_at" => null,
		      "version" => 882391722
		    ],
		    "variant_options" => [
		      [
		        "id" => "9a972fbe-3b53-102e-b60f-4abbcbc88955",
		        "name" => "Color",
		        "value" => "Black"
		      ],
		      [
		        "id" => "6b8f11e2-5188-11e0-9d7a-4040f540b50a",
		        "name" => "Fabric",
		        "value" => "Cotton"
		      ],
		      [
		        "id" => "6b8f11e2-5188-11e0-9d7a-4040f540b50a",
		        "name" => "Fabric",
		        "value" => "Cotton"
		      ]
		    ],
		    "categories" => [
		      [
		        "id" => "b8ca3a65-0183-11e4-fbb5-a7e973b74d92",
		        "name" => "tag_test",
		        "deleted_at" => null,
		        "version" => 1446856
		      ],
		      [
		        "id" => "bc305bf6-6183-11e4-f15a-16ced23b8465",
		        "name" => "variant",
		        "deleted_at" => null,
		        "version" => 961180
		      ]
		    ],
		    "images" => [
		      [
		        "id" => "06bf537b-c783-11e6-f6b9-b73917d393b6",
		        "url" => "https://vendimageuploadcdn.global.ssl.fastly.net/1920,fit/vend-images/product/original/f/0/f0ebadbc30947444d438678a4a7bd6d996f392fc.jpg",
		        "version" => 2398861114,
		        "sizes" => [
		          "ss" => "https://vendimageuploadcdn.global.ssl.fastly.net/50x50,q90/vend-images/product/original/f/0/f0ebadbc30947444d438678a4a7bd6d996f392fc.jpg",
		          "standard" => "https://vendimageuploadcdn.global.ssl.fastly.net/350,fit,q90/vend-images/product/original/f/0/f0ebadbc30947444d438678a4a7bd6d996f392fc.jpg",
		          "st" => "https://vendimageuploadcdn.global.ssl.fastly.net/40x40,q90/vend-images/product/original/f/0/f0ebadbc30947444d438678a4a7bd6d996f392fc.jpg",
		          "original" => "https://vendimageuploadcdn.global.ssl.fastly.net/1920,fit/vend-images/product/original/f/0/f0ebadbc30947444d438678a4a7bd6d996f392fc.jpg",
		          "thumb" => "https://vendimageuploadcdn.global.ssl.fastly.net/160,fit,q90/vend-images/product/original/f/0/f0ebadbc30947444d438678a4a7bd6d996f392fc.jpg",
		          "sl" => "https://vendimageuploadcdn.global.ssl.fastly.net/150x150,q90/vend-images/product/original/f/0/f0ebadbc30947444d438678a4a7bd6d996f392fc.jpg",
		          "sm" => "https://vendimageuploadcdn.global.ssl.fastly.net/100x100,q90/vend-images/product/original/f/0/f0ebadbc30947444d438678a4a7bd6d996f392fc.jpg"
		        ]
		      ]
		    ],
		    "has_variants" => true,
		    "button_order" => 2,
		    "price_including_tax" => 126.5,
		    "price_excluding_tax" => 110,
		    "loyalty_amount" => null,
		    "attributes" => [],
		    "supplier_id" => "dc85058a-a683-11e5-ef46-08f9a926615e",
		    "product_type_id" => "b1e2babe-f019-11e3-a0f5-b8ca3a64f8f4",
		    "brand_id" => "0adaafb3-6583-11e5-fb60-d5b67a17df2f",
		    "is_active" => true,
		    "image_thumbnail_url" => "https://vendimageuploadcdn.global.ssl.fastly.net/160,fit,q90/vend-images/product/original/f/0/f0ebadbc30947444d438678a4a7bd6d996f392fc.jpg",
		    "tag_ids" => [
		      "b8ca3a65-0183-11e4-fbb5-a7e973b74d92",
		      "bc305bf6-6183-11e4-f15a-16ced23b8465"
		    ]
		  ]
		]);
	}

	public static function fakeFullReturnWebhookInitial($identifier) {
		return [
			'domain_prefix' => 'pockeyttest',
		  'environment' => 'prod',
		  'payload' => '{"created_at":"2019-07-26 17:52:23","customer":{"balance":"0.00000","company_name":null,"contact_first_name":"John","contact_last_name":"Smith","created_at":"2019-07-25 20:14:07","custom_field_1":"' . $identifier . '","custom_field_2":null,"custom_field_3":null,"custom_field_4":null,"customer_code":"John-L99C","customer_group_id":"0af7b240-abc5-11e9-fb5b-ad7e3c8823e8","date_of_birth":null,"deleted_at":null,"do_not_email":false,"email":"john@pockeyt.com","enable_loyalty":true,"fax":null,"first_name":"John","id":"0af7b240-abf0-11e9-fb5b-af18c1b19c1a","last_name":"Smith","loyalty_balance":"0.00000","mobile":null,"note":" Customer","phone":null,"points":0,"sex":null,"updated_at":"2019-07-26 17:53:15","year_to_date":"943.21002"},"customer_id":"0af7b240-abf0-11e9-fb5b-af18c1b19c1a","deleted_at":null,"id":"5687606e-dbc9-8897-11e9-af192251e676","invoice_number":"12","note":"","outlet_id":"0af7b240-abc5-11e9-fb5b-ad7e3c8de6bb","register_id":"0af7b240-abc5-11e9-fb5b-ad7e3c8e53f5","register_sale_payments":[{"amount":150.29,"id":"5687606e-dbc9-9aaf-11e9-afce369fb68e","payment_date":"2019-07-26T17:53:02Z","payment_type":{"has_native_support":false,"id":"3","name":"Credit Card"},"payment_type_id":3,"retailer_payment_type":{"config":null,"id":"b852168e-794b-4058-8dfc-497caf7807bb","name":"Heist Pay","payment_type_id":"3"},"retailer_payment_type_id":"b852168e-794b-4058-8dfc-497caf7807bb"}],"register_sale_products":[{"discount":"0.00000","id":"0af7b240-abf0-11e9-fb5b-afce1f8bb1ee","is_return":false,"loyalty_value":"0.00000","note":null,"price":"69.90000","price_set":true,"price_total":"69.90000","product_id":"0af7b240-abf0-11e9-fb5b-ad7e3d5bd0b7","quantity":1,"tax":"5.24250","tax_id":"0af7b240-abf0-11e9-fb5b-ae206234d230","tax_total":"5.24250"},{"discount":"0.00000","id":"0af7b240-abf0-11e9-fb5b-afce1f8bfa7e","is_return":false,"loyalty_value":"0.00000","note":null,"price":"69.90000","price_set":true,"price_total":"69.90000","product_id":"0af7b240-abf0-11e9-fb5b-ad7e3d5bd0b7","quantity":1,"tax":"5.24250","tax_id":"0af7b240-abf0-11e9-fb5b-ae206234d230","tax_total":"5.24250"}],"return_for":null,"sale_date":"2019-07-26T17:53:02Z","short_code":"8cp759","source":"USER","source_id":null,"status":"CLOSED","taxes":[{"id":"6234ab34-ae20-11e9-9b5b-0af7b240abc5","name":"Sales Tax","rate":"0.07500","tax":10.485}],"totals":{"total_loyalty":"0.00000","total_payment":"150.29000","total_price":"139.80000","total_tax":"10.48500","total_to_pay":"0.00500"},"updated_at":"2019-07-26T17:53:14+00:00","user":{"created_at":"2019-07-23 19:15:30","display_name":"Nick Bonnaud","email":"nick.bonnaud@gmail.com","id":"0af7b240-abc5-11e9-fb5b-ad7e3c8ea635","name":"nick.bonnaud@gmail.com","target_daily":null,"target_monthly":null,"target_weekly":null,"updated_at":"2019-07-23 19:15:30"},"user_id":"0af7b240-abc5-11e9-fb5b-ad7e3c8ea635","version":11444647896}',
		  'retailer_id' => '0af7b240-abc5-11e9-fb5b-ad7e3c86513b',
		  'type' => 'sale.update',
		];
	}

	public static function fakeFullReturnWebhook($identifier) {
		return [
			'domain_prefix' => 'pockeyttest',
		  'environment' => 'prod',
		  'payload' => '{"created_at":"2019-07-26 17:52:23","customer":{"balance":"0.00000","company_name":null,"contact_first_name":"John","contact_last_name":"Smith","created_at":"2019-07-25 20:14:07","custom_field_1":"' . $identifier . '","custom_field_2":null,"custom_field_3":null,"custom_field_4":null,"customer_code":"John-L99C","customer_group_id":"0af7b240-abc5-11e9-fb5b-ad7e3c8823e8","date_of_birth":null,"deleted_at":null,"do_not_email":false,"email":"john@pockeyt.com","enable_loyalty":true,"fax":null,"first_name":"John","id":"0af7b240-abf0-11e9-fb5b-af18c1b19c1a","last_name":"Smith","loyalty_balance":"0.00000","mobile":null,"note":" Customer","phone":null,"points":0,"sex":null,"updated_at":"2019-07-26 17:53:15","year_to_date":"943.21002"},"customer_id":"0af7b240-abf0-11e9-fb5b-af18c1b19c1a","deleted_at":null,"id":"0af7b240-abf0-11e9-fb5b-afce1f8d34ce","invoice_number":"12","note":"","outlet_id":"0af7b240-abc5-11e9-fb5b-ad7e3c8de6bb","register_id":"0af7b240-abc5-11e9-fb5b-ad7e3c8e53f5","register_sale_payments":[{"amount":-150.29,"id":"5687606e-dbc9-9aaf-11e9-afce369fb68e","payment_date":"2019-07-26T17:53:02Z","payment_type":{"has_native_support":false,"id":"3","name":"Credit Card"},"payment_type_id":3,"retailer_payment_type":{"config":null,"id":"5687606e-dbc9-8897-11e9-af192251e676","name":"Heist Pay","payment_type_id":"3"},"retailer_payment_type_id":"b852168e-794b-4058-8dfc-497caf7807bb"}],"register_sale_products":[{"discount":"0.00000","id":"0af7b240-abf0-11e9-fb5b-afce1f8bb1ee","is_return":true,"loyalty_value":"0.00000","note":null,"price":"69.90000","price_set":true,"price_total":"-69.90000","product_id":"0af7b240-abf0-11e9-fb5b-ad7e3d5bd0b7","quantity":-1,"tax":"5.24250","tax_id":"0af7b240-abf0-11e9-fb5b-ae206234d230","tax_total":"-5.24250"},{"discount":"0.00000","id":"0af7b240-abf0-11e9-fb5b-afce1f8bfa7e","is_return":true,"loyalty_value":"0.00000","note":null,"price":"69.90000","price_set":true,"price_total":"-69.90000","product_id":"0af7b240-abf0-11e9-fb5b-ad7e3d5bd0b7","quantity":-1,"tax":"5.24250","tax_id":"0af7b240-abf0-11e9-fb5b-ae206234d230","tax_total":"-5.24250"}],"return_for":"5687606e-dbc9-8897-11e9-af192251e676","sale_date":"2019-07-26T17:53:02Z","short_code":"8cp759","source":"USER","source_id":null,"status":"CLOSED","taxes":[{"id":"6234ab34-ae20-11e9-9b5b-0af7b240abc5","name":"Sales Tax","rate":"0.07500","tax":-10.485}],"totals":{"total_loyalty":"0.00000","total_payment":"-150.29000","total_price":"-139.80000","total_tax":"-10.48500","total_to_pay":"0.00500"},"updated_at":"2019-07-26T17:53:14+00:00","user":{"created_at":"2019-07-23 19:15:30","display_name":"Nick Bonnaud","email":"nick.bonnaud@gmail.com","id":"0af7b240-abc5-11e9-fb5b-ad7e3c8ea635","name":"nick.bonnaud@gmail.com","target_daily":null,"target_monthly":null,"target_weekly":null,"updated_at":"2019-07-23 19:15:30"},"user_id":"0af7b240-abc5-11e9-fb5b-ad7e3c8ea635","version":11444647896}',
		  'retailer_id' => '0af7b240-abc5-11e9-fb5b-ad7e3c86513b',
		  'type' => 'sale.update',
		];
	}

	public static function fakePartialReturnWebhook($identifier) {
		return [
			'domain_prefix' => 'pockeyttest',
		  'environment' => 'prod',
		  'payload' => '{"created_at":"2019-07-26 19:30:12","customer":{"balance":"0.00000","company_name":null,"contact_first_name":"John","contact_last_name":"Smith","created_at":"2019-07-25 20:14:07","custom_field_1":"' . $identifier . '","custom_field_2":null,"custom_field_3":null,"custom_field_4":null,"customer_code":"John-L99C","customer_group_id":"0af7b240-abc5-11e9-fb5b-ad7e3c8823e8","date_of_birth":null,"deleted_at":null,"do_not_email":false,"email":"john@pockeyt.com","enable_loyalty":true,"fax":null,"first_name":"John","id":"0af7b240-abf0-11e9-fb5b-af18c1b19c1a","last_name":"Smith","loyalty_balance":"0.00000","mobile":null,"note":" Customer","phone":null,"points":0,"sex":null,"updated_at":"2019-07-26 19:31:51","year_to_date":"654.25000"},"customer_id":"0af7b240-abf0-11e9-fb5b-af18c1b19c1a","deleted_at":null,"id":"0af7b240-abf0-11e9-fb5b-afdbc97ad87b","invoice_number":"13","note":"","outlet_id":"0af7b240-abc5-11e9-fb5b-ad7e3c8de6bb","register_id":"0af7b240-abc5-11e9-fb5b-ad7e3c8e53f5","register_sale_payments":[{"amount":-288.96,"id":"5687606e-dbc9-91e8-11e9-afdbfb9eaeb7","payment_date":"2019-07-26T19:31:36Z","payment_type":{"has_native_support":false,"id":"3","name":"Credit Card"},"payment_type_id":3,"retailer_payment_type":{"config":null,"id":"b852168e-794b-4058-8dfc-497caf7807bb","name":"Heist Pay","payment_type_id":"3"},"retailer_payment_type_id":"b852168e-794b-4058-8dfc-497caf7807bb"}],"register_sale_products":[{"discount":"0.00000","id":"0af7b240-abf0-11e9-fb5b-afdbc97843fc","is_return":true,"loyalty_value":"0.00000","note":null,"price":"269.90000","price_set":true,"price_total":"0.00000","product_id":"0af7b240-abf0-11e9-fb5b-ad7e3cb470d8","quantity":0,"tax":"20.24250","tax_id":"0af7b240-abf0-11e9-fb5b-ae206234d230","tax_total":"0.00000"},{"discount":"0.00000","id":"0af7b240-abf0-11e9-fb5b-afdbc978b71e","is_return":true,"loyalty_value":"0.00000","note":null,"price":"198.90000","price_set":true,"price_total":"-198.90000","product_id":"0af7b240-abf0-11e9-fb5b-ad7e3d8f16e1","quantity":-1,"tax":"14.91750","tax_id":"0af7b240-abf0-11e9-fb5b-ae206234d230","tax_total":"-14.91750"},{"discount":"0.00000","id":"0af7b240-abf0-11e9-fb5b-afdbc9791d36","is_return":true,"loyalty_value":"0.00000","note":null,"price":"69.90000","price_set":true,"price_total":"-69.90000","product_id":"0af7b240-abf0-11e9-fb5b-ad7e3d5bd0b7","quantity":-1,"tax":"5.24250","tax_id":"0af7b240-abf0-11e9-fb5b-ae206234d230","tax_total":"-5.24250"}],"return_for":"5687606e-dbc9-8897-11e9-af1dac63fb78","sale_date":"2019-07-26T19:31:36Z","short_code":"q5o9br","source":"USER","source_id":null,"status":"CLOSED","taxes":[{"id":"6234ab34-ae20-11e9-9b5b-0af7b240abc5","name":"Sales Tax","rate":"0.07500","tax":-20.16}],"totals":{"total_loyalty":"0.00000","total_payment":"-288.96000","total_price":"-268.80000","total_tax":"-20.16000","total_to_pay":"0.00000"},"updated_at":"2019-07-26T19:31:51+00:00","user":{"created_at":"2019-07-23 19:15:30","display_name":"Nick Bonnaud","email":"nick.bonnaud@gmail.com","id":"0af7b240-abc5-11e9-fb5b-ad7e3c8ea635","name":"nick.bonnaud@gmail.com","target_daily":null,"target_monthly":null,"target_weekly":null,"updated_at":"2019-07-23 19:15:30"},"user_id":"0af7b240-abc5-11e9-fb5b-ad7e3c8ea635","version":11445516572}',
		  'retailer_id' => '0af7b240-abc5-11e9-fb5b-ad7e3c86513b',
		  'type' => 'sale.update',
		];
	}


	public static function fakeDoubleReturnFirst($identifier) {
		return [
			'domain_prefix' => 'pockeyttest',
		  'environment' => 'prod',
		  'payload' => '{"created_at":"2019-07-26 19:30:12","customer":{"balance":"0.00000","company_name":null,"contact_first_name":"John","contact_last_name":"Smith","created_at":"2019-07-25 20:14:07","custom_field_1":"' . $identifier . '","custom_field_2":null,"custom_field_3":null,"custom_field_4":null,"customer_code":"John-L99C","customer_group_id":"0af7b240-abc5-11e9-fb5b-ad7e3c8823e8","date_of_birth":null,"deleted_at":null,"do_not_email":false,"email":"john@pockeyt.com","enable_loyalty":true,"fax":null,"first_name":"John","id":"0af7b240-abf0-11e9-fb5b-af18c1b19c1a","last_name":"Smith","loyalty_balance":"0.00000","mobile":null,"note":" Customer","phone":null,"points":0,"sex":null,"updated_at":"2019-07-26 19:30:12","year_to_date":"943.21002"},"customer_id":"0af7b240-abf0-11e9-fb5b-af18c1b19c1a","deleted_at":null,"id":"0af7b240-abf0-11e9-fb5b-afdbc97ad87b","invoice_number":"13","note":"","outlet_id":"0af7b240-abc5-11e9-fb5b-ad7e3c8de6bb","register_id":"0af7b240-abc5-11e9-fb5b-ad7e3c8e53f5","register_sale_payments":[],"register_sale_products":[{"discount":"0.00000","id":"0af7b240-abf0-11e9-fb5b-afdbc97843fc","is_return":true,"loyalty_value":"0.00000","note":null,"price":"269.90000","price_set":true,"price_total":"-269.90000","product_id":"0af7b240-abf0-11e9-fb5b-ad7e3cb470d8","quantity":-1,"tax":"20.24250","tax_id":"0af7b240-abf0-11e9-fb5b-ae206234d230","tax_total":"-20.24250"},{"discount":"0.00000","id":"0af7b240-abf0-11e9-fb5b-afdbc978b71e","is_return":true,"loyalty_value":"0.00000","note":null,"price":"198.90000","price_set":true,"price_total":"-397.80000","product_id":"0af7b240-abf0-11e9-fb5b-ad7e3d8f16e1","quantity":-2,"tax":"14.91750","tax_id":"0af7b240-abf0-11e9-fb5b-ae206234d230","tax_total":"-29.83500"},{"discount":"0.00000","id":"0af7b240-abf0-11e9-fb5b-afdbc9791d36","is_return":true,"loyalty_value":"0.00000","note":null,"price":"69.90000","price_set":true,"price_total":"-209.70000","product_id":"0af7b240-abf0-11e9-fb5b-ad7e3d5bd0b7","quantity":-3,"tax":"5.24250","tax_id":"0af7b240-abf0-11e9-fb5b-ae206234d230","tax_total":"-15.72750"}],"return_for":"5687606e-dbc9-8897-11e9-af1dac63fb78","sale_date":"2019-07-26T19:30:12Z","short_code":"ifhccg","source":"USER","source_id":null,"status":"SAVED","taxes":[{"id":"6234ab34-ae20-11e9-9b5b-0af7b240abc5","name":"Sales Tax","rate":"0.07500","tax":65.805}],"totals":{"total_loyalty":"0.00000","total_payment":"0.00000","total_price":"-877.40000","total_tax":"-65.80500","total_to_pay":"-943.20500"},"updated_at":"2019-07-26T19:30:12+00:00","user":{"created_at":"2019-07-23 19:15:30","display_name":"Nick Bonnaud","email":"nick.bonnaud@gmail.com","id":"0af7b240-abc5-11e9-fb5b-ad7e3c8ea635","name":"nick.bonnaud@gmail.com","target_daily":null,"target_monthly":null,"target_weekly":null,"updated_at":"2019-07-23 19:15:30"},"user_id":"0af7b240-abc5-11e9-fb5b-ad7e3c8ea635","version":11445503276}',
		  'retailer_id' => '0af7b240-abc5-11e9-fb5b-ad7e3c86513b',
		  'type' => 'sale.update',
		];
	}

	public static function fakeDoubleReturnSecond($identifier) {
		return [
			'domain_prefix' => 'pockeyttest',
		  'environment' => 'prod',
		  'payload' => '{"created_at":"2019-07-26 19:30:12","customer":{"balance":"0.00000","company_name":null,"contact_first_name":"John","contact_last_name":"Smith","created_at":"2019-07-25 20:14:07","custom_field_1":"' . $identifier . '","custom_field_2":null,"custom_field_3":null,"custom_field_4":null,"customer_code":"John-L99C","customer_group_id":"0af7b240-abc5-11e9-fb5b-ad7e3c8823e8","date_of_birth":null,"deleted_at":null,"do_not_email":false,"email":"john@pockeyt.com","enable_loyalty":true,"fax":null,"first_name":"John","id":"0af7b240-abf0-11e9-fb5b-af18c1b19c1a","last_name":"Smith","loyalty_balance":"0.00000","mobile":null,"note":" Customer","phone":null,"points":0,"sex":null,"updated_at":"2019-07-26 19:31:51","year_to_date":"654.25000"},"customer_id":"0af7b240-abf0-11e9-fb5b-af18c1b19c1a","deleted_at":null,"id":"0af7b240-abf0-11e9-fb5b-afdbc97ad87b","invoice_number":"13","note":"","outlet_id":"0af7b240-abc5-11e9-fb5b-ad7e3c8de6bb","register_id":"0af7b240-abc5-11e9-fb5b-ad7e3c8e53f5","register_sale_payments":[{"amount":-288.96,"id":"5687606e-dbc9-91e8-11e9-afdbfb9eaeb7","payment_date":"2019-07-26T19:31:36Z","payment_type":{"has_native_support":false,"id":"3","name":"Credit Card"},"payment_type_id":3,"retailer_payment_type":{"config":null,"id":"b852168e-794b-4058-8dfc-497caf7807bb","name":"Heist Pay","payment_type_id":"3"},"retailer_payment_type_id":"b852168e-794b-4058-8dfc-497caf7807bb"}],"register_sale_products":[{"discount":"0.00000","id":"0af7b240-abf0-11e9-fb5b-afdbc97843fc","is_return":true,"loyalty_value":"0.00000","note":null,"price":"269.90000","price_set":true,"price_total":"0.00000","product_id":"0af7b240-abf0-11e9-fb5b-ad7e3cb470d8","quantity":0,"tax":"20.24250","tax_id":"0af7b240-abf0-11e9-fb5b-ae206234d230","tax_total":"0.00000"},{"discount":"0.00000","id":"0af7b240-abf0-11e9-fb5b-afdbc978b71e","is_return":true,"loyalty_value":"0.00000","note":null,"price":"198.90000","price_set":true,"price_total":"-198.90000","product_id":"0af7b240-abf0-11e9-fb5b-ad7e3d8f16e1","quantity":-1,"tax":"14.91750","tax_id":"0af7b240-abf0-11e9-fb5b-ae206234d230","tax_total":"-14.91750"},{"discount":"0.00000","id":"0af7b240-abf0-11e9-fb5b-afdbc9791d36","is_return":true,"loyalty_value":"0.00000","note":null,"price":"69.90000","price_set":true,"price_total":"-69.90000","product_id":"0af7b240-abf0-11e9-fb5b-ad7e3d5bd0b7","quantity":-1,"tax":"5.24250","tax_id":"0af7b240-abf0-11e9-fb5b-ae206234d230","tax_total":"-5.24250"}],"return_for":"5687606e-dbc9-8897-11e9-af1dac63fb78","sale_date":"2019-07-26T19:31:36Z","short_code":"q5o9br","source":"USER","source_id":null,"status":"CLOSED","taxes":[{"id":"6234ab34-ae20-11e9-9b5b-0af7b240abc5","name":"Sales Tax","rate":"0.07500","tax":-20.16}],"totals":{"total_loyalty":"0.00000","total_payment":"-288.96000","total_price":"-268.80000","total_tax":"-20.16000","total_to_pay":"0.00000"},"updated_at":"2019-07-26T19:31:51+00:00","user":{"created_at":"2019-07-23 19:15:30","display_name":"Nick Bonnaud","email":"nick.bonnaud@gmail.com","id":"0af7b240-abc5-11e9-fb5b-ad7e3c8ea635","name":"nick.bonnaud@gmail.com","target_daily":null,"target_monthly":null,"target_weekly":null,"updated_at":"2019-07-23 19:15:30"},"user_id":"0af7b240-abc5-11e9-fb5b-ad7e3c8ea635","version":11445516572}',
		  'retailer_id' => '0af7b240-abc5-11e9-fb5b-ad7e3c86513b',
		  'type' => 'sale.update',
		];
	}
}