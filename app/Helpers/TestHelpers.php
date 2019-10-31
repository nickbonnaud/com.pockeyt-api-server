<?php

namespace App\Helpers;

use \Faker as Faker;
use App\Models\Location\Region;
use App\Models\Business\ActiveItem;

class TestHelpers {

	public static function fakeGeoCodeResponse() {
		return json_encode(array(
      "results" => [
        [
           "address_components" => [
              [
                 "long_name" => "1600",
                 "short_name" => "1600",
                 "types" => [ "street_number" ]
              ],
              [
                 "long_name" => "Amphitheatre Pkwy",
                 "short_name" => "Amphitheatre Pkwy",
                 "types" => [ "route" ]
              ],
              [
                 "long_name" => "Mountain View",
                 "short_name" => "Mountain View",
                 "types" => [ "locality", "political" ]
              ],
              [
                 "long_name" => "Santa Clara County",
                 "short_name" => "Santa Clara County",
                 "types" => [ "administrative_area_level_2", "political" ]
              ],
              [
                 "long_name" => "California",
                 "short_name" => "CA",
                 "types" => [ "administrative_area_level_1", "political" ]
              ],
              [
                 "long_name" => "United States",
                 "short_name" => "US",
                 "types" => [ "country", "political" ]
              ],
              [
                 "long_name" => "94043",
                 "short_name" => "94043",
                 "types" => [ "postal_code" ]
              ]
           ],
           "formatted_address" => "1600 Amphitheatre Parkway, Mountain View, CA 94043, USA",
           "geometry" => [
              "location" => [
                 "lat" => factory(\App\Models\Business\GeoAccount::class)->make()->lat,
                 "lng" => factory(\App\Models\Business\GeoAccount::class)->make()->lng
              ],
              "location_type" => "ROOFTOP",
              "viewport" => [
                 "northeast" => [
                    "lat" => 37.4238253802915,
                    "lng" => -122.0829009197085
                 ],
                 "southwest" => [
                    "lat" => 37.4211274197085,
                    "lng" => -122.0855988802915
                 ]
              ]
           ],
           "place_id" => "ChIJ2eUgeAK6j4ARbn5u_wAGqWA",
           "types" => [ "street_address" ]
        ]
     ],
     'status' => 'OK'
    ));
	}

  public static function fakeRegionClosest($coords) {
    $regions = Region::all();
    $regionsArray = [];
    foreach ($regions as $region) {
      array_push($regionsArray, (object)['id' => $region->id, 'distance' => self::getDistance($coords, $region)]);
    }
    $regionRow = collect($regionsArray)->sortBy('distance')->where('distance', '<', 20)->first();
    $region = $regionRow ? Region::where('id', $regionRow->id)->first() : null;
    return $region;
  }

  public static function fakeSquareAuthResponse() {
    return json_encode(array(
      "access_token" => "not_token",
      "token_type" => "bearer",
      "expires_at" => "2006-01-02T15:04:05Z",
      "merchant_id" => "not_merchant",
      "refresh_token" => "REFRESH_TOKEN"
    ));
  }

  public static function fakeSquareLocationResponseSingle() {
    return json_encode(array(
      "locations" => [
        [
          "id" => "18YC4JDH91E1H",
          "name" => "your location name",
          "address" => [
            "address_line_1" => "123 Main St",
            "locality" => "San Francisco",
            "administrative_district_level_1" => "CA",
            "postal_code" => "94114",
            "country" => "US"
          ],
          "timezone" => "America/Los_Angeles",
          "capabilities" => [
            "CREDIT_CARD_PROCESSING"
          ],
          "status" => "ACTIVE",
          "created_at" => "2016-09-19T17:33:12Z",
          "merchant_id" => "3MYCJG5GVYQ8Q",
          "country" => "US",
          "language_code" => "en-US",
          "currency" => "USD",
          "phone_number" => "+1 650-354-7217",
          "business_name" => "Pumbaa's business name"
        ]
      ]
    ));
  }

  public static function fakeSquareLocationResponseDouble() {
    return json_encode(array(
      "locations" => [
        [
          "id" => "18YC4JDH91E1H",
          "name" => "your location name",
          "address" => [
            "address_line_1" => "123 Main St",
            "locality" => "San Francisco",
            "administrative_district_level_1" => "CA",
            "postal_code" => "94114",
            "country" => "US"
          ],
          "timezone" => "America/Los_Angeles",
          "capabilities" => [
            "CREDIT_CARD_PROCESSING"
          ],
          "status" => "ACTIVE",
          "created_at" => "2016-09-19T17:33:12Z",
          "merchant_id" => "3MYCJG5GVYQ8Q",
          "country" => "US",
          "language_code" => "en-US",
          "currency" => "USD",
          "phone_number" => "+1 650-354-7217",
          "business_name" => "Pumbaa's business name"
        ],
        [
          "id" => "VVNJRU84HIG9BBV",
          "name" => "your location name",
          "address" => [
            "address_line_1" => "321 Broad St",
            "locality" => "San Francisco",
            "administrative_district_level_1" => "CA",
            "postal_code" => "94114",
            "country" => "US"
          ],
          "timezone" => "America/Los_Angeles",
          "capabilities" => [
            "CREDIT_CARD_PROCESSING"
          ],
          "status" => "ACTIVE",
          "created_at" => "2016-09-19T17:33:12Z",
          "merchant_id" => "3MYCJG5GVYQ8Q",
          "country" => "US",
          "language_code" => "en-US",
          "currency" => "USD",
          "phone_number" => "+1 650-354-7217",
          "business_name" => "Pumbaa's business name"
        ]
      ]
    ));
  }

  public static function fakeSquareInventoryResponse() {
    $faker = Faker\Factory::create();
    $numItems = $faker->numberBetween($min = 1, $max = 10);
    return json_encode(
      self::createItemsArray($numItems)
    );
  }

  public static function paginateHeader() {
    return count(ActiveItem::all()) > 0 ? null : "<https://connect.squareup.com/v1/MERCHANT_ID/payments?batch_token=BATCH_TOKEN>;rel='next'";
  }

  private static function createItemsArray($numItems) {
    $faker = Faker\Factory::create();
    $items = [];
    $i = 0;
    while ($i < $numItems) {
      $item = [
        'id' => $itemId = $faker->bothify('s##??###?##????#?'),
        'name' => $faker->word,
        'description' => $faker->sentence($nbWords = 6, $variableNbWords = true),
        'type' => 'NORMAL',
        'color' => '9da2a6',
        'abbreviation' => $faker->lexify('?????'),
        'visibility' => 'PUBLIC',
        'available_online' => true,
        'category' => [
          'id' => $categoryId = $faker->bothify('s##??###?##????#?'),
          'name' => $faker->word
        ],
        'variations' => self::createVariationsArray($faker->numberBetween($min = 1, $max = 10), $itemId),
        'taxable' => true,
        'category_id' => $categoryId,
        'available_for_pickup' => false,
        'v2_id' => $faker->bothify('s##??###?##????#?')
      ];
      array_push($items, $item);
      $i++;
    }
    return $items;
  }

  private static function createVariationsArray($numVariations, $itemId) {
    $faker = Faker\Factory::create();
    $variations = [];
    $i = 0;
    while ($i < $numVariations) {
      $variation = [
        'id' => $faker->bothify('s##??###?##????#?'),
        'inventory_alert_threshold' => 10,
        'inventory_alert_type' => 'LOW_QUANTITY',
        'item_id' => $itemId,
        'name' => $faker->word,
        'price_money' => [
          'amount' => ($faker->numberBetween($min = 1, $max = 100)) * 100,
          'currency_code' => 'USD'
        ],
        'pricing_type' => 'FIXED_PRICING',
        'track_inventory' => true,
        'v2_id' => $faker->bothify('s##??###?##????#?')
      ];
      array_push($variations, $variation);
      $i++;
    }
    return $variations;
  }

  public static function fakeSquarePaymentFetch() {
    $payment = [
        "id" => "FA1HYbBCfyDXLc4dLIVCLQB",
        "merchant_id" => "AJF0JT16KR0AS",
        "creator_id" => "AJF0JT16KR0AS",
        "created_at" => "2019-04-26T13:11:54Z",
        "device" => [
            "id" => "DEVICE_INSTALLATION_ID:99B46D55-AF41-48B1-A402-F80F07B4AC4F",
            "name" => "Nick"
        ],
        "payment_url" => "https://squareup.com/dashboard/sales/transactions/35VFtehlEmjj3DTZUzo86d8eV",
        "receipt_url" => "https://squareup.com/receipt/preview/FA1HYbBCfyDXLc4dLIVCLQB",
        "inclusive_tax_money" => [
            "amount" => 0,
            "currency_code" => "USD"
        ],
        "additive_tax_money" => [
            "amount" => 38,
            "currency_code" => "USD"
        ],
        "tax_money" => [
            "amount" => 38,
            "currency_code" => "USD"
        ],
        "tip_money" => [
            "amount" => 0,
            "currency_code" => "USD"
        ],
        "discount_money" => [
            "amount" => 0,
            "currency_code" => "USD"
        ],
        "total_collected_money" => [
            "amount" => 1438,
            "currency_code" => "USD"
        ],
        "processing_fee_money" => [
            "amount" => 0,
            "currency_code" => "USD"
        ],
        "net_total_money" => [
            "amount" => 1438,
            "currency_code" => "USD"
        ],
        "refunded_money" => [
            "amount" => 0,
            "currency_code" => "USD"
        ],
        "swedish_rounding_money" => [
            "amount" => 0,
            "currency_code" => "USD"
        ],
        "gross_sales_money" => [
            "amount" => 1400,
            "currency_code" => "USD"
        ],
        "net_sales_money" => [
            "amount" => 1400,
            "currency_code" => "USD"
        ],
        "surcharge_money" => [
            "amount" => 0,
            "currency_code" => "USD"
        ],
        "surcharges" => [],
        "inclusive_tax" => [],
        "additive_tax" => [
            [
                "name" => "Sales Tax",
                "rate" => "0.02750000",
                "inclusion_type" => "ADDITIVE",
                "applied_money" => [
                    "amount" => 38,
                    "currency_code" => "USD"
                ],
                "fee_id" => "D7A79E01-5E95-4E02-BE04-C3C5B67EE301"
            ]
        ],
        "tender" => [
            [
                "type" => "OTHER",
                "employee_id" => 'fake_id',
                "payment_note" => "Heist",
                "name" => "CUSTOM",
                "id" => "FA1HYbBCfyDXLc4dLIVCLQB",
                "total_money" => [
                    "amount" => 1438,
                    "currency_code" => "USD"
                ],
                "refunded_money" => [
                    "amount" => 0,
                    "currency_code" => "USD"
                ],
                "receipt_url" => "https://squareup.com/receipt/preview/FA1HYbBCfyDXLc4dLIVCLQB",
                "is_exchange" => false,
                "tendered_at" => "2019-04-26T13:11:54Z",
                "settled_at" => "2019-04-26T13:11:55Z"
            ]
        ],
        "refunds" => [],
        "itemizations" => [
            [
                "name" => "Popcorn",
                "quantity" => "1.00000000",
                "item_variation_name" => "Regular",
                "item_detail" => [
                    "category_name" => "",
                    "sku" => "",
                    "item_id" => "8734BBB7-8894-4BCD-8F03-ACD85F601701",
                    "item_variation_id" => "0DAAADC3-D830-47F0-82C1-43FF02B120D6"
                ],
                "itemization_type" => "ITEM",
                "total_money" => [
                    "amount" => 924,
                    "currency_code" => "USD"
                ],
                "single_quantity_money" => [
                    "amount" => 900,
                    "currency_code" => "USD"
                ],
                "gross_sales_money" => [
                    "amount" => 900,
                    "currency_code" => "USD"
                ],
                "discount_money" => [
                    "amount" => 0,
                    "currency_code" => "USD"
                ],
                "net_sales_money" => [
                    "amount" => 900,
                    "currency_code" => "USD"
                ],
                "taxes" => [
                    [
                        "name" => "Sales Tax",
                        "rate" => "0.02750000",
                        "inclusion_type" => "ADDITIVE",
                        "applied_money" => [
                            "amount" => 24,
                            "currency_code" => "USD"
                        ],
                        "fee_id" => "D7A79E01-5E95-4E02-BE04-C3C5B67EE301"
                    ]
                ],
                "discounts" => [],
                "modifiers" => []
            ],
            [
                "name" => "Soda",
                "quantity" => "1.00000000",
                "item_variation_name" => "Coke",
                "item_detail" => [
                    "category_name" => "",
                    "sku" => "",
                    "item_id" => "921012C9-82C0-40A0-865E-45D97E26772B",
                    "item_variation_id" => "218C53AC-649D-4788-B88F-B9DCEB062B1D"
                ],
                "itemization_type" => "ITEM",
                "total_money" => [
                    "amount" => 514,
                    "currency_code" => "USD"
                ],
                "single_quantity_money" => [
                    "amount" => 500,
                    "currency_code" => "USD"
                ],
                "gross_sales_money" => [
                    "amount" => 500,
                    "currency_code" => "USD"
                ],
                "discount_money" => [
                    "amount" => 0,
                    "currency_code" => "USD"
                ],
                "net_sales_money" => [
                    "amount" => 500,
                    "currency_code" => "USD"
                ],
                "taxes" => [
                    [
                        "name" => "Sales Tax",
                        "rate" => "0.02750000",
                        "inclusion_type" => "ADDITIVE",
                        "applied_money" => [
                            "amount" => 14,
                            "currency_code" => "USD"
                        ],
                        "fee_id" => "D7A79E01-5E95-4E02-BE04-C3C5B67EE301"
                    ]
                ],
                "discounts" => [],
                "modifiers" => []
            ]
        ],
        "extensions" => [
            "metadata" => []
        ]
    ];
    return json_encode($payment);
  }

  public static function fakeSquareTransactionFetch() {
    $transaction = [
      "transaction" => [
          "id" => "rX9mgUqnAuZ4NOsL4WvBfsxeV",
          "location_id" => "AJF0JT16KR0AS",
          "created_at" => "2019-04-25T20:06:55Z",
          "tenders" => [
              [
                  "id" => "94NuDkHmtK9sI7IGb9EOLQB",
                  "location_id" => "AJF0JT16KR0AS",
                  "transaction_id" => "rX9mgUqnAuZ4NOsL4WvBfsxeV",
                  "created_at" => "2019-04-25T20:06:55Z",
                  "amount_money" => [
                      "amount" => 1000,
                      "currency" => "USD"
                  ],
                  "processing_fee_money" => [
                      "amount" => 0,
                      "currency" => "USD"
                  ],
                  "customer_id" => "3VS7KBCQBX601683QZWXPY7W2C",
                  "type" => "OTHER"
              ]
          ],
          "product" => "REGISTER",
          "client_id" => "3E4627F7-D5F9-4C8C-B645-CB8CA892D364"
      ]
    ];
    return json_encode($transaction);
  }

  public static function fakeSquareCustomerFetch() {
    $customer = [
      "customer" => [
          "id" => "3VS7KBCQBX601683QZWXPY7W2C",
          "created_at" => "2019-04-25T20:01:46.604Z",
          "updated_at" => "2019-04-25T20:37:12Z",
          "given_name" => "Test",
          "family_name" => "Customer",
          "email_address" => "test.customer@gmail.com",
          "reference_id" => "heist_b8aa8380-6799-11e9-b2da-0fc14cd3f31e",
          "preferences" => [
              "email_unsubscribed" => false
          ],
          "groups" => [
              [
                  "id" => "79N6C3W3C8F22.REACHABLE",
                  "name" => "Reachable"
              ]
          ],
          "creation_source" => "DIRECTORY"
      ]
    ];
    return json_encode($customer);
  }

  public static function fakePush() {
    return json_decode(json_encode(
      [
        "success" => true,
        "error" => "
           Client error: `POST https://fcm.googleapis.com/fcm/send` resulted in a `401 Invalid (legacy) Server-key delivered or Sender is not authorized to perform request.` response:\n
            <HTML>\n
            <HEAD>\n
            <TITLE>Invalid (legacy) Server-key delivered or Sender is not authorized to perform request.</TITLE>\n
            </HEA (truncated...)\n
            "
      ]
    ));
  }

  public static function fakeSquareCreateCustomer($body) {
    return json_encode([
      'customer' => [
        'id' => 'JDKYHBWT1D4F8MFH63DBMEN8Y4',
        "given_name" => $body['given_name'],
        "family_name" => $body['family_name'],
        "email_address" => $body['email_address'],
        "reference_id" => $body['reference_id'],
        "note" => $body['note']
      ]
    ]);
  }

  public static function fakeSquareEmployeeFetch($employeeId) {
    return json_encode([
      "employee" => [
          "id" => $employeeId,
          "first_name" => "Jane",
          "last_name" => "Smith",
          "location_ids" => ["LOCATION_ID"],
          "status" => "ACTIVE",
          "created_at" => "2019-02-20T01:28:49Z",
      ]
    ]);
  }

  private static function getDistance($coords, $region) {
    $r = 6371;
    $dLat = self::deg2rad(($coords['lat']) - ($region['center_lat']));
    $dLon = self::deg2rad(($coords['lng']) - ($region['center_lng'])); 
    $a = 
      sin($dLat/2) * sin($dLat/2) +
      cos(deg2rad($region['center_lat'])) * cos(deg2rad($coords['lat'])) * 
      sin($dLon/2) * sin($dLon/2)
      ; 
    $c = 2 * atan2(sqrt($a), sqrt(1-$a)); 
    $d = $r * $c;
    return $d;
  }

  private static function deg2rad($deg) {
    return $deg * (M_PI/180);
  }
}
