<?php

namespace App\Helpers;

use \Faker as Faker;

class ShopifyTestHelpers {

	public static function fakeAccessTokenResponse() {
		return json_encode([
			'access_token' => 'not_token',
			"scope" => "write_orders,read_customers"
		]);
	}

	public static function fakeCreateWebHook() {
		return json_encode([
			'webhook' => [
				"id" => 1047897713,
		    "address" => "https://whatever.hostname.com/",
		    "topic" => "orders/create",
		    "created_at" => "2019-06-04T13:55:06-04:00",
		    "updated_at" => "2019-06-04T13:55:06-04:00",
		    "format" => "json",
		    "fields" => [],
		    "metafield_namespaces" => [],
		    "api_version" => "2019-04"
			]
		]);
	}

	public static function fakeReceiveWebhook() {
		return array(
		  'id' => 988524576817,
		  'email' => NULL,
		  'closed_at' => '2019-07-03T16:11:02-04:00',
		  'created_at' => '2019-07-03T16:11:00-04:00',
		  'updated_at' => '2019-07-03T16:11:02-04:00',
		  'number' => 3,
		  'note' => NULL,
		  'token' => 'b2e0a344aac75c51c5e70b396e2bde88',
		  'gateway' => 'Pockeyt Pay',
		  'test' => false,
		  'total_price' => '17.20',
		  'subtotal_price' => '16.00',
		  'total_weight' => 0,
		  'total_tax' => '1.20',
		  'taxes_included' => false,
		  'currency' => 'USD',
		  'financial_status' => 'paid',
		  'confirmed' => true,
		  'total_discounts' => '0.00',
		  'total_line_items_price' => '16.00',
		  'cart_token' => NULL,
		  'buyer_accepts_marketing' => false,
		  'name' => '#1003',
		  'referring_site' => NULL,
		  'landing_site' => '/admin/checkouts.json',
		  'cancelled_at' => NULL,
		  'cancel_reason' => NULL,
		  'total_price_usd' => '17.20',
		  'checkout_token' => '694ce512998748cc90c993c8c3aa5962',
		  'reference' => NULL,
		  'user_id' => 29169287217,
		  'location_id' => 17277419569,
		  'source_identifier' => '17277419569-1-1003',
		  'source_url' => NULL,
		  'processed_at' => '2019-07-03T16:11:00-04:00',
		  'device_id' => 1,
		  'phone' => NULL,
		  'customer_locale' => 'en',
		  'app_id' => 129785,
		  'browser_ip' => '45.37.97.85',
		  'landing_site_ref' => NULL,
		  'order_number' => 1003,
		  'discount_applications' => 
		  array (
		  ),
		  'discount_codes' => 
		  array (
		  ),
		  'note_attributes' => 
		  array (
		    0 => 
		    array (
		      'name' => strtolower(env('BUSINESS_NAME')) . '_id',
		      'value' => '123e4567-e89b-12d3-a456-426655440000',
		    ),
		  ),
		  'payment_gateway_names' => 
		  array (
		    0 => 'Pockeyt Pay',
		  ),
		  'processing_method' => 'Pockeyt Pay',
		  'checkout_id' => 9345427800113,
		  'source_name' => 'pos',
		  'fulfillment_status' => 'fulfilled',
		  'tax_lines' => 
		  array (
		    0 => 
		    array (
		      'price' => '0.76',
		      'rate' => 0.0475,
		      'title' => 'NC State Tax',
		      'price_set' => 
		      array (
		        'shop_money' => 
		        array (
		          'amount' => '0.76',
		          'currency_code' => 'USD',
		        ),
		        'presentment_money' => 
		        array (
		          'amount' => '0.76',
		          'currency_code' => 'USD',
		        ),
		      ),
		    ),
		    1 => 
		    array (
		      'price' => '0.44',
		      'rate' => 0.0275,
		      'title' => 'Orange County Tax',
		      'price_set' => 
		      array (
		        'shop_money' => 
		        array (
		          'amount' => '0.44',
		          'currency_code' => 'USD',
		        ),
		        'presentment_money' => 
		        array (
		          'amount' => '0.44',
		          'currency_code' => 'USD',
		        ),
		      ),
		    ),
		  ),
		  'tags' => NULL,
		  'contact_email' => NULL,
		  'order_status_url' => 'https://pockeyt-test.myshopify.com/3956015153/orders/b2e0a344aac75c51c5e70b396e2bde88/authenticate?key=7de9158b9376192b0fb88076b9afb8ab',
		  'presentment_currency' => 'USD',
		  'total_line_items_price_set' => 
		  array (
		    'shop_money' => 
		    array (
		      'amount' => '16.00',
		      'currency_code' => 'USD',
		    ),
		    'presentment_money' => 
		    array (
		      'amount' => '16.00',
		      'currency_code' => 'USD',
		    ),
		  ),
		  'total_discounts_set' => 
		  array (
		    'shop_money' => 
		    array (
		      'amount' => '0.00',
		      'currency_code' => 'USD',
		    ),
		    'presentment_money' => 
		    array (
		      'amount' => '0.00',
		      'currency_code' => 'USD',
		    ),
		  ),
		  'total_shipping_price_set' => 
		  array (
		    'shop_money' => 
		    array (
		      'amount' => '0.00',
		      'currency_code' => 'USD',
		    ),
		    'presentment_money' => 
		    array (
		      'amount' => '0.00',
		      'currency_code' => 'USD',
		    ),
		  ),
		  'subtotal_price_set' => 
		  array (
		    'shop_money' => 
		    array (
		      'amount' => '16.00',
		      'currency_code' => 'USD',
		    ),
		    'presentment_money' => 
		    array (
		      'amount' => '16.00',
		      'currency_code' => 'USD',
		    ),
		  ),
		  'total_price_set' => 
		  array (
		    'shop_money' => 
		    array (
		      'amount' => '17.20',
		      'currency_code' => 'USD',
		    ),
		    'presentment_money' => 
		    array (
		      'amount' => '17.20',
		      'currency_code' => 'USD',
		    ),
		  ),
		  'total_tax_set' => 
		  array (
		    'shop_money' => 
		    array (
		      'amount' => '1.20',
		      'currency_code' => 'USD',
		    ),
		    'presentment_money' => 
		    array (
		      'amount' => '1.20',
		      'currency_code' => 'USD',
		    ),
		  ),
		  'total_tip_received' => '0.0',
		  'line_items' => 
		  array (
		    0 => 
		    array (
		      'id' => 2109186834481,
		      'variant_id' => 15700650098737,
		      'title' => 'Beer',
		      'quantity' => 2,
		      'sku' => NULL,
		      'variant_title' => NULL,
		      'vendor' => 'pockeyt-test',
		      'fulfillment_service' => 'manual',
		      'product_id' => 2033901568049,
		      'requires_shipping' => true,
		      'taxable' => true,
		      'gift_card' => false,
		      'name' => 'Beer',
		      'variant_inventory_management' => NULL,
		      'properties' => 
		      array (
		      ),
		      'product_exists' => true,
		      'fulfillable_quantity' => 0,
		      'grams' => 0,
		      'price' => '3.00',
		      'total_discount' => '0.00',
		      'fulfillment_status' => 'fulfilled',
		      'price_set' => 
		      array (
		        'shop_money' => 
		        array (
		          'amount' => '3.00',
		          'currency_code' => 'USD',
		        ),
		        'presentment_money' => 
		        array (
		          'amount' => '3.00',
		          'currency_code' => 'USD',
		        ),
		      ),
		      'total_discount_set' => 
		      array (
		        'shop_money' => 
		        array (
		          'amount' => '0.00',
		          'currency_code' => 'USD',
		        ),
		        'presentment_money' => 
		        array (
		          'amount' => '0.00',
		          'currency_code' => 'USD',
		        ),
		      ),
		      'discount_allocations' => 
		      array (
		      ),
		      'tax_lines' => 
		      array (
		        0 => 
		        array (
		          'title' => 'NC State Tax',
		          'price' => '0.28',
		          'rate' => 0.0475,
		          'price_set' => 
		          array (
		            'shop_money' => 
		            array (
		              'amount' => '0.28',
		              'currency_code' => 'USD',
		            ),
		            'presentment_money' => 
		            array (
		              'amount' => '0.28',
		              'currency_code' => 'USD',
		            ),
		          ),
		        ),
		        1 => 
		        array (
		          'title' => 'Orange County Tax',
		          'price' => '0.16',
		          'rate' => 0.0275,
		          'price_set' => 
		          array (
		            'shop_money' => 
		            array (
		              'amount' => '0.16',
		              'currency_code' => 'USD',
		            ),
		            'presentment_money' => 
		            array (
		              'amount' => '0.16',
		              'currency_code' => 'USD',
		            ),
		          ),
		        ),
		      ),
		      'origin_location' => 
		      array (
		        'id' => 971936366641,
		        'country_code' => 'US',
		        'province_code' => 'NC',
		        'name' => '220 Elizabeth Street',
		        'address1' => '220 Elizabeth Street',
		        'address2' => NULL,
		        'city' => 'Chapel Hill',
		        'zip' => '27514',
		      ),
		    ),
		    1 => 
		    array (
		      'id' => 2109186867249,
		      'variant_id' => 15804249833521,
		      'title' => 'Pizza',
		      'quantity' => 1,
		      'sku' => NULL,
		      'variant_title' => NULL,
		      'vendor' => 'pockeyt-test',
		      'fulfillment_service' => 'manual',
		      'product_id' => 2055142080561,
		      'requires_shipping' => true,
		      'taxable' => true,
		      'gift_card' => false,
		      'name' => 'Pizza',
		      'variant_inventory_management' => NULL,
		      'properties' => 
		      array (
		      ),
		      'product_exists' => true,
		      'fulfillable_quantity' => 0,
		      'grams' => 0,
		      'price' => '10.00',
		      'total_discount' => '0.00',
		      'fulfillment_status' => 'fulfilled',
		      'price_set' => 
		      array (
		        'shop_money' => 
		        array (
		          'amount' => '10.00',
		          'currency_code' => 'USD',
		        ),
		        'presentment_money' => 
		        array (
		          'amount' => '10.00',
		          'currency_code' => 'USD',
		        ),
		      ),
		      'total_discount_set' => 
		      array (
		        'shop_money' => 
		        array (
		          'amount' => '0.00',
		          'currency_code' => 'USD',
		        ),
		        'presentment_money' => 
		        array (
		          'amount' => '0.00',
		          'currency_code' => 'USD',
		        ),
		      ),
		      'discount_allocations' => 
		      array (
		      ),
		      'tax_lines' => 
		      array (
		        0 => 
		        array (
		          'title' => 'NC State Tax',
		          'price' => '0.48',
		          'rate' => 0.0475,
		          'price_set' => 
		          array (
		            'shop_money' => 
		            array (
		              'amount' => '0.48',
		              'currency_code' => 'USD',
		            ),
		            'presentment_money' => 
		            array (
		              'amount' => '0.48',
		              'currency_code' => 'USD',
		            ),
		          ),
		        ),
		        1 => 
		        array (
		          'title' => 'Orange County Tax',
		          'price' => '0.28',
		          'rate' => 0.0275,
		          'price_set' => 
		          array (
		            'shop_money' => 
		            array (
		              'amount' => '0.28',
		              'currency_code' => 'USD',
		            ),
		            'presentment_money' => 
		            array (
		              'amount' => '0.28',
		              'currency_code' => 'USD',
		            ),
		          ),
		        ),
		      ),
		      'origin_location' => 
		      array (
		        'id' => 971936366641,
		        'country_code' => 'US',
		        'province_code' => 'NC',
		        'name' => '220 Elizabeth Street',
		        'address1' => '220 Elizabeth Street',
		        'address2' => NULL,
		        'city' => 'Chapel Hill',
		        'zip' => '27514',
		      ),
		    ),
		  ),
		  'shipping_lines' => 
		  array (
		  ),
		  'fulfillments' => 
		  array (
		    0 => 
		    array (
		      'id' => 917525397553,
		      'order_id' => 988524576817,
		      'status' => 'success',
		      'created_at' => '2019-07-03T16:11:01-04:00',
		      'service' => 'manual',
		      'updated_at' => '2019-07-03T16:11:01-04:00',
		      'tracking_company' => NULL,
		      'shipment_status' => NULL,
		      'location_id' => 17277419569,
		      'tracking_number' => NULL,
		      'tracking_numbers' => 
		      array (
		      ),
		      'tracking_url' => NULL,
		      'tracking_urls' => 
		      array (
		      ),
		      'receipt' => 
		      array (
		      ),
		      'name' => '#1003.1',
		      'line_items' => 
		      array (
		        0 => 
		        array (
		          'id' => 2109186834481,
		          'variant_id' => 15700650098737,
		          'title' => 'Beer',
		          'quantity' => 2,
		          'sku' => NULL,
		          'variant_title' => NULL,
		          'vendor' => 'pockeyt-test',
		          'fulfillment_service' => 'manual',
		          'product_id' => 2033901568049,
		          'requires_shipping' => true,
		          'taxable' => true,
		          'gift_card' => false,
		          'name' => 'Beer',
		          'variant_inventory_management' => NULL,
		          'properties' => 
		          array (
		          ),
		          'product_exists' => true,
		          'fulfillable_quantity' => 0,
		          'grams' => 0,
		          'price' => '3.00',
		          'total_discount' => '0.00',
		          'fulfillment_status' => 'fulfilled',
		          'price_set' => 
		          array (
		            'shop_money' => 
		            array (
		              'amount' => '3.00',
		              'currency_code' => 'USD',
		            ),
		            'presentment_money' => 
		            array (
		              'amount' => '3.00',
		              'currency_code' => 'USD',
		            ),
		          ),
		          'total_discount_set' => 
		          array (
		            'shop_money' => 
		            array (
		              'amount' => '0.00',
		              'currency_code' => 'USD',
		            ),
		            'presentment_money' => 
		            array (
		              'amount' => '0.00',
		              'currency_code' => 'USD',
		            ),
		          ),
		          'discount_allocations' => 
		          array (
		          ),
		          'tax_lines' => 
		          array (
		            0 => 
		            array (
		              'title' => 'NC State Tax',
		              'price' => '0.28',
		              'rate' => 0.0475,
		              'price_set' => 
		              array (
		                'shop_money' => 
		                array (
		                  'amount' => '0.28',
		                  'currency_code' => 'USD',
		                ),
		                'presentment_money' => 
		                array (
		                  'amount' => '0.28',
		                  'currency_code' => 'USD',
		                ),
		              ),
		            ),
		            1 => 
		            array (
		              'title' => 'Orange County Tax',
		              'price' => '0.16',
		              'rate' => 0.0275,
		              'price_set' => 
		              array (
		                'shop_money' => 
		                array (
		                  'amount' => '0.16',
		                  'currency_code' => 'USD',
		                ),
		                'presentment_money' => 
		                array (
		                  'amount' => '0.16',
		                  'currency_code' => 'USD',
		                ),
		              ),
		            ),
		          ),
		          'origin_location' => 
		          array (
		            'id' => 971936366641,
		            'country_code' => 'US',
		            'province_code' => 'NC',
		            'name' => '220 Elizabeth Street',
		            'address1' => '220 Elizabeth Street',
		            'address2' => NULL,
		            'city' => 'Chapel Hill',
		            'zip' => '27514',
		          ),
		        ),
		        1 => 
		        array (
		          'id' => 2109186867249,
		          'variant_id' => 15804249833521,
		          'title' => 'Pizza',
		          'quantity' => 1,
		          'sku' => NULL,
		          'variant_title' => NULL,
		          'vendor' => 'pockeyt-test',
		          'fulfillment_service' => 'manual',
		          'product_id' => 2055142080561,
		          'requires_shipping' => true,
		          'taxable' => true,
		          'gift_card' => false,
		          'name' => 'Pizza',
		          'variant_inventory_management' => NULL,
		          'properties' => 
		          array (
		          ),
		          'product_exists' => true,
		          'fulfillable_quantity' => 0,
		          'grams' => 0,
		          'price' => '10.00',
		          'total_discount' => '0.00',
		          'fulfillment_status' => 'fulfilled',
		          'price_set' => 
		          array (
		            'shop_money' => 
		            array (
		              'amount' => '10.00',
		              'currency_code' => 'USD',
		            ),
		            'presentment_money' => 
		            array (
		              'amount' => '10.00',
		              'currency_code' => 'USD',
		            ),
		          ),
		          'total_discount_set' => 
		          array (
		            'shop_money' => 
		            array (
		              'amount' => '0.00',
		              'currency_code' => 'USD',
		            ),
		            'presentment_money' => 
		            array (
		              'amount' => '0.00',
		              'currency_code' => 'USD',
		            ),
		          ),
		          'discount_allocations' => 
		          array (
		          ),
		          'tax_lines' => 
		          array (
		            0 => 
		            array (
		              'title' => 'NC State Tax',
		              'price' => '0.48',
		              'rate' => 0.0475,
		              'price_set' => 
		              array (
		                'shop_money' => 
		                array (
		                  'amount' => '0.48',
		                  'currency_code' => 'USD',
		                ),
		                'presentment_money' => 
		                array (
		                  'amount' => '0.48',
		                  'currency_code' => 'USD',
		                ),
		              ),
		            ),
		            1 => 
		            array (
		              'title' => 'Orange County Tax',
		              'price' => '0.28',
		              'rate' => 0.0275,
		              'price_set' => 
		              array (
		                'shop_money' => 
		                array (
		                  'amount' => '0.28',
		                  'currency_code' => 'USD',
		                ),
		                'presentment_money' => 
		                array (
		                  'amount' => '0.28',
		                  'currency_code' => 'USD',
		                ),
		              ),
		            ),
		          ),
		          'origin_location' => 
		          array (
		            'id' => 971936366641,
		            'country_code' => 'US',
		            'province_code' => 'NC',
		            'name' => '220 Elizabeth Street',
		            'address1' => '220 Elizabeth Street',
		            'address2' => NULL,
		            'city' => 'Chapel Hill',
		            'zip' => '27514',
		          ),
		        ),
		      ),
		    ),
		  ),
		  'client_details' => 
		  array (
		    'browser_ip' => '45.37.97.85',
		    'accept_language' => 'en-us',
		    'user_agent' => 'Shopify POS/5.29.0 (iPad; iOS 12.3.1; Scale/2.00)',
		    'session_hash' => NULL,
		    'browser_width' => NULL,
		    'browser_height' => NULL,
		  ),
		  'refunds' => 
		  array (
		  ),
		);
	}

	public static function fakeReceiveWebhookNoNote() {
		return array(
		  'id' => 988524576817,
		  'email' => NULL,
		  'closed_at' => '2019-07-03T16:11:02-04:00',
		  'created_at' => '2019-07-03T16:11:00-04:00',
		  'updated_at' => '2019-07-03T16:11:02-04:00',
		  'number' => 3,
		  'note' => NULL,
		  'token' => 'b2e0a344aac75c51c5e70b396e2bde88',
		  'gateway' => 'Pockeyt Pay',
		  'test' => false,
		  'total_price' => '17.20',
		  'subtotal_price' => '16.00',
		  'total_weight' => 0,
		  'total_tax' => '1.20',
		  'taxes_included' => false,
		  'currency' => 'USD',
		  'financial_status' => 'paid',
		  'confirmed' => true,
		  'total_discounts' => '0.00',
		  'total_line_items_price' => '16.00',
		  'cart_token' => NULL,
		  'buyer_accepts_marketing' => false,
		  'name' => '#1003',
		  'referring_site' => NULL,
		  'landing_site' => '/admin/checkouts.json',
		  'cancelled_at' => NULL,
		  'cancel_reason' => NULL,
		  'total_price_usd' => '17.20',
		  'checkout_token' => '694ce512998748cc90c993c8c3aa5962',
		  'reference' => NULL,
		  'user_id' => 29169287217,
		  'location_id' => 17277419569,
		  'source_identifier' => '17277419569-1-1003',
		  'source_url' => NULL,
		  'processed_at' => '2019-07-03T16:11:00-04:00',
		  'device_id' => 1,
		  'phone' => NULL,
		  'customer_locale' => 'en',
		  'app_id' => 129785,
		  'browser_ip' => '45.37.97.85',
		  'landing_site_ref' => NULL,
		  'order_number' => 1003,
		  'discount_applications' => 
		  array (
		  ),
		  'discount_codes' => 
		  array (
		  ),
		  'note_attributes' => 
		  array (
		  ),
		  'payment_gateway_names' => 
		  array (
		    0 => 'Pockeyt Pay',
		  ),
		  'processing_method' => 'Pockeyt Pay',
		  'checkout_id' => 9345427800113,
		  'source_name' => 'pos',
		  'fulfillment_status' => 'fulfilled',
		  'tax_lines' => 
		  array (
		    0 => 
		    array (
		      'price' => '0.76',
		      'rate' => 0.0475,
		      'title' => 'NC State Tax',
		      'price_set' => 
		      array (
		        'shop_money' => 
		        array (
		          'amount' => '0.76',
		          'currency_code' => 'USD',
		        ),
		        'presentment_money' => 
		        array (
		          'amount' => '0.76',
		          'currency_code' => 'USD',
		        ),
		      ),
		    ),
		    1 => 
		    array (
		      'price' => '0.44',
		      'rate' => 0.0275,
		      'title' => 'Orange County Tax',
		      'price_set' => 
		      array (
		        'shop_money' => 
		        array (
		          'amount' => '0.44',
		          'currency_code' => 'USD',
		        ),
		        'presentment_money' => 
		        array (
		          'amount' => '0.44',
		          'currency_code' => 'USD',
		        ),
		      ),
		    ),
		  ),
		  'tags' => NULL,
		  'contact_email' => NULL,
		  'order_status_url' => 'https://pockeyt-test.myshopify.com/3956015153/orders/b2e0a344aac75c51c5e70b396e2bde88/authenticate?key=7de9158b9376192b0fb88076b9afb8ab',
		  'presentment_currency' => 'USD',
		  'total_line_items_price_set' => 
		  array (
		    'shop_money' => 
		    array (
		      'amount' => '16.00',
		      'currency_code' => 'USD',
		    ),
		    'presentment_money' => 
		    array (
		      'amount' => '16.00',
		      'currency_code' => 'USD',
		    ),
		  ),
		  'total_discounts_set' => 
		  array (
		    'shop_money' => 
		    array (
		      'amount' => '0.00',
		      'currency_code' => 'USD',
		    ),
		    'presentment_money' => 
		    array (
		      'amount' => '0.00',
		      'currency_code' => 'USD',
		    ),
		  ),
		  'total_shipping_price_set' => 
		  array (
		    'shop_money' => 
		    array (
		      'amount' => '0.00',
		      'currency_code' => 'USD',
		    ),
		    'presentment_money' => 
		    array (
		      'amount' => '0.00',
		      'currency_code' => 'USD',
		    ),
		  ),
		  'subtotal_price_set' => 
		  array (
		    'shop_money' => 
		    array (
		      'amount' => '16.00',
		      'currency_code' => 'USD',
		    ),
		    'presentment_money' => 
		    array (
		      'amount' => '16.00',
		      'currency_code' => 'USD',
		    ),
		  ),
		  'total_price_set' => 
		  array (
		    'shop_money' => 
		    array (
		      'amount' => '17.20',
		      'currency_code' => 'USD',
		    ),
		    'presentment_money' => 
		    array (
		      'amount' => '17.20',
		      'currency_code' => 'USD',
		    ),
		  ),
		  'total_tax_set' => 
		  array (
		    'shop_money' => 
		    array (
		      'amount' => '1.20',
		      'currency_code' => 'USD',
		    ),
		    'presentment_money' => 
		    array (
		      'amount' => '1.20',
		      'currency_code' => 'USD',
		    ),
		  ),
		  'total_tip_received' => '0.0',
		  'line_items' => 
		  array (
		    0 => 
		    array (
		      'id' => 2109186834481,
		      'variant_id' => 15700650098737,
		      'title' => 'Beer',
		      'quantity' => 2,
		      'sku' => NULL,
		      'variant_title' => NULL,
		      'vendor' => 'pockeyt-test',
		      'fulfillment_service' => 'manual',
		      'product_id' => 2033901568049,
		      'requires_shipping' => true,
		      'taxable' => true,
		      'gift_card' => false,
		      'name' => 'Beer',
		      'variant_inventory_management' => NULL,
		      'properties' => 
		      array (
		      ),
		      'product_exists' => true,
		      'fulfillable_quantity' => 0,
		      'grams' => 0,
		      'price' => '3.00',
		      'total_discount' => '0.00',
		      'fulfillment_status' => 'fulfilled',
		      'price_set' => 
		      array (
		        'shop_money' => 
		        array (
		          'amount' => '3.00',
		          'currency_code' => 'USD',
		        ),
		        'presentment_money' => 
		        array (
		          'amount' => '3.00',
		          'currency_code' => 'USD',
		        ),
		      ),
		      'total_discount_set' => 
		      array (
		        'shop_money' => 
		        array (
		          'amount' => '0.00',
		          'currency_code' => 'USD',
		        ),
		        'presentment_money' => 
		        array (
		          'amount' => '0.00',
		          'currency_code' => 'USD',
		        ),
		      ),
		      'discount_allocations' => 
		      array (
		      ),
		      'tax_lines' => 
		      array (
		        0 => 
		        array (
		          'title' => 'NC State Tax',
		          'price' => '0.28',
		          'rate' => 0.0475,
		          'price_set' => 
		          array (
		            'shop_money' => 
		            array (
		              'amount' => '0.28',
		              'currency_code' => 'USD',
		            ),
		            'presentment_money' => 
		            array (
		              'amount' => '0.28',
		              'currency_code' => 'USD',
		            ),
		          ),
		        ),
		        1 => 
		        array (
		          'title' => 'Orange County Tax',
		          'price' => '0.16',
		          'rate' => 0.0275,
		          'price_set' => 
		          array (
		            'shop_money' => 
		            array (
		              'amount' => '0.16',
		              'currency_code' => 'USD',
		            ),
		            'presentment_money' => 
		            array (
		              'amount' => '0.16',
		              'currency_code' => 'USD',
		            ),
		          ),
		        ),
		      ),
		      'origin_location' => 
		      array (
		        'id' => 971936366641,
		        'country_code' => 'US',
		        'province_code' => 'NC',
		        'name' => '220 Elizabeth Street',
		        'address1' => '220 Elizabeth Street',
		        'address2' => NULL,
		        'city' => 'Chapel Hill',
		        'zip' => '27514',
		      ),
		    ),
		    1 => 
		    array (
		      'id' => 2109186867249,
		      'variant_id' => 15804249833521,
		      'title' => 'Pizza',
		      'quantity' => 1,
		      'sku' => NULL,
		      'variant_title' => NULL,
		      'vendor' => 'pockeyt-test',
		      'fulfillment_service' => 'manual',
		      'product_id' => 2055142080561,
		      'requires_shipping' => true,
		      'taxable' => true,
		      'gift_card' => false,
		      'name' => 'Pizza',
		      'variant_inventory_management' => NULL,
		      'properties' => 
		      array (
		      ),
		      'product_exists' => true,
		      'fulfillable_quantity' => 0,
		      'grams' => 0,
		      'price' => '10.00',
		      'total_discount' => '0.00',
		      'fulfillment_status' => 'fulfilled',
		      'price_set' => 
		      array (
		        'shop_money' => 
		        array (
		          'amount' => '10.00',
		          'currency_code' => 'USD',
		        ),
		        'presentment_money' => 
		        array (
		          'amount' => '10.00',
		          'currency_code' => 'USD',
		        ),
		      ),
		      'total_discount_set' => 
		      array (
		        'shop_money' => 
		        array (
		          'amount' => '0.00',
		          'currency_code' => 'USD',
		        ),
		        'presentment_money' => 
		        array (
		          'amount' => '0.00',
		          'currency_code' => 'USD',
		        ),
		      ),
		      'discount_allocations' => 
		      array (
		      ),
		      'tax_lines' => 
		      array (
		        0 => 
		        array (
		          'title' => 'NC State Tax',
		          'price' => '0.48',
		          'rate' => 0.0475,
		          'price_set' => 
		          array (
		            'shop_money' => 
		            array (
		              'amount' => '0.48',
		              'currency_code' => 'USD',
		            ),
		            'presentment_money' => 
		            array (
		              'amount' => '0.48',
		              'currency_code' => 'USD',
		            ),
		          ),
		        ),
		        1 => 
		        array (
		          'title' => 'Orange County Tax',
		          'price' => '0.28',
		          'rate' => 0.0275,
		          'price_set' => 
		          array (
		            'shop_money' => 
		            array (
		              'amount' => '0.28',
		              'currency_code' => 'USD',
		            ),
		            'presentment_money' => 
		            array (
		              'amount' => '0.28',
		              'currency_code' => 'USD',
		            ),
		          ),
		        ),
		      ),
		      'origin_location' => 
		      array (
		        'id' => 971936366641,
		        'country_code' => 'US',
		        'province_code' => 'NC',
		        'name' => '220 Elizabeth Street',
		        'address1' => '220 Elizabeth Street',
		        'address2' => NULL,
		        'city' => 'Chapel Hill',
		        'zip' => '27514',
		      ),
		    ),
		  ),
		  'shipping_lines' => 
		  array (
		  ),
		  'fulfillments' => 
		  array (
		    0 => 
		    array (
		      'id' => 917525397553,
		      'order_id' => 988524576817,
		      'status' => 'success',
		      'created_at' => '2019-07-03T16:11:01-04:00',
		      'service' => 'manual',
		      'updated_at' => '2019-07-03T16:11:01-04:00',
		      'tracking_company' => NULL,
		      'shipment_status' => NULL,
		      'location_id' => 17277419569,
		      'tracking_number' => NULL,
		      'tracking_numbers' => 
		      array (
		      ),
		      'tracking_url' => NULL,
		      'tracking_urls' => 
		      array (
		      ),
		      'receipt' => 
		      array (
		      ),
		      'name' => '#1003.1',
		      'line_items' => 
		      array (
		        0 => 
		        array (
		          'id' => 2109186834481,
		          'variant_id' => 15700650098737,
		          'title' => 'Beer',
		          'quantity' => 2,
		          'sku' => NULL,
		          'variant_title' => NULL,
		          'vendor' => 'pockeyt-test',
		          'fulfillment_service' => 'manual',
		          'product_id' => 2033901568049,
		          'requires_shipping' => true,
		          'taxable' => true,
		          'gift_card' => false,
		          'name' => 'Beer',
		          'variant_inventory_management' => NULL,
		          'properties' => 
		          array (
		          ),
		          'product_exists' => true,
		          'fulfillable_quantity' => 0,
		          'grams' => 0,
		          'price' => '3.00',
		          'total_discount' => '0.00',
		          'fulfillment_status' => 'fulfilled',
		          'price_set' => 
		          array (
		            'shop_money' => 
		            array (
		              'amount' => '3.00',
		              'currency_code' => 'USD',
		            ),
		            'presentment_money' => 
		            array (
		              'amount' => '3.00',
		              'currency_code' => 'USD',
		            ),
		          ),
		          'total_discount_set' => 
		          array (
		            'shop_money' => 
		            array (
		              'amount' => '0.00',
		              'currency_code' => 'USD',
		            ),
		            'presentment_money' => 
		            array (
		              'amount' => '0.00',
		              'currency_code' => 'USD',
		            ),
		          ),
		          'discount_allocations' => 
		          array (
		          ),
		          'tax_lines' => 
		          array (
		            0 => 
		            array (
		              'title' => 'NC State Tax',
		              'price' => '0.28',
		              'rate' => 0.0475,
		              'price_set' => 
		              array (
		                'shop_money' => 
		                array (
		                  'amount' => '0.28',
		                  'currency_code' => 'USD',
		                ),
		                'presentment_money' => 
		                array (
		                  'amount' => '0.28',
		                  'currency_code' => 'USD',
		                ),
		              ),
		            ),
		            1 => 
		            array (
		              'title' => 'Orange County Tax',
		              'price' => '0.16',
		              'rate' => 0.0275,
		              'price_set' => 
		              array (
		                'shop_money' => 
		                array (
		                  'amount' => '0.16',
		                  'currency_code' => 'USD',
		                ),
		                'presentment_money' => 
		                array (
		                  'amount' => '0.16',
		                  'currency_code' => 'USD',
		                ),
		              ),
		            ),
		          ),
		          'origin_location' => 
		          array (
		            'id' => 971936366641,
		            'country_code' => 'US',
		            'province_code' => 'NC',
		            'name' => '220 Elizabeth Street',
		            'address1' => '220 Elizabeth Street',
		            'address2' => NULL,
		            'city' => 'Chapel Hill',
		            'zip' => '27514',
		          ),
		        ),
		        1 => 
		        array (
		          'id' => 2109186867249,
		          'variant_id' => 15804249833521,
		          'title' => 'Pizza',
		          'quantity' => 1,
		          'sku' => NULL,
		          'variant_title' => NULL,
		          'vendor' => 'pockeyt-test',
		          'fulfillment_service' => 'manual',
		          'product_id' => 2055142080561,
		          'requires_shipping' => true,
		          'taxable' => true,
		          'gift_card' => false,
		          'name' => 'Pizza',
		          'variant_inventory_management' => NULL,
		          'properties' => 
		          array (
		          ),
		          'product_exists' => true,
		          'fulfillable_quantity' => 0,
		          'grams' => 0,
		          'price' => '10.00',
		          'total_discount' => '0.00',
		          'fulfillment_status' => 'fulfilled',
		          'price_set' => 
		          array (
		            'shop_money' => 
		            array (
		              'amount' => '10.00',
		              'currency_code' => 'USD',
		            ),
		            'presentment_money' => 
		            array (
		              'amount' => '10.00',
		              'currency_code' => 'USD',
		            ),
		          ),
		          'total_discount_set' => 
		          array (
		            'shop_money' => 
		            array (
		              'amount' => '0.00',
		              'currency_code' => 'USD',
		            ),
		            'presentment_money' => 
		            array (
		              'amount' => '0.00',
		              'currency_code' => 'USD',
		            ),
		          ),
		          'discount_allocations' => 
		          array (
		          ),
		          'tax_lines' => 
		          array (
		            0 => 
		            array (
		              'title' => 'NC State Tax',
		              'price' => '0.48',
		              'rate' => 0.0475,
		              'price_set' => 
		              array (
		                'shop_money' => 
		                array (
		                  'amount' => '0.48',
		                  'currency_code' => 'USD',
		                ),
		                'presentment_money' => 
		                array (
		                  'amount' => '0.48',
		                  'currency_code' => 'USD',
		                ),
		              ),
		            ),
		            1 => 
		            array (
		              'title' => 'Orange County Tax',
		              'price' => '0.28',
		              'rate' => 0.0275,
		              'price_set' => 
		              array (
		                'shop_money' => 
		                array (
		                  'amount' => '0.28',
		                  'currency_code' => 'USD',
		                ),
		                'presentment_money' => 
		                array (
		                  'amount' => '0.28',
		                  'currency_code' => 'USD',
		                ),
		              ),
		            ),
		          ),
		          'origin_location' => 
		          array (
		            'id' => 971936366641,
		            'country_code' => 'US',
		            'province_code' => 'NC',
		            'name' => '220 Elizabeth Street',
		            'address1' => '220 Elizabeth Street',
		            'address2' => NULL,
		            'city' => 'Chapel Hill',
		            'zip' => '27514',
		          ),
		        ),
		      ),
		    ),
		  ),
		  'client_details' => 
		  array (
		    'browser_ip' => '45.37.97.85',
		    'accept_language' => 'en-us',
		    'user_agent' => 'Shopify POS/5.29.0 (iPad; iOS 12.3.1; Scale/2.00)',
		    'session_hash' => NULL,
		    'browser_width' => NULL,
		    'browser_height' => NULL,
		  ),
		  'refunds' => 
		  array (
		  ),
		);
	}

	public static function fakeCreateRefundOrder() {
		return json_encode([
		  "order" => [
		    "id" => 988524576817,
		    "email" => "",
		    "closed_at" => "2019-07-03T16:11:02-04:00",
		    "created_at" => "2019-07-03T16:11:00-04:00",
		    "updated_at" => "2019-07-03T16:11:02-04:00",
		    "number" => 3,
		    "note" => null,
		    "token" => "b2e0a344aac75c51c5e70b396e2bde88",
		    "gateway" => "Pockeyt Pay",
		    "test" => false,
		    "total_price" => "17.20",
		    "subtotal_price" => "16.00",
		    "total_weight" => 0,
		    "total_tax" => "1.20",
		    "taxes_included" => false,
		    "currency" => "USD",
		    "financial_status" => "paid",
		    "confirmed" => true,
		    "total_discounts" => "0.00",
		    "total_line_items_price" => "16.00",
		    "cart_token" => "",
		    "buyer_accepts_marketing" => false,
		    "name" => "#1003",
		    "referring_site" => null,
		    "landing_site" => "/admin/checkouts.json",
		    "cancelled_at" => null,
		    "cancel_reason" => null,
		    "total_price_usd" => "17.20",
		    "checkout_token" => "694ce512998748cc90c993c8c3aa5962",
		    "reference" => null,
		    "user_id" => 29169287217,
		    "location_id" => 17277419569,
		    "source_identifier" => "17277419569-1-1003",
		    "source_url" => null,
		    "processed_at" => "2019-07-03T16:11:00-04:00",
		    "device_id" => 1,
		    "phone" => null,
		    "customer_locale" => "en",
		    "app_id" => 129785,
		    "browser_ip" => "45.37.97.85",
		    "landing_site_ref" => null,
		    "order_number" => 1003,
		    "discount_applications" => [],
		    "discount_codes" => [],
		    "note_attributes" => [
		      0 => [
		        "name" => "heist_id",
		        "value" => "123e4567-e89b-12d3-a456-426655440000"
		      ]
		    ],
		    "payment_gateway_names" => [
		      0 => "Pockeyt Pay"
		    ],
		    "processing_method" => "Pockeyt Pay",
		    "checkout_id" => 9345427800113,
		    "source_name" => "pos",
		    "fulfillment_status" => "fulfilled",
		    "tags" => "",
		    "contact_email" => null,
		    "order_status_url" => "https://pockeyt-test.myshopify.com/3956015153/orders/b2e0a344aac75c51c5e70b396e2bde88/authenticate?key=7de9158b9376192b0fb88076b9afb8ab",
		    "presentment_currency" => "USD",
		    "total_tip_received" => "0.0",
		    "admin_graphql_api_id" => "gid://shopify/Order/988524576817",
		    "line_items" => [
		      0 => [
		        "id" => 2109186834481,
		        "variant_id" => 15700650098737,
		        "title" => "Beer",
		        "quantity" => 2,
		        "sku" => "",
		        "variant_title" => "",
		        "vendor" => "pockeyt-test",
		        "fulfillment_service" => "manual",
		        "product_id" => 2033901568049,
		        "requires_shipping" => true,
		        "taxable" => true,
		        "gift_card" => false,
		        "name" => "Beer",
		        "variant_inventory_management" => null,
		        "properties" => [],
		        "product_exists" => true,
		        "fulfillable_quantity" => 0,
		        "grams" => 0,
		        "price" => "3.00",
		        "total_discount" => "0.00",
		        "fulfillment_status" => "fulfilled",
		      ],
		      1 => [
		        "id" => 2109186867249,
		        "variant_id" => 15804249833521,
		        "title" => "Pizza",
		        "quantity" => 1,
		        "sku" => "",
		        "variant_title" => "",
		        "vendor" => "pockeyt-test",
		        "fulfillment_service" => "manual",
		        "product_id" => 2055142080561,
		        "requires_shipping" => true,
		        "taxable" => true,
		        "gift_card" => false,
		        "name" => "Pizza",
		        "variant_inventory_management" => null,
		        "properties" => [],
		        "product_exists" => true,
		        "fulfillable_quantity" => 0,
		        "grams" => 0,
		        "price" => "10.00",
		        "total_discount" => "0.00",
		        "fulfillment_status" => "fulfilled",
		      ]
		    ],
		    "shipping_lines" => [],
		    "refunds" => []
		  ]
		]);
	}

	public static function fakeReceiveRefundWebhook() {
		return array (
		  'id' => 61577527345,
		  'order_id' => 988524576817,
		  'created_at' => '2019-07-09T13:42:54-04:00',
		  'note' => NULL,
		  'user_id' => 29169287217,
		  'processed_at' => '2019-07-09T13:42:54-04:00',
		  'restock' => false,
		  'refund_line_items' => 
		  array (
		    0 => 
		    array (
		      'id' => 79352234033,
		      'quantity' => 1,
		      'line_item_id' => 2123766202417,
		      'location_id' => NULL,
		      'restock_type' => 'no_restock',
		      'subtotal' => 3.0,
		      'total_tax' => 0.22,
		      'subtotal_set' => 
		      array (
		        'shop_money' => 
		        array (
		          'amount' => '3.00',
		          'currency_code' => 'XXX',
		        ),
		        'presentment_money' => 
		        array (
		          'amount' => '3.00',
		          'currency_code' => 'XXX',
		        ),
		      ),
		      'total_tax_set' => 
		      array (
		        'shop_money' => 
		        array (
		          'amount' => '0.22',
		          'currency_code' => 'USD',
		        ),
		        'presentment_money' => 
		        array (
		          'amount' => '0.22',
		          'currency_code' => 'USD',
		        ),
		      ),
		      'line_item' => 
		      array (
		        'id' => 2123766202417,
		        'variant_id' => 15700650098737,
		        'title' => 'Beer',
		        'quantity' => 2,
		        'sku' => NULL,
		        'variant_title' => NULL,
		        'vendor' => 'pockeyt-test',
		        'fulfillment_service' => 'manual',
		        'product_id' => 2033901568049,
		        'requires_shipping' => true,
		        'taxable' => true,
		        'gift_card' => false,
		        'name' => 'Beer',
		        'variant_inventory_management' => NULL,
		        'properties' => 
		        array (
		        ),
		        'product_exists' => true,
		        'fulfillable_quantity' => 0,
		        'grams' => 0,
		        'price' => '3.00',
		        'total_discount' => '0.00',
		        'fulfillment_status' => 'fulfilled',
		        'price_set' => 
		        array (
		          'shop_money' => 
		          array (
		            'amount' => '3.00',
		            'currency_code' => 'USD',
		          ),
		          'presentment_money' => 
		          array (
		            'amount' => '3.00',
		            'currency_code' => 'USD',
		          ),
		        ),
		        'total_discount_set' => 
		        array (
		          'shop_money' => 
		          array (
		            'amount' => '0.00',
		            'currency_code' => 'USD',
		          ),
		          'presentment_money' => 
		          array (
		            'amount' => '0.00',
		            'currency_code' => 'USD',
		          ),
		        ),
		        'discount_allocations' => 
		        array (
		        ),
		        'tax_lines' => 
		        array (
		          0 => 
		          array (
		            'title' => 'NC State Tax',
		            'price' => '0.28',
		            'rate' => 0.0475,
		            'price_set' => 
		            array (
		              'shop_money' => 
		              array (
		                'amount' => '0.28',
		                'currency_code' => 'USD',
		              ),
		              'presentment_money' => 
		              array (
		                'amount' => '0.28',
		                'currency_code' => 'USD',
		              ),
		            ),
		          ),
		          1 => 
		          array (
		            'title' => 'Orange County Tax',
		            'price' => '0.16',
		            'rate' => 0.0275,
		            'price_set' => 
		            array (
		              'shop_money' => 
		              array (
		                'amount' => '0.16',
		                'currency_code' => 'USD',
		              ),
		              'presentment_money' => 
		              array (
		                'amount' => '0.16',
		                'currency_code' => 'USD',
		              ),
		            ),
		          ),
		        ),
		        'origin_location' => 
		        array (
		          'id' => 971936366641,
		          'country_code' => 'US',
		          'province_code' => 'NC',
		          'name' => '220 Elizabeth Street',
		          'address1' => '220 Elizabeth Street',
		          'address2' => NULL,
		          'city' => 'Chapel Hill',
		          'zip' => '27514',
		        ),
		      ),
		    ),
		    1 => 
		    array (
		      'id' => 79352266801,
		      'quantity' => 1,
		      'line_item_id' => 2123766235185,
		      'location_id' => NULL,
		      'restock_type' => 'no_restock',
		      'subtotal' => 10.0,
		      'total_tax' => 0.76,
		      'subtotal_set' => 
		      array (
		        'shop_money' => 
		        array (
		          'amount' => '10.00',
		          'currency_code' => 'XXX',
		        ),
		        'presentment_money' => 
		        array (
		          'amount' => '10.00',
		          'currency_code' => 'XXX',
		        ),
		      ),
		      'total_tax_set' => 
		      array (
		        'shop_money' => 
		        array (
		          'amount' => '0.76',
		          'currency_code' => 'USD',
		        ),
		        'presentment_money' => 
		        array (
		          'amount' => '0.76',
		          'currency_code' => 'USD',
		        ),
		      ),
		      'line_item' => 
		      array (
		        'id' => 2123766235185,
		        'variant_id' => 15804249833521,
		        'title' => 'Pizza',
		        'quantity' => 5,
		        'sku' => NULL,
		        'variant_title' => NULL,
		        'vendor' => 'pockeyt-test',
		        'fulfillment_service' => 'manual',
		        'product_id' => 2055142080561,
		        'requires_shipping' => true,
		        'taxable' => true,
		        'gift_card' => false,
		        'name' => 'Pizza',
		        'variant_inventory_management' => NULL,
		        'properties' => 
		        array (
		        ),
		        'product_exists' => true,
		        'fulfillable_quantity' => 0,
		        'grams' => 0,
		        'price' => '10.00',
		        'total_discount' => '0.00',
		        'fulfillment_status' => 'fulfilled',
		        'price_set' => 
		        array (
		          'shop_money' => 
		          array (
		            'amount' => '10.00',
		            'currency_code' => 'USD',
		          ),
		          'presentment_money' => 
		          array (
		            'amount' => '10.00',
		            'currency_code' => 'USD',
		          ),
		        ),
		        'total_discount_set' => 
		        array (
		          'shop_money' => 
		          array (
		            'amount' => '0.00',
		            'currency_code' => 'USD',
		          ),
		          'presentment_money' => 
		          array (
		            'amount' => '0.00',
		            'currency_code' => 'USD',
		          ),
		        ),
		        'discount_allocations' => 
		        array (
		        ),
		        'tax_lines' => 
		        array (
		          0 => 
		          array (
		            'title' => 'NC State Tax',
		            'price' => '2.38',
		            'rate' => 0.0475,
		            'price_set' => 
		            array (
		              'shop_money' => 
		              array (
		                'amount' => '2.38',
		                'currency_code' => 'USD',
		              ),
		              'presentment_money' => 
		              array (
		                'amount' => '2.38',
		                'currency_code' => 'USD',
		              ),
		            ),
		          ),
		          1 => 
		          array (
		            'title' => 'Orange County Tax',
		            'price' => '1.38',
		            'rate' => 0.0275,
		            'price_set' => 
		            array (
		              'shop_money' => 
		              array (
		                'amount' => '1.38',
		                'currency_code' => 'USD',
		              ),
		              'presentment_money' => 
		              array (
		                'amount' => '1.38',
		                'currency_code' => 'USD',
		              ),
		            ),
		          ),
		        ),
		        'origin_location' => 
		        array (
		          'id' => 971936366641,
		          'country_code' => 'US',
		          'province_code' => 'NC',
		          'name' => '220 Elizabeth Street',
		          'address1' => '220 Elizabeth Street',
		          'address2' => NULL,
		          'city' => 'Chapel Hill',
		          'zip' => '27514',
		        ),
		      ),
		    ),
		  ),
		  'transactions' => 
		  array (
		    0 => 
		    array (
		      'id' => 1169793548337,
		      'order_id' => 995992535089,
		      'kind' => 'refund',
		      'gateway' => 'Pockeyt Pay',
		      'status' => 'success',
		      'message' => 'Refunded 13.98 from manual gateway',
		      'created_at' => '2019-07-09T13:42:54-04:00',
		      'test' => false,
		      'authorization' => NULL,
		      'location_id' => 17277419569,
		      'user_id' => 29169287217,
		      'parent_id' => 1169361698865,
		      'processed_at' => '2019-07-09T13:42:54-04:00',
		      'device_id' => 653197361,
		      'receipt' => 
		      array (
		      ),
		      'error_code' => NULL,
		      'source_name' => 'pos',
		      'amount' => '13.98',
		      'currency' => 'USD',
		    ),
		  ),
		  'order_adjustments' => 
		  array (
		  ),
		);
	}

	public static function fakeReceiveRefundFullWebhook() {
		return array (
		  'id' => 61577527345,
		  'order_id' => 988524576817,
		  'created_at' => '2019-07-09T13:42:54-04:00',
		  'note' => NULL,
		  'user_id' => 29169287217,
		  'processed_at' => '2019-07-09T13:42:54-04:00',
		  'restock' => false,
		  'refund_line_items' => 
		  array (
		    0 => 
		    array (
		      'id' => 79352234033,
		      'quantity' => 2,
		      'line_item_id' => 2123766202417,
		      'location_id' => NULL,
		      'restock_type' => 'no_restock',
		      'subtotal' => 6.0,
		      'total_tax' => 0.44,
		      'subtotal_set' => 
		      array (
		        'shop_money' => 
		        array (
		          'amount' => '3.00',
		          'currency_code' => 'XXX',
		        ),
		        'presentment_money' => 
		        array (
		          'amount' => '3.00',
		          'currency_code' => 'XXX',
		        ),
		      ),
		      'total_tax_set' => 
		      array (
		        'shop_money' => 
		        array (
		          'amount' => '0.22',
		          'currency_code' => 'USD',
		        ),
		        'presentment_money' => 
		        array (
		          'amount' => '0.22',
		          'currency_code' => 'USD',
		        ),
		      ),
		      'line_item' => 
		      array (
		        'id' => 2123766202417,
		        'variant_id' => 15700650098737,
		        'title' => 'Beer',
		        'quantity' => 2,
		        'sku' => NULL,
		        'variant_title' => NULL,
		        'vendor' => 'pockeyt-test',
		        'fulfillment_service' => 'manual',
		        'product_id' => 2033901568049,
		        'requires_shipping' => true,
		        'taxable' => true,
		        'gift_card' => false,
		        'name' => 'Beer',
		        'variant_inventory_management' => NULL,
		        'properties' => 
		        array (
		        ),
		        'product_exists' => true,
		        'fulfillable_quantity' => 0,
		        'grams' => 0,
		        'price' => '3.00',
		        'total_discount' => '0.00',
		        'fulfillment_status' => 'fulfilled',
		        'price_set' => 
		        array (
		          'shop_money' => 
		          array (
		            'amount' => '3.00',
		            'currency_code' => 'USD',
		          ),
		          'presentment_money' => 
		          array (
		            'amount' => '3.00',
		            'currency_code' => 'USD',
		          ),
		        ),
		        'total_discount_set' => 
		        array (
		          'shop_money' => 
		          array (
		            'amount' => '0.00',
		            'currency_code' => 'USD',
		          ),
		          'presentment_money' => 
		          array (
		            'amount' => '0.00',
		            'currency_code' => 'USD',
		          ),
		        ),
		        'discount_allocations' => 
		        array (
		        ),
		        'tax_lines' => 
		        array (
		          0 => 
		          array (
		            'title' => 'NC State Tax',
		            'price' => '0.28',
		            'rate' => 0.0475,
		            'price_set' => 
		            array (
		              'shop_money' => 
		              array (
		                'amount' => '0.28',
		                'currency_code' => 'USD',
		              ),
		              'presentment_money' => 
		              array (
		                'amount' => '0.28',
		                'currency_code' => 'USD',
		              ),
		            ),
		          ),
		          1 => 
		          array (
		            'title' => 'Orange County Tax',
		            'price' => '0.16',
		            'rate' => 0.0275,
		            'price_set' => 
		            array (
		              'shop_money' => 
		              array (
		                'amount' => '0.16',
		                'currency_code' => 'USD',
		              ),
		              'presentment_money' => 
		              array (
		                'amount' => '0.16',
		                'currency_code' => 'USD',
		              ),
		            ),
		          ),
		        ),
		        'origin_location' => 
		        array (
		          'id' => 971936366641,
		          'country_code' => 'US',
		          'province_code' => 'NC',
		          'name' => '220 Elizabeth Street',
		          'address1' => '220 Elizabeth Street',
		          'address2' => NULL,
		          'city' => 'Chapel Hill',
		          'zip' => '27514',
		        ),
		      ),
		    ),
		    1 => 
		    array (
		      'id' => 79352266801,
		      'quantity' => 1,
		      'line_item_id' => 2123766235185,
		      'location_id' => NULL,
		      'restock_type' => 'no_restock',
		      'subtotal' => 10.0,
		      'total_tax' => 0.76,
		      'subtotal_set' => 
		      array (
		        'shop_money' => 
		        array (
		          'amount' => '10.00',
		          'currency_code' => 'XXX',
		        ),
		        'presentment_money' => 
		        array (
		          'amount' => '10.00',
		          'currency_code' => 'XXX',
		        ),
		      ),
		      'total_tax_set' => 
		      array (
		        'shop_money' => 
		        array (
		          'amount' => '0.76',
		          'currency_code' => 'USD',
		        ),
		        'presentment_money' => 
		        array (
		          'amount' => '0.76',
		          'currency_code' => 'USD',
		        ),
		      ),
		      'line_item' => 
		      array (
		        'id' => 2123766235185,
		        'variant_id' => 15804249833521,
		        'title' => 'Pizza',
		        'quantity' => 5,
		        'sku' => NULL,
		        'variant_title' => NULL,
		        'vendor' => 'pockeyt-test',
		        'fulfillment_service' => 'manual',
		        'product_id' => 2055142080561,
		        'requires_shipping' => true,
		        'taxable' => true,
		        'gift_card' => false,
		        'name' => 'Pizza',
		        'variant_inventory_management' => NULL,
		        'properties' => 
		        array (
		        ),
		        'product_exists' => true,
		        'fulfillable_quantity' => 0,
		        'grams' => 0,
		        'price' => '10.00',
		        'total_discount' => '0.00',
		        'fulfillment_status' => 'fulfilled',
		        'price_set' => 
		        array (
		          'shop_money' => 
		          array (
		            'amount' => '10.00',
		            'currency_code' => 'USD',
		          ),
		          'presentment_money' => 
		          array (
		            'amount' => '10.00',
		            'currency_code' => 'USD',
		          ),
		        ),
		        'total_discount_set' => 
		        array (
		          'shop_money' => 
		          array (
		            'amount' => '0.00',
		            'currency_code' => 'USD',
		          ),
		          'presentment_money' => 
		          array (
		            'amount' => '0.00',
		            'currency_code' => 'USD',
		          ),
		        ),
		        'discount_allocations' => 
		        array (
		        ),
		        'tax_lines' => 
		        array (
		          0 => 
		          array (
		            'title' => 'NC State Tax',
		            'price' => '2.38',
		            'rate' => 0.0475,
		            'price_set' => 
		            array (
		              'shop_money' => 
		              array (
		                'amount' => '2.38',
		                'currency_code' => 'USD',
		              ),
		              'presentment_money' => 
		              array (
		                'amount' => '2.38',
		                'currency_code' => 'USD',
		              ),
		            ),
		          ),
		          1 => 
		          array (
		            'title' => 'Orange County Tax',
		            'price' => '1.38',
		            'rate' => 0.0275,
		            'price_set' => 
		            array (
		              'shop_money' => 
		              array (
		                'amount' => '1.38',
		                'currency_code' => 'USD',
		              ),
		              'presentment_money' => 
		              array (
		                'amount' => '1.38',
		                'currency_code' => 'USD',
		              ),
		            ),
		          ),
		        ),
		        'origin_location' => 
		        array (
		          'id' => 971936366641,
		          'country_code' => 'US',
		          'province_code' => 'NC',
		          'name' => '220 Elizabeth Street',
		          'address1' => '220 Elizabeth Street',
		          'address2' => NULL,
		          'city' => 'Chapel Hill',
		          'zip' => '27514',
		        ),
		      ),
		    ),
		  ),
		  'transactions' => 
		  array (
		    0 => 
		    array (
		      'id' => 1169793548337,
		      'order_id' => 995992535089,
		      'kind' => 'refund',
		      'gateway' => 'Pockeyt Pay',
		      'status' => 'success',
		      'message' => 'Refunded 13.98 from manual gateway',
		      'created_at' => '2019-07-09T13:42:54-04:00',
		      'test' => false,
		      'authorization' => NULL,
		      'location_id' => 17277419569,
		      'user_id' => 29169287217,
		      'parent_id' => 1169361698865,
		      'processed_at' => '2019-07-09T13:42:54-04:00',
		      'device_id' => 653197361,
		      'receipt' => 
		      array (
		      ),
		      'error_code' => NULL,
		      'source_name' => 'pos',
		      'amount' => '17.20',
		      'currency' => 'USD',
		    ),
		  ),
		  'order_adjustments' => 
		  array (
		  ),
		);
	}
}