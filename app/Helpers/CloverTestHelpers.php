<?php

namespace App\Helpers;

use \Faker as Faker;

class CloverTestHelpers {

	public static function fakeCloverAuthResponse() {
		return json_encode(array(
			"access_token" => "not_token"
		));
	}

	public static function fakeInventoryResponse($url) {
		parse_str((parse_url($url))['query'], $query);
		$offset = $query['offset'];
		$numItems = $offset > 100 ? 50 : 100;
		return json_encode([
			"elements" => self::createItemsArray($numItems),
			'href' => $url
		]);
	}

	public static function createItemsArray($numItems) {
		$faker = Faker\Factory::create();
		$items = [];
		$i = 0;
		while ($i < $numItems) {
			$item = [
				'id' => $faker->bothify('s##??###?##????#?'),
				'hidden' => false,
				'name' => $faker->word,
				"alternateName" => $faker->boolean ? $faker->word : "",
				"code" => $faker->ean13,
				'sku' => $faker->ean8,
				"price" => $faker->numberBetween($min = 100, $max = 10000),
				'priceType' => "FIXED",
				'defaultTaxRates' => true,
				'unitName' => "",
				'isRevenue' => true,
				'modifiedTime' => now()
			];
			array_push($items, $item);
			$i++;
		}
		return $items;
	}

	public static function fakeOrderResponse($orderId) {
		if ($orderId == 'not_stored') {
			return json_encode(self::getNotStoredOrder($orderId));
		} elseif ($orderId == 'paid') {
			return json_encode(self::getPaidOrder($orderId));
		} elseif ($orderId == 'partial') {
			return json_encode(self::getPartialPaidOrder($orderId));
		} elseif ($orderId == 'partial_paid_all') {
			return json_encode(self::getPartialPaidAllOrder($orderId));
		} elseif ($orderId == 'add_items_initial') {
			return json_encode(self::getAddItemsInitial($orderId));
		} elseif ($orderId == 'add_items_addition') {
			return json_encode(self::getAddItemsAdditional('add_items_initial'));
		} elseif ($orderId == 'initial_paid_not_tender') {
			return json_encode(self::getInitialNotTender('not_tender'));
		} elseif ($orderId == 'final_paid_not_tender') {
			return json_encode(self::getFinalNotTender('not_tender'));
		} elseif ($orderId == 'partial_update_not_tender') {
			return json_encode(self::getPartialNotTender('not_tender'));
		} elseif ($orderId == 'partial_update_complete') {
			return json_encode(self::getPartialPaidAllOrder('partial'));
		} elseif ($orderId == 'no_change') {
			return json_encode(self::getUpdateNoChange('not_stored'));
		} elseif ($orderId == 'final_paid_tender') {
			return json_encode(self::getFinalPaidTender('not_tender'));
		} elseif ($orderId == 'partial_update_complete_tender') {
			return json_encode(self::getPartialPaidAllOrderTender('partial'));
		} elseif ($orderId == 'refund_full_id') {
			return json_encode(self::getFullRefundOrder($orderId));
		} elseif ($orderId == 'refund_partial_id') {
			return json_encode(self::getPartialRefundOrder($orderId));
		} elseif ($orderId == 'close_full_bill') {
			return json_encode(self::getCloseFullBill($orderId));
		}
	}

	public static function fakeAddNote($customer) {
		return json_encode([
			"href" => "https://sandbox.dev.clover.com/v3/merchants/RR9ACXMZ6AFA1/orders/62X4CVYM3YTKY",
			"id" => 'cdnskvndso',
			"currency" => "USD",
			"employee" => [
				"id" => "TY6XC27QR7RH4"
			],
			"total" => 2043,
			'note' => env('BUSINESS_NAME') . " customer: {$customer->profile->first_name} {$customer->profile->last_name}",
			"taxRemoved" => false,
			"isVat" => false,
			"state" => "open",
			"manualTransaction" => false,
			"groupLineItems" => true,
			"testMode" => false,
			"createdTime" => 1557423651000,
			"clientCreatedTime" => 1557423651000,
			"modifiedTime" => 1557423655000,
			"lineItems" => [
				"elements" => [
					0 => [
						"id" => "5NVEW5Z4A9MM0",
						"orderRef" => [
							"id" => "62X4CVYM3YTKY"
						],
						"item" => [
							"id" => "F3BXRCYTJXB3R"
						],
						"name" => "Beer",
						"alternateName" => "",
						"price" => 300,
						"itemCode" => "",
						"printed" => false,
						"createdTime" => 1557423651000,
						"orderClientCreatedTime" => 1557423651000,
						"exchanged" => false,
						"refunded" => false,
						"isRevenue" => true,
					],
					1 => [
						"id" => "DVY9C98HR12XY",
						"orderRef" => [
							"id" => "62X4CVYM3YTKY"
						],
						"item" => [
							"id" => "F3BXRCYTJXB3R"
						],
						"name" => "Beer",
						"alternateName" => "",
						"price" => 300,
						"itemCode" => "",
						"printed" => false,
						"createdTime" => 1557423652000,
						"orderClientCreatedTime" => 1557423651000,
						"exchanged" => false,
						"refunded" => false,
						"isRevenue" => true,
					],
					2 => [
						"id" => "JTJ22R9Y4ZPHY",
						"orderRef" => [
							"id" => "62X4CVYM3YTKY"
						],
						"item" => [
							"id" => "F3BXRCYTJXB3R"
						],
						"name" => "Beer",
						"alternateName" => "",
						"price" => 300,
						"itemCode" => "",
						"printed" => false,
						"createdTime" => 1557423652000,
						"orderClientCreatedTime" => 1557423651000,
						"exchanged" => false,
						"refunded" => false,
						"isRevenue" => true,
					],
					3 => [
						"id" => "8GS5JQB2Q6H7J",
						"orderRef" => [
							"id" => "62X4CVYM3YTKY"
						],
						"item" => [
							"id" => "Z26TW7H5CCZG2"
						],
						"name" => "Pizza",
						"alternateName" => "",
						"price" => 1000,
						"itemCode" => "",
						"printed" => false,
						"createdTime" => 1557423655000,
						"orderClientCreatedTime" => 1557423651000,
						"exchanged" => false,
						"refunded" => false,
						"isRevenue" => true
					]
				]
			],
			"device" => [
				"id" => "1622c447-b29a-4c47-a625-fd2f6450e175"
			]
		]);
	}


	public static function getCloseFullBill($orderId) {
		return [
			"id" => "853PKH276C9VY",
			"order" => [
				"id" => "VVJD6HPGA66WA"
			],
			"tender" => [
				"href" => "https://sandbox.dev.clover.com/v3/merchants/RR9ACXMZ6AFA1/tenders/BPQN5844528BA",
				"id" => "BPQN5844528BA"
			],
			"amount" => 1398,
			"tipAmount" => 300,
			"taxAmount" => 98,
			"createdTime" => 1557956444000,
			"clientCreatedTime" => 1557956444000,
			"modifiedTime" => 1557956444000,
			"result" => "SUCCESS",
		];
	}

	public static function getPartialRefundOrder($orderId) {
		return [
			"href" => "https://sandbox.dev.clover.com/v3/merchants/RR9ACXMZ6AFA1/orders/62X4CVYM3YTKY",
			"id" => $orderId,
			"currency" => "USD",
			"employee" => [
				"id" => "TY6XC27QR7RH4"
			],
			"total" => 968,
			"taxRemoved" => false,
			"isVat" => false,
			"state" => "locked",
			"manualTransaction" => false,
			"groupLineItems" => true,
			"testMode" => false,
			"payType" =>"FULL",
			"createdTime" => 1557423651000,
			"clientCreatedTime" => 1557423651000,
			"modifiedTime" => 1557423655000,
			"lineItems" => [
				"elements" => [
					0 => [
						"id" => "5NVEW5Z4A9MM0",
						"orderRef" => [
							"id" => "62X4CVYM3YTKY"
						],
						"item" => [
							"id" => "F3BXRCYTJXB3R"
						],
						"name" => "Beer",
						"alternateName" => "",
						"price" => 300,
						"itemCode" => "",
						"printed" => false,
						"createdTime" => 1557423651000,
						"orderClientCreatedTime" => 1557423651000,
						"exchanged" => false,
						"refunded" => false,
						"isRevenue" => true,
					],
					1 => [
						"id" => "DVY9C98HR12XY",
						"orderRef" => [
							"id" => "62X4CVYM3YTKY"
						],
						"item" => [
							"id" => "F3BXRCYTJXB3R"
						],
						"name" => "Beer",
						"alternateName" => "",
						"price" => 300,
						"itemCode" => "",
						"printed" => false,
						"createdTime" => 1557423652000,
						"orderClientCreatedTime" => 1557423651000,
						"exchanged" => false,
						"refunded" => false,
						"isRevenue" => true,
					],
					2 => [
						"id" => "JTJ22R9Y4ZPHY",
						"orderRef" => [
							"id" => "62X4CVYM3YTKY"
						],
						"item" => [
							"id" => "F3BXRCYTJXB3R"
						],
						"name" => "Beer",
						"alternateName" => "",
						"price" => 300,
						"itemCode" => "",
						"printed" => false,
						"createdTime" => 1557423652000,
						"orderClientCreatedTime" => 1557423651000,
						"exchanged" => false,
						"refunded" => false,
						"isRevenue" => true,
					],
					3 => [
						"id" => "8GS5JQB2Q6H7J",
						"orderRef" => [
							"id" => "62X4CVYM3YTKY"
						],
						"item" => [
							"id" => "Z26TW7H5CCZG2"
						],
						"name" => "Pizza",
						"alternateName" => "",
						"price" => 1000,
						"itemCode" => "",
						"printed" => false,
						"createdTime" => 1557423655000,
						"orderClientCreatedTime" => 1557423651000,
						"exchanged" => false,
						"refunded" => true,
						"refund" => [
							'id' => "19TC5HT6TT3NG"
						],
						"isRevenue" => true
					]
				]
			],
			"payments" => [
				"elements" =>[
					0 => [
						"id" =>"N418C6R2KHKEE",
						"order" => [
							"id" => $orderId
						],
						"device" => [
							"id" =>"1622c447-b29a-4c47-a625-fd2f6450e175"
						],
						"tender" => [
							"href" =>"https://sandbox.dev.clover.com/v3/merchants/RR9ACXMZ6AFA1/tenders/N4YEJK89A6E5M",
							"id" => "fake_tender_id"
						],
						"amount" => 2043,
						"taxAmount" => 143,
						"cashTendered" => 2043,
						"employee" => [
							"id" => "TY6XC27QR7RH4"
						],
						"createdTime" => 1557423698000,
						"clientCreatedTime" => 1557423698000,
						"modifiedTime" => 1557423698000,
						"result" => "SUCCESS"
					]
				]
			],
			"refunds" => [
				"elements" => [
					0 => [
						"id" => "MMRTG4BJEP1E4",
						"orderRef" => [
							"href" => "https://sandbox.dev.clover.com/v3/merchants/RR9ACXMZ6AFA1/orders/GFAFP15WAMZCG",
							"id" => "GFAFP15WAMZCG",
							"currency" => "USD",
							"employee" => [
								"href" => "https://sandbox.dev.clover.com/v3/merchants/RR9ACXMZ6AFA1/employees/TY6XC27QR7RH4",
								"id" => "TY6XC27QR7RH4",
								"orders" => [
									"href" => "https://sandbox.dev.clover.com/v3/merchants/RR9ACXMZ6AFA1/employees/TY6XC27QR7RH4/orders"
								]
							],
							"total" => 968,
							"taxRemoved" => false,
							"isVat" => false,
							"state" => "locked",
							"manualTransaction" => false,
							"groupLineItems" => true,
							"testMode" => false,
							"payType" => "FULL",
							"createdTime" => 1557423669000,
							"clientCreatedTime" => 1557423668000,
							"modifiedTime" => 1557946783000,
							"device" => [
								"href" => "https://sandbox.dev.clover.com/v3/merchants/RR9ACXMZ6AFA1/devices/1622c447-b29a-4c47-a625-fd2f6450e1 ...",
								"id" => "1622c447-b29a-4c47-a625-fd2f6450e175"
							]
						],
						"device" => [
							"id" => "1622c447-b29a-4c47-a625-fd2f6450e175"
						],
						"amount" => 1075,
						"taxAmount" => 75,
						"tipAmount" => 0,
						"createdTime" => 1557946783000,
						"clientCreatedTime" => 1557946783000,
						"payment" => [
							"id" => "N418C6R2KHKEE",
							"order" => [
								"id" => "GFAFP15WAMZCG"
							],
							"device" => [
								"href" => "https://sandbox.dev.clover.com/v3/merchants/RR9ACXMZ6AFA1/devices/1622c447-b29a-4c47-a625-fd2f6450e1 ...",
								"id" => "1622c447-b29a-4c47-a625-fd2f6450e175"
							],
							"tender" => [
								"href" => "https://sandbox.dev.clover.com/v3/merchants/RR9ACXMZ6AFA1/tenders/N4YEJK89A6E5M",
								"id" => "fake_tender_id"
							],
							"amount" => 2043,
							"tipAmount" => 0,
							"taxAmount" => 143,
							"cashbackAmount" => 0,
							"cashTendered" => 2043,
							"employee" => [
								"href" => "https://sandbox.dev.clover.com/v3/merchants/RR9ACXMZ6AFA1/employees/TY6XC27QR7RH4",
								"id" => "TY6XC27QR7RH4",
								"orders" => [
									"href" => "https://sandbox.dev.clover.com/v3/merchants/RR9ACXMZ6AFA1/employees/TY6XC27QR7RH4/orders"
								]
							],
							"createdTime" => 1557423698000,
							"clientCreatedTime" => 1557423698000,
							"modifiedTime" => 1557423698000,
							"offline" => false,
							"result" => "SUCCESS",
						],
						"employee" => [
							"id" => "TY6XC27QR7RH4"
						],
						"voided" => false
					]
				]
			],
			"device" => [
				"id" => "1622c447-b29a-4c47-a625-fd2f6450e175"
			]
		];
	}

	public static function getFullRefundOrder($orderId) {
		return [
			"href" => "https://sandbox.dev.clover.com/v3/merchants/RR9ACXMZ6AFA1/orders/62X4CVYM3YTKY",
			"id" => $orderId,
			"currency" => "USD",
			"employee" => [
				"id" => "TY6XC27QR7RH4"
			],
			"total" => 2043,
			"taxRemoved" => false,
			"isVat" => false,
			"state" => "locked",
			"manualTransaction" => false,
			"groupLineItems" => true,
			"testMode" => false,
			"payType" =>"FULL",
			"createdTime" => 1557423651000,
			"clientCreatedTime" => 1557423651000,
			"modifiedTime" => 1557423655000,
			"lineItems" => [
				"elements" => [
					0 => [
						"id" => "5NVEW5Z4A9MM0",
						"orderRef" => [
							"id" => "62X4CVYM3YTKY"
						],
						"item" => [
							"id" => "F3BXRCYTJXB3R"
						],
						"name" => "Beer",
						"alternateName" => "",
						"price" => 300,
						"itemCode" => "",
						"printed" => false,
						"createdTime" => 1557423651000,
						"orderClientCreatedTime" => 1557423651000,
						"exchanged" => false,
						"refunded" => false,
						"isRevenue" => true,
					],
					1 => [
						"id" => "DVY9C98HR12XY",
						"orderRef" => [
							"id" => "62X4CVYM3YTKY"
						],
						"item" => [
							"id" => "F3BXRCYTJXB3R"
						],
						"name" => "Beer",
						"alternateName" => "",
						"price" => 300,
						"itemCode" => "",
						"printed" => false,
						"createdTime" => 1557423652000,
						"orderClientCreatedTime" => 1557423651000,
						"exchanged" => false,
						"refunded" => false,
						"isRevenue" => true,
					],
					2 => [
						"id" => "JTJ22R9Y4ZPHY",
						"orderRef" => [
							"id" => "62X4CVYM3YTKY"
						],
						"item" => [
							"id" => "F3BXRCYTJXB3R"
						],
						"name" => "Beer",
						"alternateName" => "",
						"price" => 300,
						"itemCode" => "",
						"printed" => false,
						"createdTime" => 1557423652000,
						"orderClientCreatedTime" => 1557423651000,
						"exchanged" => false,
						"refunded" => false,
						"isRevenue" => true,
					],
					3 => [
						"id" => "8GS5JQB2Q6H7J",
						"orderRef" => [
							"id" => "62X4CVYM3YTKY"
						],
						"item" => [
							"id" => "Z26TW7H5CCZG2"
						],
						"name" => "Pizza",
						"alternateName" => "",
						"price" => 1000,
						"itemCode" => "",
						"printed" => false,
						"createdTime" => 1557423655000,
						"orderClientCreatedTime" => 1557423651000,
						"exchanged" => false,
						"refunded" => false,
						"isRevenue" => true
					]
				]
			],
			"payments" => [
				"elements" =>[
					0 => [
						"id" =>"N418C6R2KHKEE",
						"order" => [
							"id" => $orderId
						],
						"device" => [
							"id" =>"1622c447-b29a-4c47-a625-fd2f6450e175"
						],
						"tender" => [
							"href" =>"https://sandbox.dev.clover.com/v3/merchants/RR9ACXMZ6AFA1/tenders/N4YEJK89A6E5M",
							"id" => "fake_tender_id"
						],
						"amount" => 2043,
						"taxAmount" => 143,
						"cashTendered" => 2043,
						"employee" => [
							"id" => "TY6XC27QR7RH4"
						],
						"createdTime" => 1557423698000,
						"clientCreatedTime" => 1557423698000,
						"modifiedTime" => 1557423698000,
						"result" => "SUCCESS"
					]
				]
			],
			"refunds" => [
				"elements" => [
					0 => [
						"id" => "MMRTG4BJEP1E4",
						"orderRef" => [
							"href" => "https://sandbox.dev.clover.com/v3/merchants/RR9ACXMZ6AFA1/orders/GFAFP15WAMZCG",
							"id" => "GFAFP15WAMZCG",
							"currency" => "USD",
							"employee" => [
								"href" => "https://sandbox.dev.clover.com/v3/merchants/RR9ACXMZ6AFA1/employees/TY6XC27QR7RH4",
								"id" => "TY6XC27QR7RH4",
								"orders" => [
									"href" => "https://sandbox.dev.clover.com/v3/merchants/RR9ACXMZ6AFA1/employees/TY6XC27QR7RH4/orders"
								]
							],
							"total" => 2043,
							"taxRemoved" => false,
							"isVat" => false,
							"state" => "locked",
							"manualTransaction" => false,
							"groupLineItems" => true,
							"testMode" => false,
							"payType" => "FULL",
							"createdTime" => 1557423669000,
							"clientCreatedTime" => 1557423668000,
							"modifiedTime" => 1557946783000,
							"device" => [
								"href" => "https://sandbox.dev.clover.com/v3/merchants/RR9ACXMZ6AFA1/devices/1622c447-b29a-4c47-a625-fd2f6450e1 ...",
								"id" => "1622c447-b29a-4c47-a625-fd2f6450e175"
							]
						],
						"device" => [
							"id" => "1622c447-b29a-4c47-a625-fd2f6450e175"
						],
						"amount" => 2043,
						"taxAmount" => 143,
						"tipAmount" => 0,
						"createdTime" => 1557946783000,
						"clientCreatedTime" => 1557946783000,
						"payment" => [
							"id" => "N418C6R2KHKEE",
							"order" => [
								"id" => "GFAFP15WAMZCG"
							],
							"device" => [
								"href" => "https://sandbox.dev.clover.com/v3/merchants/RR9ACXMZ6AFA1/devices/1622c447-b29a-4c47-a625-fd2f6450e1 ...",
								"id" => "1622c447-b29a-4c47-a625-fd2f6450e175"
							],
							"tender" => [
								"href" => "https://sandbox.dev.clover.com/v3/merchants/RR9ACXMZ6AFA1/tenders/N4YEJK89A6E5M",
								"id" => "fake_tender_id"
							],
							"amount" => 2043,
							"tipAmount" => 0,
							"taxAmount" => 143,
							"cashbackAmount" => 0,
							"cashTendered" => 2043,
							"employee" => [
								"href" => "https://sandbox.dev.clover.com/v3/merchants/RR9ACXMZ6AFA1/employees/TY6XC27QR7RH4",
								"id" => "TY6XC27QR7RH4",
								"orders" => [
									"href" => "https://sandbox.dev.clover.com/v3/merchants/RR9ACXMZ6AFA1/employees/TY6XC27QR7RH4/orders"
								]
							],
							"createdTime" => 1557423698000,
							"clientCreatedTime" => 1557423698000,
							"modifiedTime" => 1557423698000,
							"offline" => false,
							"result" => "SUCCESS",
						],
						"employee" => [
							"id" => "TY6XC27QR7RH4"
						],
						"voided" => false
					]
				]
			],
			"device" => [
				"id" => "1622c447-b29a-4c47-a625-fd2f6450e175"
			]
		];
	}

	public static function getPartialPaidAllOrderTender($orderId) {
		return [
			"href" => "https://sandbox.dev.clover.com/v3/merchants/RR9ACXMZ6AFA1/orders/BKT9FWHMFK916",
			"id" => $orderId,
			"currency" => "USD",
			"employee" => [
				"id" => "TY6XC27QR7RH4"
			],
			"total" => 4085,
			"note" => "",
			"taxRemoved" => false,
			"isVat" => false,
			"state" => "locked",
			"manualTransaction" => false,
			"groupLineItems" => true,
			"testMode" => false,
			"payType" => "SPLIT_CUSTOM",
			"createdTime" => 1557434285000,
			"clientCreatedTime" => 1557434285000,
			"modifiedTime" => 1557442455000,
			"lineItems" => [
				"elements" => [
					0 => [
						"id" => "T5R3HS5PQRD9M",
						"orderRef" => [
							"id" => "BKT9FWHMFK916"
						],
						"item" => [
							"id" => "F3BXRCYTJXB3R"
						],
						"name" => "Beer",
						"alternateName" => "",
						"price" => 300,
						"itemCode" => "",
						"printed" => false,
						"createdTime" => 1557434285000,
						"orderClientCreatedTime" => 1557434285000,
						"exchanged" => false,
						"refunded" => false,
						"isRevenue" => true
					],
					1 => [
						"id" => "EDA2XXP90M42A",
						"orderRef" => [
							"id" => "BKT9FWHMFK916"
						],
						"item" => [
							"id" => "Z26TW7H5CCZG2"
						],
						"name" => "Pizza",
						"alternateName" => "",
						"price" => 1000,
						"itemCode" => "",
						"printed" => false,
						"createdTime" => 1557434287000,
						"orderClientCreatedTime" => 1557434285000,
						"exchanged" => false,
						"refunded" => false,
						"isRevenue" => true,
					],
					2 => [
						"id" => "ES8V2PFTKR100",
						"orderRef" => [
							"id" => "BKT9FWHMFK916"
						],
						"item" => [
							"id" => "F3BXRCYTJXB3R"
						],
						"name" => "Beer",
						"alternateName" => "",
						"price" => 300,
						"itemCode" => "",
						"printed" => false,
						"createdTime" => 1557436673000,
						"orderClientCreatedTime" => 1557434285000,
						"exchanged" => false,
						"refunded" => false,
						"isRevenue" => true,
					],
					3 => [
						"id" => "9CEPR9ZPG69CP",
						"orderRef" => [
							"id" => "BKT9FWHMFK916"
						],
						"item" => [
							"id" => "F3BXRCYTJXB3R"
						],
						"name" => "Beer",
						"alternateName" => "",
						"price" => 300,
						"itemCode" => "",
						"printed" => false,
						"createdTime" => 1557436679000,
						"orderClientCreatedTime" => 1557434285000,
						"exchanged" => false,
						"refunded" => false,
						"isRevenue" => true,
					],
					4 => [
						"id" => "A3CADAEZ3QKGY",
						"orderRef" => [
							"id" => "BKT9FWHMFK916"
						],
						"item" => [
							"id" => "F3BXRCYTJXB3R"
						],
						"name" => "Beer",
						"alternateName" => "",
						"price" => 300,
						"itemCode" => "",
						"printed" => false,
						"createdTime" => 1557442417000,
						"orderClientCreatedTime" => 1557434285000,
						"exchanged" => false,
						"refunded" => false,
						"isRevenue" => true,
					],
					5 => [
						"id" => "127NQRS7X5P9Y",
						"orderRef" => [
							"id" => "BKT9FWHMFK916"
						],
						"item" => [
							"id" => "F3BXRCYTJXB3R"
						],
						"name" => "Beer",
						"alternateName" => "",
						"price" => 300,
						"itemCode" => "",
						"printed" => false,
						"createdTime" => 1557442421000,
						"orderClientCreatedTime" => 1557434285000,
						"exchanged" => false,
						"refunded" => false,
						"isRevenue" => true,
					],
					6 => [
						"id" => "XVS8GJF62JKRM",
						"orderRef" => [
							"id" => "BKT9FWHMFK916"
						],
						"item" => [
							"id" => "Z26TW7H5CCZG2"
						],
						"name" => "Pizza",
						"alternateName" => "",
						"price" => 1000,
						"itemCode" => "",
						"printed" => false,
						"createdTime" => 1557442422000,
						"orderClientCreatedTime" => 1557434285000,
						"exchanged" => false,
						"refunded" => false,
						"isRevenue" => true,
					],
					7 => [
						"id" => "JNV1AEN5AJXT6",
						"orderRef" => [
							"id" => "BKT9FWHMFK916"
						],
						"item" => [
							"id" => "F3BXRCYTJXB3R"
						],
						"name" => "Beer",
						"alternateName" => "",
						"price" => 300,
						"itemCode" => "",
						"printed" => false,
						"createdTime" => 1557442424000,
						"orderClientCreatedTime" => 1557434285000,
						"exchanged" => false,
						"refunded" => false,
						"isRevenue" => true,
					]
				]
			],
			"payments" => [
				"elements" => [
					0 => [
						"id" => "KXPAKNSKCRVYE",
						"order" => [
							"id" => "BKT9FWHMFK916"
						],
						"device" => [
							"id" => "1622c447-b29a-4c47-a625-fd2f6450e175"
						],
						"tender" => [
							"href" => "https://sandbox.dev.clover.com/v3/merchants/RR9ACXMZ6AFA1/tenders/N4YEJK89A6E5M",
							"id" => "N4YEJK89A6E5M"
						],
						"amount" => 2043,
						"taxAmount" => 143,
						"cashTendered" => 2043,
						"employee" => [
							"id" => "TY6XC27QR7RH4"
						],
						"createdTime" => 1557442455000,
						"clientCreatedTime" => 1557442452000,
						"modifiedTime" => 1557442455000,
						"result" => "SUCCESS",
					],
					1 => [
						"id" => "44YC0WXQ7MKW2",
						"order" => [
							"id" => "BKT9FWHMFK916"
						],
						"device" => [
							"id" => "1622c447-b29a-4c47-a625-fd2f6450e175"
						],
						"tender" => [
							"href" => "https://sandbox.dev.clover.com/v3/merchants/RR9ACXMZ6AFA1/tenders/N4YEJK89A6E5M",
							"id" => "fake_tender_id"
						],
						"amount" => 2042,
						"taxAmount" => 142,
						"cashTendered" => 2042,
						"employee" => [
							"id" => "TY6XC27QR7RH4"
						],
						"createdTime" => 1557513168000,
						"clientCreatedTime" => 1557513167000,
						"modifiedTime" => 1557513168000,
						"result" => "SUCCESS"
					]
				]
			],
			"device" => [
				"id" => "1622c447-b29a-4c47-a625-fd2f6450e175"
			]
		];
	}

	public static function getFinalPaidTender($orderId) {
		return [
			"href" => "https://sandbox.dev.clover.com/v3/merchants/RR9ACXMZ6AFA1/orders/62X4CVYM3YTKY",
			"id" => $orderId,
			"currency" => "USD",
			"employee" => [
				"id" => "TY6XC27QR7RH4"
			],
			"total" => 2043,
			"taxRemoved" => false,
			"isVat" => false,
			"state" => "locked",
			"manualTransaction" => false,
			"groupLineItems" => true,
			"testMode" => false,
			"payType" =>"FULL",
			"createdTime" => 1557423651000,
			"clientCreatedTime" => 1557423651000,
			"modifiedTime" => 1557423655000,
			"lineItems" => [
				"elements" => [
					0 => [
						"id" => "5NVEW5Z4A9MM0",
						"orderRef" => [
							"id" => "62X4CVYM3YTKY"
						],
						"item" => [
							"id" => "F3BXRCYTJXB3R"
						],
						"name" => "Beer",
						"alternateName" => "",
						"price" => 300,
						"itemCode" => "",
						"printed" => false,
						"createdTime" => 1557423651000,
						"orderClientCreatedTime" => 1557423651000,
						"exchanged" => false,
						"refunded" => false,
						"isRevenue" => true,
					],
					1 => [
						"id" => "DVY9C98HR12XY",
						"orderRef" => [
							"id" => "62X4CVYM3YTKY"
						],
						"item" => [
							"id" => "F3BXRCYTJXB3R"
						],
						"name" => "Beer",
						"alternateName" => "",
						"price" => 300,
						"itemCode" => "",
						"printed" => false,
						"createdTime" => 1557423652000,
						"orderClientCreatedTime" => 1557423651000,
						"exchanged" => false,
						"refunded" => false,
						"isRevenue" => true,
					],
					2 => [
						"id" => "JTJ22R9Y4ZPHY",
						"orderRef" => [
							"id" => "62X4CVYM3YTKY"
						],
						"item" => [
							"id" => "F3BXRCYTJXB3R"
						],
						"name" => "Beer",
						"alternateName" => "",
						"price" => 300,
						"itemCode" => "",
						"printed" => false,
						"createdTime" => 1557423652000,
						"orderClientCreatedTime" => 1557423651000,
						"exchanged" => false,
						"refunded" => false,
						"isRevenue" => true,
					],
					3 => [
						"id" => "8GS5JQB2Q6H7J",
						"orderRef" => [
							"id" => "62X4CVYM3YTKY"
						],
						"item" => [
							"id" => "Z26TW7H5CCZG2"
						],
						"name" => "Pizza",
						"alternateName" => "",
						"price" => 1000,
						"itemCode" => "",
						"printed" => false,
						"createdTime" => 1557423655000,
						"orderClientCreatedTime" => 1557423651000,
						"exchanged" => false,
						"refunded" => false,
						"isRevenue" => true
					]
				]
			],
			"payments" => [
				"elements" =>[
					0 => [
						"id" =>"N418C6R2KHKEE",
						"order" => [
							"id" => $orderId
						],
						"device" => [
							"id" =>"1622c447-b29a-4c47-a625-fd2f6450e175"
						],
						"tender" => [
							"href" =>"https://sandbox.dev.clover.com/v3/merchants/RR9ACXMZ6AFA1/tenders/N4YEJK89A6E5M",
							"id" => "fake_tender_id"
						],
						"amount" => 2043,
						"taxAmount" => 143,
						"cashTendered" => 2043,
						"employee" => [
							"id" => "TY6XC27QR7RH4"
						],
						"createdTime" => 1557423698000,
						"clientCreatedTime" => 1557423698000,
						"modifiedTime" => 1557423698000,
						"result" => "SUCCESS"
					]
				]
			],
			"device" => [
				"id" => "1622c447-b29a-4c47-a625-fd2f6450e175"
			]
		];
	}

	public static function getUpdateNoChange($orderId) {
		return [ 
			"href" => "https://sandbox.dev.clover.com/v3/merchants/RR9ACXMZ6AFA1/orders/62X4CVYM3YTKY",
			"id" => $orderId,
			"currency" => "USD",
			"employee" => [
				"id" => "TY6XC27QR7RH4"
			],
			"total" => 2043,
			"note" => "some new note",
			"taxRemoved" => false,
			"isVat" => false,
			"state" => "open",
			"manualTransaction" => false,
			"groupLineItems" => true,
			"testMode" => false,
			"createdTime" => 1557423651000,
			"clientCreatedTime" => 1557423651000,
			"modifiedTime" => 1557423655000,
			"lineItems" => [
				"elements" => [
					0 => [
						"id" => "5NVEW5Z4A9MM0",
						"orderRef" => [
							"id" => "62X4CVYM3YTKY"
						],
						"item" => [
							"id" => "F3BXRCYTJXB3R"
						],
						"name" => "Beer",
						"alternateName" => "",
						"price" => 300,
						"itemCode" => "",
						"printed" => false,
						"createdTime" => 1557423651000,
						"orderClientCreatedTime" => 1557423651000,
						"exchanged" => false,
						"refunded" => false,
						"isRevenue" => true,
					],
					1 => [
						"id" => "DVY9C98HR12XY",
						"orderRef" => [
							"id" => "62X4CVYM3YTKY"
						],
						"item" => [
							"id" => "F3BXRCYTJXB3R"
						],
						"name" => "Beer",
						"alternateName" => "",
						"price" => 300,
						"itemCode" => "",
						"printed" => false,
						"createdTime" => 1557423652000,
						"orderClientCreatedTime" => 1557423651000,
						"exchanged" => false,
						"refunded" => false,
						"isRevenue" => true,
					],
					2 => [
						"id" => "JTJ22R9Y4ZPHY",
						"orderRef" => [
							"id" => "62X4CVYM3YTKY"
						],
						"item" => [
							"id" => "F3BXRCYTJXB3R"
						],
						"name" => "Beer",
						"alternateName" => "",
						"price" => 300,
						"itemCode" => "",
						"printed" => false,
						"createdTime" => 1557423652000,
						"orderClientCreatedTime" => 1557423651000,
						"exchanged" => false,
						"refunded" => false,
						"isRevenue" => true,
					],
					3 => [
						"id" => "8GS5JQB2Q6H7J",
						"orderRef" => [
							"id" => "62X4CVYM3YTKY"
						],
						"item" => [
							"id" => "Z26TW7H5CCZG2"
						],
						"name" => "Pizza",
						"alternateName" => "",
						"price" => 1000,
						"itemCode" => "",
						"printed" => false,
						"createdTime" => 1557423655000,
						"orderClientCreatedTime" => 1557423651000,
						"exchanged" => false,
						"refunded" => false,
						"isRevenue" => true
					]
				]
			],
			"device" => [
				"id" => "1622c447-b29a-4c47-a625-fd2f6450e175"
			]
		];
	}

	public static function getPartialNotTender($orderId) {
		return [
			"href" => "https://sandbox.dev.clover.com/v3/merchants/RR9ACXMZ6AFA1/orders/62X4CVYM3YTKY",
			"id" => $orderId,
			"currency" => "USD",
			"employee" => [
				"id" => "TY6XC27QR7RH4"
			],
			"total" => 2043,
			"taxRemoved" => false,
			"isVat" => false,
			"state" => "locked",
			"manualTransaction" => false,
			"groupLineItems" => true,
			"testMode" => false,
			"payType" => "SPLIT_CUSTOM",
			"createdTime" => 1557423651000,
			"clientCreatedTime" => 1557423651000,
			"modifiedTime" => 1557423655000,
			"lineItems" => [
				"elements" => [
					0 => [
						"id" => "5NVEW5Z4A9MM0",
						"orderRef" => [
							"id" => "62X4CVYM3YTKY"
						],
						"item" => [
							"id" => "F3BXRCYTJXB3R"
						],
						"name" => "Beer",
						"alternateName" => "",
						"price" => 300,
						"itemCode" => "",
						"printed" => false,
						"createdTime" => 1557423651000,
						"orderClientCreatedTime" => 1557423651000,
						"exchanged" => false,
						"refunded" => false,
						"isRevenue" => true,
					],
					1 => [
						"id" => "DVY9C98HR12XY",
						"orderRef" => [
							"id" => "62X4CVYM3YTKY"
						],
						"item" => [
							"id" => "F3BXRCYTJXB3R"
						],
						"name" => "Beer",
						"alternateName" => "",
						"price" => 300,
						"itemCode" => "",
						"printed" => false,
						"createdTime" => 1557423652000,
						"orderClientCreatedTime" => 1557423651000,
						"exchanged" => false,
						"refunded" => false,
						"isRevenue" => true,
					],
					2 => [
						"id" => "JTJ22R9Y4ZPHY",
						"orderRef" => [
							"id" => "62X4CVYM3YTKY"
						],
						"item" => [
							"id" => "F3BXRCYTJXB3R"
						],
						"name" => "Beer",
						"alternateName" => "",
						"price" => 300,
						"itemCode" => "",
						"printed" => false,
						"createdTime" => 1557423652000,
						"orderClientCreatedTime" => 1557423651000,
						"exchanged" => false,
						"refunded" => false,
						"isRevenue" => true,
					],
					3 => [
						"id" => "8GS5JQB2Q6H7J",
						"orderRef" => [
							"id" => "62X4CVYM3YTKY"
						],
						"item" => [
							"id" => "Z26TW7H5CCZG2"
						],
						"name" => "Pizza",
						"alternateName" => "",
						"price" => 1000,
						"itemCode" => "",
						"printed" => false,
						"createdTime" => 1557423655000,
						"orderClientCreatedTime" => 1557423651000,
						"exchanged" => false,
						"refunded" => false,
						"isRevenue" => true
					]
				]
			],
			"payments" => [
				"elements" => [
					0 => [
						"id" => "KXPAKNSKCRVYE",
						"order" => [
							"id" => "BKT9FWHMFK916"
						],
						"device" => [
							"id" => "1622c447-b29a-4c47-a625-fd2f6450e175"
						],
						"tender" => [
							"href" => "https://sandbox.dev.clover.com/v3/merchants/RR9ACXMZ6AFA1/tenders/N4YEJK89A6E5M",
							"id" => "N4YEJK89A6E5M"
						],
						"amount" => 1022,
						"taxAmount" => 72,
						"cashTendered" => 1022,
						"employee" => [
							"id" => "TY6XC27QR7RH4"
						],
						"createdTime" => 1557442455000,
						"clientCreatedTime" => 1557442452000,
						"modifiedTime" => 1557442455000,
						"result" => "SUCCESS",
					]
				]
			],
			"device" => [
				"id" => "1622c447-b29a-4c47-a625-fd2f6450e175"
			]
		];
	}

	public static function getFinalNotTender($orderId) {
		return [
			"href" => "https://sandbox.dev.clover.com/v3/merchants/RR9ACXMZ6AFA1/orders/62X4CVYM3YTKY",
			"id" => $orderId,
			"currency" => "USD",
			"employee" => [
				"id" => "TY6XC27QR7RH4"
			],
			"total" => 2043,
			"taxRemoved" => false,
			"isVat" => false,
			"state" => "locked",
			"manualTransaction" => false,
			"groupLineItems" => true,
			"testMode" => false,
			"payType" =>"FULL",
			"createdTime" => 1557423651000,
			"clientCreatedTime" => 1557423651000,
			"modifiedTime" => 1557423655000,
			"lineItems" => [
				"elements" => [
					0 => [
						"id" => "5NVEW5Z4A9MM0",
						"orderRef" => [
							"id" => "62X4CVYM3YTKY"
						],
						"item" => [
							"id" => "F3BXRCYTJXB3R"
						],
						"name" => "Beer",
						"alternateName" => "",
						"price" => 300,
						"itemCode" => "",
						"printed" => false,
						"createdTime" => 1557423651000,
						"orderClientCreatedTime" => 1557423651000,
						"exchanged" => false,
						"refunded" => false,
						"isRevenue" => true,
					],
					1 => [
						"id" => "DVY9C98HR12XY",
						"orderRef" => [
							"id" => "62X4CVYM3YTKY"
						],
						"item" => [
							"id" => "F3BXRCYTJXB3R"
						],
						"name" => "Beer",
						"alternateName" => "",
						"price" => 300,
						"itemCode" => "",
						"printed" => false,
						"createdTime" => 1557423652000,
						"orderClientCreatedTime" => 1557423651000,
						"exchanged" => false,
						"refunded" => false,
						"isRevenue" => true,
					],
					2 => [
						"id" => "JTJ22R9Y4ZPHY",
						"orderRef" => [
							"id" => "62X4CVYM3YTKY"
						],
						"item" => [
							"id" => "F3BXRCYTJXB3R"
						],
						"name" => "Beer",
						"alternateName" => "",
						"price" => 300,
						"itemCode" => "",
						"printed" => false,
						"createdTime" => 1557423652000,
						"orderClientCreatedTime" => 1557423651000,
						"exchanged" => false,
						"refunded" => false,
						"isRevenue" => true,
					],
					3 => [
						"id" => "8GS5JQB2Q6H7J",
						"orderRef" => [
							"id" => "62X4CVYM3YTKY"
						],
						"item" => [
							"id" => "Z26TW7H5CCZG2"
						],
						"name" => "Pizza",
						"alternateName" => "",
						"price" => 1000,
						"itemCode" => "",
						"printed" => false,
						"createdTime" => 1557423655000,
						"orderClientCreatedTime" => 1557423651000,
						"exchanged" => false,
						"refunded" => false,
						"isRevenue" => true
					]
				]
			],
			"payments" => [
				"elements" =>[
					0 => [
						"id" =>"N418C6R2KHKEE",
						"order" => [
							"id" => $orderId
						],
						"device" => [
							"id" =>"1622c447-b29a-4c47-a625-fd2f6450e175"
						],
						"tender" => [
							"href" =>"https://sandbox.dev.clover.com/v3/merchants/RR9ACXMZ6AFA1/tenders/N4YEJK89A6E5M",
							"id" => "N4YEJK89A6E5M"
						],
						"amount" => 2043,
						"taxAmount" => 143,
						"cashTendered" => 2043,
						"employee" => [
							"id" => "TY6XC27QR7RH4"
						],
						"createdTime" => 1557423698000,
						"clientCreatedTime" => 1557423698000,
						"modifiedTime" => 1557423698000,
						"result" => "SUCCESS"
					]
				]
			],
			"device" => [
				"id" => "1622c447-b29a-4c47-a625-fd2f6450e175"
			]
		];
	}

	public static function getInitialNotTender($orderId) {
		return [
			"href" => "https://sandbox.dev.clover.com/v3/merchants/RR9ACXMZ6AFA1/orders/62X4CVYM3YTKY",
			"id" => $orderId,
			"currency" => "USD",
			"employee" => [
				"id" => "TY6XC27QR7RH4"
			],
			"total" => 2043,
			"taxRemoved" => false,
			"isVat" => false,
			"state" => "open",
			"manualTransaction" => false,
			"groupLineItems" => true,
			"testMode" => false,
			"createdTime" => 1557423651000,
			"clientCreatedTime" => 1557423651000,
			"modifiedTime" => 1557423655000,
			"lineItems" => [
				"elements" => [
					0 => [
						"id" => "5NVEW5Z4A9MM0",
						"orderRef" => [
							"id" => "62X4CVYM3YTKY"
						],
						"item" => [
							"id" => "F3BXRCYTJXB3R"
						],
						"name" => "Beer",
						"alternateName" => "",
						"price" => 300,
						"itemCode" => "",
						"printed" => false,
						"createdTime" => 1557423651000,
						"orderClientCreatedTime" => 1557423651000,
						"exchanged" => false,
						"refunded" => false,
						"isRevenue" => true,
					],
					1 => [
						"id" => "DVY9C98HR12XY",
						"orderRef" => [
							"id" => "62X4CVYM3YTKY"
						],
						"item" => [
							"id" => "F3BXRCYTJXB3R"
						],
						"name" => "Beer",
						"alternateName" => "",
						"price" => 300,
						"itemCode" => "",
						"printed" => false,
						"createdTime" => 1557423652000,
						"orderClientCreatedTime" => 1557423651000,
						"exchanged" => false,
						"refunded" => false,
						"isRevenue" => true,
					],
					2 => [
						"id" => "JTJ22R9Y4ZPHY",
						"orderRef" => [
							"id" => "62X4CVYM3YTKY"
						],
						"item" => [
							"id" => "F3BXRCYTJXB3R"
						],
						"name" => "Beer",
						"alternateName" => "",
						"price" => 300,
						"itemCode" => "",
						"printed" => false,
						"createdTime" => 1557423652000,
						"orderClientCreatedTime" => 1557423651000,
						"exchanged" => false,
						"refunded" => false,
						"isRevenue" => true,
					],
					3 => [
						"id" => "8GS5JQB2Q6H7J",
						"orderRef" => [
							"id" => "62X4CVYM3YTKY"
						],
						"item" => [
							"id" => "Z26TW7H5CCZG2"
						],
						"name" => "Pizza",
						"alternateName" => "",
						"price" => 1000,
						"itemCode" => "",
						"printed" => false,
						"createdTime" => 1557423655000,
						"orderClientCreatedTime" => 1557423651000,
						"exchanged" => false,
						"refunded" => false,
						"isRevenue" => true
					]
				]
			],
			"device" => [
				"id" => "1622c447-b29a-4c47-a625-fd2f6450e175"
			]
		];
	}

	public static function getAddItemsAdditional($orderId) {
		return [
			"href" => "https://sandbox.dev.clover.com/v3/merchants/RR9ACXMZ6AFA1/orders/7BWY5AH2DNP20",
			"id" => $orderId,
			"currency" => "USD",
			"employee" => [
				"id" => "TY6XC27QR7RH4"
			],
			"total" => 1720,
			"taxRemoved" => false,
			"isVat" => false,
			"state" => "open",
			"manualTransaction" => false,
			"groupLineItems" => true,
			"testMode" => false,
			"createdTime" => 1557519030000,
			"clientCreatedTime" => 1557519029000,
			"modifiedTime" => 1557519029000,
			"lineItems" => [
				"elements" => [
					0 => [
						"id" => "S2123MVCCTW7J",
						"orderRef" => [
							"id" => "7BWY5AH2DNP20"
						],
						"item" => [
							"id" => "Z26TW7H5CCZG2"
						],
						"name" => "Pizza",
						"alternateName" => "",
						"price" => 1000,
						"itemCode" => "",
						"printed" => false,
						"createdTime" => 1557519030000,
						"orderClientCreatedTime" => 1557519029000,
						"exchanged" => false,
						"refunded" => false,
						"isRevenue" => true,
					],
					1 => [
						"id" => "GVRDHR8KCAGRW",
						"orderRef" => [
							"id" => "7BWY5AH2DNP20"
						],
						"item" => [
							"id" => "F3BXRCYTJXB3R"
						],
						"name" => "Beer",
						"alternateName" => "",
						"price" => 300,
						"itemCode" => "",
						"printed" => false,
						"createdTime" => 1557519457000,
						"orderClientCreatedTime" => 1557519029000,
						"exchanged" => false,
						"refunded" => false,
						"isRevenue" => true,
					],
					2 => [
						"id" => "WJDZB0JM8NW3P",
						"orderRef" => [
							"id" => "7BWY5AH2DNP20"
						],
						"item" => [
							"id" => "F3BXRCYTJXB3R"
						],
						"name" => "Beer",
						"alternateName" => "",
						"price" => 300,
						"itemCode" => "",
						"printed" => false,
						"createdTime" => 1557519458000,
						"orderClientCreatedTime" => 1557519029000,
						"exchanged" => false,
						"refunded" => false,
						"isRevenue" => true,
					]
				]
			],
			"device" => [
				"id" => "1622c447-b29a-4c47-a625-fd2f6450e175"
			]
		];
	}

	public static function getAddItemsInitial($orderId) {
		return [
			"href" => "https://sandbox.dev.clover.com/v3/merchants/RR9ACXMZ6AFA1/orders/7BWY5AH2DNP20",
			"id" => "$orderId",
			"currency" => "USD",
			"employee" => [
				"id" => "TY6XC27QR7RH4"
			],
			"total" => 1075,
			"taxRemoved" => false,
			"isVat" => false,
			"state" => "open",
			"manualTransaction" => false,
			"groupLineItems" => true,
			"testMode" => false,
			"createdTime" => 1557519030000,
			"clientCreatedTime" => 1557519029000,
			"modifiedTime" => 1557519029000,
			"lineItems" => [
				"elements" => [
					0 => [
						"id" => "S2123MVCCTW7J",
						"orderRef" => [
							"id" => "7BWY5AH2DNP20"
						],
						"item" => [
							"id" => "Z26TW7H5CCZG2"
						],
						"name" => "Pizza",
						"alternateName" => "",
						"price" => 1000,
						"itemCode" => "",
						"printed" => false,
						"createdTime" => 1557519030000,
						"orderClientCreatedTime" => 1557519029000,
						"exchanged" => false,
						"refunded" => false,
						"isRevenue" => true,
					]
				]
			],
			"device" => [
				"id" => "1622c447-b29a-4c47-a625-fd2f6450e175"
			]
		];
	}

	public static function getPartialPaidAllOrder($orderId) {
		return [
			"href" => "https://sandbox.dev.clover.com/v3/merchants/RR9ACXMZ6AFA1/orders/BKT9FWHMFK916",
			"id" => $orderId,
			"currency" => "USD",
			"employee" => [
				"id" => "TY6XC27QR7RH4"
			],
			"total" => 4085,
			"note" => "",
			"taxRemoved" => false,
			"isVat" => false,
			"state" => "locked",
			"manualTransaction" => false,
			"groupLineItems" => true,
			"testMode" => false,
			"payType" => "SPLIT_CUSTOM",
			"createdTime" => 1557434285000,
			"clientCreatedTime" => 1557434285000,
			"modifiedTime" => 1557442455000,
			"lineItems" => [
				"elements" => [
					0 => [
						"id" => "T5R3HS5PQRD9M",
						"orderRef" => [
							"id" => "BKT9FWHMFK916"
						],
						"item" => [
							"id" => "F3BXRCYTJXB3R"
						],
						"name" => "Beer",
						"alternateName" => "",
						"price" => 300,
						"itemCode" => "",
						"printed" => false,
						"createdTime" => 1557434285000,
						"orderClientCreatedTime" => 1557434285000,
						"exchanged" => false,
						"refunded" => false,
						"isRevenue" => true
					],
					1 => [
						"id" => "EDA2XXP90M42A",
						"orderRef" => [
							"id" => "BKT9FWHMFK916"
						],
						"item" => [
							"id" => "Z26TW7H5CCZG2"
						],
						"name" => "Pizza",
						"alternateName" => "",
						"price" => 1000,
						"itemCode" => "",
						"printed" => false,
						"createdTime" => 1557434287000,
						"orderClientCreatedTime" => 1557434285000,
						"exchanged" => false,
						"refunded" => false,
						"isRevenue" => true,
					],
					2 => [
						"id" => "ES8V2PFTKR100",
						"orderRef" => [
							"id" => "BKT9FWHMFK916"
						],
						"item" => [
							"id" => "F3BXRCYTJXB3R"
						],
						"name" => "Beer",
						"alternateName" => "",
						"price" => 300,
						"itemCode" => "",
						"printed" => false,
						"createdTime" => 1557436673000,
						"orderClientCreatedTime" => 1557434285000,
						"exchanged" => false,
						"refunded" => false,
						"isRevenue" => true,
					],
					3 => [
						"id" => "9CEPR9ZPG69CP",
						"orderRef" => [
							"id" => "BKT9FWHMFK916"
						],
						"item" => [
							"id" => "F3BXRCYTJXB3R"
						],
						"name" => "Beer",
						"alternateName" => "",
						"price" => 300,
						"itemCode" => "",
						"printed" => false,
						"createdTime" => 1557436679000,
						"orderClientCreatedTime" => 1557434285000,
						"exchanged" => false,
						"refunded" => false,
						"isRevenue" => true,
					],
					4 => [
						"id" => "A3CADAEZ3QKGY",
						"orderRef" => [
							"id" => "BKT9FWHMFK916"
						],
						"item" => [
							"id" => "F3BXRCYTJXB3R"
						],
						"name" => "Beer",
						"alternateName" => "",
						"price" => 300,
						"itemCode" => "",
						"printed" => false,
						"createdTime" => 1557442417000,
						"orderClientCreatedTime" => 1557434285000,
						"exchanged" => false,
						"refunded" => false,
						"isRevenue" => true,
					],
					5 => [
						"id" => "127NQRS7X5P9Y",
						"orderRef" => [
							"id" => "BKT9FWHMFK916"
						],
						"item" => [
							"id" => "F3BXRCYTJXB3R"
						],
						"name" => "Beer",
						"alternateName" => "",
						"price" => 300,
						"itemCode" => "",
						"printed" => false,
						"createdTime" => 1557442421000,
						"orderClientCreatedTime" => 1557434285000,
						"exchanged" => false,
						"refunded" => false,
						"isRevenue" => true,
					],
					6 => [
						"id" => "XVS8GJF62JKRM",
						"orderRef" => [
							"id" => "BKT9FWHMFK916"
						],
						"item" => [
							"id" => "Z26TW7H5CCZG2"
						],
						"name" => "Pizza",
						"alternateName" => "",
						"price" => 1000,
						"itemCode" => "",
						"printed" => false,
						"createdTime" => 1557442422000,
						"orderClientCreatedTime" => 1557434285000,
						"exchanged" => false,
						"refunded" => false,
						"isRevenue" => true,
					],
					7 => [
						"id" => "JNV1AEN5AJXT6",
						"orderRef" => [
							"id" => "BKT9FWHMFK916"
						],
						"item" => [
							"id" => "F3BXRCYTJXB3R"
						],
						"name" => "Beer",
						"alternateName" => "",
						"price" => 300,
						"itemCode" => "",
						"printed" => false,
						"createdTime" => 1557442424000,
						"orderClientCreatedTime" => 1557434285000,
						"exchanged" => false,
						"refunded" => false,
						"isRevenue" => true,
					]
				]
			],
			"payments" => [
				"elements" => [
					0 => [
						"id" => "KXPAKNSKCRVYE",
						"order" => [
							"id" => "BKT9FWHMFK916"
						],
						"device" => [
							"id" => "1622c447-b29a-4c47-a625-fd2f6450e175"
						],
						"tender" => [
							"href" => "https://sandbox.dev.clover.com/v3/merchants/RR9ACXMZ6AFA1/tenders/N4YEJK89A6E5M",
							"id" => "N4YEJK89A6E5M"
						],
						"amount" => 2043,
						"taxAmount" => 143,
						"cashTendered" => 2043,
						"employee" => [
							"id" => "TY6XC27QR7RH4"
						],
						"createdTime" => 1557442455000,
						"clientCreatedTime" => 1557442452000,
						"modifiedTime" => 1557442455000,
						"result" => "SUCCESS",
					],
					1 => [
						"id" => "44YC0WXQ7MKW2",
						"order" => [
							"id" => "BKT9FWHMFK916"
						],
						"device" => [
							"id" => "1622c447-b29a-4c47-a625-fd2f6450e175"
						],
						"tender" => [
							"href" => "https://sandbox.dev.clover.com/v3/merchants/RR9ACXMZ6AFA1/tenders/N4YEJK89A6E5M",
							"id" => "N4YEJK89A6E5M"
						],
						"amount" => 2042,
						"taxAmount" => 142,
						"cashTendered" => 2042,
						"employee" => [
							"id" => "TY6XC27QR7RH4"
						],
						"createdTime" => 1557513168000,
						"clientCreatedTime" => 1557513167000,
						"modifiedTime" => 1557513168000,
						"result" => "SUCCESS"
					]
				]
			],
			"device" => [
				"id" => "1622c447-b29a-4c47-a625-fd2f6450e175"
			]
		];
	}

	public static function getPartialPaidOrder($orderId) {
		return [
			"href" => "https://sandbox.dev.clover.com/v3/merchants/RR9ACXMZ6AFA1/orders/BKT9FWHMFK916",
			"id" => $orderId,
			"currency" => "USD",
			"employee" => [
				"id" => "TY6XC27QR7RH4"
			],
			"total" => 4085,
			"note" => "",
			"taxRemoved" => false,
			"isVat" => false,
			"state" => "locked",
			"manualTransaction" => false,
			"groupLineItems" => true,
			"testMode" => false,
			"payType" => "SPLIT_CUSTOM",
			"createdTime" => 1557434285000,
			"clientCreatedTime" => 1557434285000,
			"modifiedTime" => 1557442455000,
			"lineItems" => [
				"elements" => [
					0 => [
						"id" => "T5R3HS5PQRD9M",
						"orderRef" => [
							"id" => "BKT9FWHMFK916"
						],
						"item" => [
							"id" => "F3BXRCYTJXB3R"
						],
						"name" => "Beer",
						"alternateName" => "",
						"price" => 300,
						"itemCode" => "",
						"printed" => false,
						"createdTime" => 1557434285000,
						"orderClientCreatedTime" => 1557434285000,
						"exchanged" => false,
						"refunded" => false,
						"isRevenue" => true
					],
					1 => [
						"id" => "EDA2XXP90M42A",
						"orderRef" => [
							"id" => "BKT9FWHMFK916"
						],
						"item" => [
							"id" => "Z26TW7H5CCZG2"
						],
						"name" => "Pizza",
						"alternateName" => "",
						"price" => 1000,
						"itemCode" => "",
						"printed" => false,
						"createdTime" => 1557434287000,
						"orderClientCreatedTime" => 1557434285000,
						"exchanged" => false,
						"refunded" => false,
						"isRevenue" => true,
					],
					2 => [
						"id" => "ES8V2PFTKR100",
						"orderRef" => [
							"id" => "BKT9FWHMFK916"
						],
						"item" => [
							"id" => "F3BXRCYTJXB3R"
						],
						"name" => "Beer",
						"alternateName" => "",
						"price" => 300,
						"itemCode" => "",
						"printed" => false,
						"createdTime" => 1557436673000,
						"orderClientCreatedTime" => 1557434285000,
						"exchanged" => false,
						"refunded" => false,
						"isRevenue" => true,
					],
					3 => [
						"id" => "9CEPR9ZPG69CP",
						"orderRef" => [
							"id" => "BKT9FWHMFK916"
						],
						"item" => [
							"id" => "F3BXRCYTJXB3R"
						],
						"name" => "Beer",
						"alternateName" => "",
						"price" => 300,
						"itemCode" => "",
						"printed" => false,
						"createdTime" => 1557436679000,
						"orderClientCreatedTime" => 1557434285000,
						"exchanged" => false,
						"refunded" => false,
						"isRevenue" => true,
					],
					4 => [
						"id" => "A3CADAEZ3QKGY",
						"orderRef" => [
							"id" => "BKT9FWHMFK916"
						],
						"item" => [
							"id" => "F3BXRCYTJXB3R"
						],
						"name" => "Beer",
						"alternateName" => "",
						"price" => 300,
						"itemCode" => "",
						"printed" => false,
						"createdTime" => 1557442417000,
						"orderClientCreatedTime" => 1557434285000,
						"exchanged" => false,
						"refunded" => false,
						"isRevenue" => true,
					],
					5 => [
						"id" => "127NQRS7X5P9Y",
						"orderRef" => [
							"id" => "BKT9FWHMFK916"
						],
						"item" => [
							"id" => "F3BXRCYTJXB3R"
						],
						"name" => "Beer",
						"alternateName" => "",
						"price" => 300,
						"itemCode" => "",
						"printed" => false,
						"createdTime" => 1557442421000,
						"orderClientCreatedTime" => 1557434285000,
						"exchanged" => false,
						"refunded" => false,
						"isRevenue" => true,
					],
					6 => [
						"id" => "XVS8GJF62JKRM",
						"orderRef" => [
							"id" => "BKT9FWHMFK916"
						],
						"item" => [
							"id" => "Z26TW7H5CCZG2"
						],
						"name" => "Pizza",
						"alternateName" => "",
						"price" => 1000,
						"itemCode" => "",
						"printed" => false,
						"createdTime" => 1557442422000,
						"orderClientCreatedTime" => 1557434285000,
						"exchanged" => false,
						"refunded" => false,
						"isRevenue" => true,
					],
					7 => [
						"id" => "JNV1AEN5AJXT6",
						"orderRef" => [
							"id" => "BKT9FWHMFK916"
						],
						"item" => [
							"id" => "F3BXRCYTJXB3R"
						],
						"name" => "Beer",
						"alternateName" => "",
						"price" => 300,
						"itemCode" => "",
						"printed" => false,
						"createdTime" => 1557442424000,
						"orderClientCreatedTime" => 1557434285000,
						"exchanged" => false,
						"refunded" => false,
						"isRevenue" => true,
					]
				]
			],
			"payments" => [
				"elements" => [
					0 => [
						"id" => "KXPAKNSKCRVYE",
						"order" => [
							"id" => "BKT9FWHMFK916"
						],
						"device" => [
							"id" => "1622c447-b29a-4c47-a625-fd2f6450e175"
						],
						"tender" => [
							"href" => "https://sandbox.dev.clover.com/v3/merchants/RR9ACXMZ6AFA1/tenders/N4YEJK89A6E5M",
							"id" => "N4YEJK89A6E5M"
						],
						"amount" => 2043,
						"taxAmount" => 143,
						"cashTendered" => 2043,
						"employee" => [
							"id" => "TY6XC27QR7RH4"
						],
						"createdTime" => 1557442455000,
						"clientCreatedTime" => 1557442452000,
						"modifiedTime" => 1557442455000,
						"result" => "SUCCESS",
					]
				]
			],
			"device" => [
				"id" => "1622c447-b29a-4c47-a625-fd2f6450e175"
			]
		];
	}

	public static function getNotStoredOrder($orderId) {
		return [
			"href" => "https://sandbox.dev.clover.com/v3/merchants/RR9ACXMZ6AFA1/orders/62X4CVYM3YTKY",
			"id" => $orderId,
			"currency" => "USD",
			"employee" => [
				"id" => "TY6XC27QR7RH4"
			],
			"total" => 2043,
			"taxRemoved" => false,
			"isVat" => false,
			"state" => "open",
			"manualTransaction" => false,
			"groupLineItems" => true,
			"testMode" => false,
			"createdTime" => 1557423651000,
			"clientCreatedTime" => 1557423651000,
			"modifiedTime" => 1557423655000,
			"lineItems" => [
				"elements" => [
					0 => [
						"id" => "5NVEW5Z4A9MM0",
						"orderRef" => [
							"id" => "62X4CVYM3YTKY"
						],
						"item" => [
							"id" => "F3BXRCYTJXB3R"
						],
						"name" => "Beer",
						"alternateName" => "",
						"price" => 300,
						"itemCode" => "",
						"printed" => false,
						"createdTime" => 1557423651000,
						"orderClientCreatedTime" => 1557423651000,
						"exchanged" => false,
						"refunded" => false,
						"isRevenue" => true,
					],
					1 => [
						"id" => "DVY9C98HR12XY",
						"orderRef" => [
							"id" => "62X4CVYM3YTKY"
						],
						"item" => [
							"id" => "F3BXRCYTJXB3R"
						],
						"name" => "Beer",
						"alternateName" => "",
						"price" => 300,
						"itemCode" => "",
						"printed" => false,
						"createdTime" => 1557423652000,
						"orderClientCreatedTime" => 1557423651000,
						"exchanged" => false,
						"refunded" => false,
						"isRevenue" => true,
					],
					2 => [
						"id" => "JTJ22R9Y4ZPHY",
						"orderRef" => [
							"id" => "62X4CVYM3YTKY"
						],
						"item" => [
							"id" => "F3BXRCYTJXB3R"
						],
						"name" => "Beer",
						"alternateName" => "",
						"price" => 300,
						"itemCode" => "",
						"printed" => false,
						"createdTime" => 1557423652000,
						"orderClientCreatedTime" => 1557423651000,
						"exchanged" => false,
						"refunded" => false,
						"isRevenue" => true,
					],
					3 => [
						"id" => "8GS5JQB2Q6H7J",
						"orderRef" => [
							"id" => "62X4CVYM3YTKY"
						],
						"item" => [
							"id" => "Z26TW7H5CCZG2"
						],
						"name" => "Pizza",
						"alternateName" => "",
						"price" => 1000,
						"itemCode" => "",
						"printed" => false,
						"createdTime" => 1557423655000,
						"orderClientCreatedTime" => 1557423651000,
						"exchanged" => false,
						"refunded" => false,
						"isRevenue" => true
					]
				]
			],
			"device" => [
				"id" => "1622c447-b29a-4c47-a625-fd2f6450e175"
			]
		];
	}

	public static function fakeEmployeeFetch($employeeId) {
		$faker = Faker\Factory::create();
		return json_encode([
			'id' => $employeeId,
			'name' => $faker->boolean ? $faker->firstName . ' ' . $faker->lastName : $faker->lastName,
			'nickname' => $faker->firstName,
			'email' => $faker->email
		]);
	}

	private static function getPaidOrder($orderId) {
		return [
			"href" => "https://sandbox.dev.clover.com/v3/merchants/RR9ACXMZ6AFA1/orders/GFAFP15WAMZCG",
			"id" => $orderId,
			"currency" =>"USD",
			"employee" => [
				"id" =>"TY6XC27QR7RH4"
			],
			"total" =>2150,
			"taxRemoved" =>false,
			"isVat" =>false,
			"state" =>"locked",
			"manualTransaction" =>false,
			"groupLineItems" =>true,
			"testMode" =>false,
			"payType" =>"FULL",
			"createdTime" =>1557423669000,
			"clientCreatedTime" =>1557423668000,
			"modifiedTime" =>1557423698000,
			"lineItems" => [
				"elements" => [
					0 => [
						"id" => "GXK97VGV4BQS6",
						"orderRef" => [
							"id" => "GFAFP15WAMZCG"
						],
						"item" => [
							"id" => "Z26TW7H5CCZG2"
						],
						"name" => "Pizza",
						"alternateName" => "",
						"price" => 1000,
						"itemCode" => "",
						"printed" => false,
						"createdTime" => 1557423669000,
						"orderClientCreatedTime" => 1557423668000,
						"exchanged" => false,
						"refunded" => false,
						"isRevenue" => true,
					],
					1 => [
						"id" => "E1WZKSAMSAZGE",
						"orderRef" => [
							"id" => "GFAFP15WAMZCG"
						],
						"item" => [
							"id" => "Z26TW7H5CCZG2"
						],
						"name" => "Pizza",
						"alternateName" => "",
						"price" => 1000,
						"itemCode" => "",
						"printed" => false,
						"createdTime" => 1557423674000,
						"orderClientCreatedTime" => 1557423668000,
						"exchanged" => false,
						"refunded" => false,
						"isRevenue" => true,
					]
				]
			],
			"payments" => [
				"elements" =>[
					0 => [
						"id" =>"N418C6R2KHKEE",
						"order" => [
							"id" => $orderId
						],
						"device" => [
							"id" =>"1622c447-b29a-4c47-a625-fd2f6450e175"
						],
						"tender" => [
							"href" =>"https://sandbox.dev.clover.com/v3/merchants/RR9ACXMZ6AFA1/tenders/N4YEJK89A6E5M",
							"id" => "N4YEJK89A6E5M"
						],
						"amount" => 2150,
						"taxAmount" => 150,
						"cashTendered" => 2150,
						"employee" => [
							"id" => "TY6XC27QR7RH4"
						],
						"createdTime" => 1557423698000,
						"clientCreatedTime" => 1557423698000,
						"modifiedTime" => 1557423698000,
						"result" => "SUCCESS"
					]
				]
			],
			"device" => [
				"id" => "1622c447-b29a-4c47-a625-fd2f6450e175"
			]
		];
	}
}