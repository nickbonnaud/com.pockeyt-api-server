<?php

namespace App\Helpers;

use \Faker as Faker;

class LightspeedRetailTestHelpers {

	public static function fakeLightspeedRetailAuthResponse() {
		return json_encode([
			'access_token' => 'not_token',
			'expires_in' => 3600,
			'token_type' => 'bearer',
			'refresh_token' => 'not_refresh_token'
		]);
	}

	public static function fakeLightspeedRetailAccountResponse() {
		return json_encode([
			"Account" => [
		    "accountID" => "not_real_account_id",
		    "name" => "not_real_account_name",
		    "link" => [
		      "@attributes" => [
		        "href" => "/API/Account/not_real_account_id"
		      ]
		    ]
		  ]
		]);
	}

	public static function fakeLightspeedRetailRefreshTokenResponse() {
		return json_encode([
			"access_token" => "new_access_token",
	    "expires_in" => 3600,
	    "token_type" => "bearer",
	    "scope" => "employee:all systemuserid:12345"
		]);
	}

	public static function fakeLightspeedRetailSaleResponse() {
		return json_encode([
		  "@attributes" => [
		    "count" => "1",
		  ],
		  "Sale" => [
	      "saleID" => "4401",
	      "timeStamp" => "2019-01-19T17:38:25+00:00",
	      "discountPercent" => "0",
	      "completed" => "false",
	      "archived" => "false",
	      "voided" => "false",
	      "enablePromotions" => "true",
	      "isTaxInclusive" => "false",
	      "createTime" => "2019-01-19T17:38:24+00:00",
	      "updateTime" => "2019-01-19T17:38:25+00:00",
	      "referenceNumber" => "",
	      "referenceNumberSource" => "",
	      "tax1Rate" => "0.075",
	      "tax2Rate" => "0",
	      "change" => "0",
	      "receiptPreference" => "printed",
	      "displayableSubtotal" => "515",
	      "ticketNumber" => "220000004401",
	      "calcDiscount" => "48.75",
	      "calcTotal" => "501.22",
	      "calcSubtotal" => "515",
	      "calcTaxable" => "466.25",
	      "calcNonTaxable" => "0",
	      "calcAvgCost" => "139",
	      "calcFIFOCost" => "139",
	      "calcTax1" => "34.97",
	      "calcTax2" => "0",
	      "calcPayments" => "0",
	      "total" => "501.22",
	      "totalDue" => "501.22",
	      "displayableTotal" => "501.22",
	      "balance" => "0",
	      "customerID" => "5548",
	      "discountID" => "0",
	      "employeeID" => "1",
	      "quoteID" => "0",
	      "registerID" => "6",
	      "shipToID" => "0",
	      "shopID" => "1",
	      "taxCategoryID" => "1",
	      "SaleLines" => [
	        "SaleLine" => [
	          [
	            "saleLineID" => "5728",
	            "createTime" => "2019-01-19T17:39:53+00:00",
	            "timeStamp" => "2019-01-19T17:40:04+00:00",
	            "unitQuantity" => "1",
	            "unitPrice" => "325",
	            "normalUnitPrice" => "0",
	            "discountAmount" => "0",
	            "discountPercent" => "0.15",
	            "avgCost" => "139",
	            "fifoCost" => "139",
	            "tax" => "true",
	            "tax1Rate" => "0.075",
	            "tax2Rate" => "0",
	            "isLayaway" => "false",
	            "isWorkorder" => "false",
	            "isSpecialOrder" => "false",
	            "displayableSubtotal" => "276.25",
	            "displayableUnitPrice" => "325",
	            "calcLineDiscount" => "48.75",
	            "calcTransactionDiscount" => "0",
	            "calcTotal" => "296.96875",
	            "calcSubtotal" => "325",
	            "calcTax1" => "20.71875",
	            "calcTax2" => "0",
	            "taxClassID" => "1",
	            "customerID" => "0",
	            "discountID" => "44",
	            "employeeID" => "1",
	            "itemID" => "6220",
	            "noteID" => "0",
	            "parentSaleLineID" => "0",
	            "shopID" => "1",
	            "saleID" => "4401",
	            "Discount" => [
	              "discountID" => "44",
	              "name" => "15OFF2018",
	              "discountAmount" => "0",
	              "discountPercent" => "0.15",
	              "requireCustomer" => "false",
	              "archived" => "false",
	              "timeStamp" => "2018-12-26T21:21:03+00:00"
	            ],
	            "Item" => [
	              "itemID" => "6220",
	              "systemSku" => "210000006228",
	              "defaultCost" => "139",
	              "avgCost" => "139",
	              "discountable" => "true",
	              "tax" => "true",
	              "archived" => "false",
	              "itemType" => "default",
	              "serialized" => "false",
	              "description" => "9MM TUNGSTEN CARBIDE STEP EDGE BAND",
	              "modelYear" => "0",
	              "upc" => "",
	              "ean" => "",
	              "customSku" => "",
	              "manufacturerSku" => "PSI-2548833",
	              "createTime" => "2019-01-19T17:07:49+00:00",
	              "timeStamp" => "2019-01-19T17:39:25+00:00",
	              "publishToEcom" => "false",
	              "categoryID" => "43",
	              "taxClassID" => "1",
	              "departmentID" => "0",
	              "itemMatrixID" => "0",
	              "itemAttributesID" => "0",
	              "manufacturerID" => "0",
	              "noteID" => "13652",
	              "seasonID" => "0",
	              "defaultVendorID" => "444",
	              "Prices" => [
	                "ItemPrice" => [
	                  [
	                    "amount" => "325",
	                    "useTypeID" => "1",
	                    "useType" => "Default"
	                  ],
	                  [
	                    "amount" => "325",
	                    "useTypeID" => "2",
	                    "useType" => "MSRP"
	                  ],
	                  [
	                    "amount" => "325",
	                    "useTypeID" => "3",
	                    "useType" => "Online"
	                  ]
	                ]
	              ]
	            ]
	          ],
	          [
	            "saleLineID" => "5729",
	            "createTime" => "2019-01-19T17:40:33+00:00",
	            "timeStamp" => "2019-01-19T17:42:02+00:00",
	            "unitQuantity" => "1",
	            "unitPrice" => "190",
	            "normalUnitPrice" => "0",
	            "discountAmount" => "0",
	            "discountPercent" => "0",
	            "avgCost" => "0",
	            "fifoCost" => "0",
	            "tax" => "true",
	            "tax1Rate" => "0.075",
	            "tax2Rate" => "0",
	            "isLayaway" => "false",
	            "isWorkorder" => "false",
	            "isSpecialOrder" => "false",
	            "displayableSubtotal" => "190",
	            "displayableUnitPrice" => "190",
	            "calcLineDiscount" => "0",
	            "calcTransactionDiscount" => "0",
	            "calcTotal" => "204.25",
	            "calcSubtotal" => "190",
	            "calcTax1" => "14.25",
	            "calcTax2" => "0",
	            "taxClassID" => "1",
	            "customerID" => "0",
	            "discountID" => "0",
	            "employeeID" => "1",
	            "itemID" => "330",
	            "noteID" => "13654",
	            "parentSaleLineID" => "0",
	            "shopID" => "1",
	            "saleID" => "4401",
	            "Item" => [
	              "itemID" => "330",
	              "systemSku" => "210000000330",
	              "defaultCost" => "0",
	              "avgCost" => "0",
	              "discountable" => "true",
	              "tax" => "true",
	              "archived" => "false",
	              "itemType" => "non_inventory",
	              "serialized" => "false",
	              "description" => "Repair Job",
	              "modelYear" => "0",
	              "upc" => "",
	              "ean" => "",
	              "customSku" => "",
	              "manufacturerSku" => "",
	              "createTime" => "2018-04-17T13:32:14+00:00",
	              "timeStamp" => "2018-12-13T17:02:29+00:00",
	              "publishToEcom" => "false",
	              "categoryID" => "102",
	              "taxClassID" => "1",
	              "departmentID" => "0",
	              "itemMatrixID" => "0",
	              "itemAttributesID" => "0",
	              "manufacturerID" => "0",
	              "noteID" => "330",
	              "seasonID" => "0",
	              "defaultVendorID" => "0",
	              "Prices" => [
	                "ItemPrice" => [
	                  [
	                    "amount" => "0",
	                    "useTypeID" => "1",
	                    "useType" => "Default"
	                  ],
	                  [
	                    "amount" => "0",
	                    "useTypeID" => "2",
	                    "useType" => "MSRP"
	                  ],
	                  [
	                    "amount" => "0",
	                    "useTypeID" => "3",
	                    "useType" => "Online"
	                  ]
	                ]
	              ]
	            ],
	            "Note" => [
	              "noteID" => "13654",
	              "note" => "ARMS job 44149101 re-tip ring $100\nArms Job 5149501 Refinish watch $90",
	              "isPublic" => "false",
	              "timeStamp" => "2019-01-19T17:42:02+00:00"
	            ]
	          ]
	        ]
	      ],
	      "SalePayments" => [
	        "SalePayment" => [
	          [
	            "salePaymentID" => "2498",
	            "amount" => "296.97",
	            "createTime" => "2019-01-19T17:40:12+00:00",
	            "archived" => "true",
	            "remoteReference" => "",
	            "paymentID" => "f0d3ae70-8665-4054-812c-3e9e6d7dd9ff",
	            "saleID" => "4401",
	            "paymentTypeID" => "9",
	            "ccChargeID" => "0",
	            "refPaymentID" => "0",
	            "registerID" => "6",
	            "employeeID" => "1",
	            "creditAccountID" => "0",
	            "PaymentType" => [
	              "paymentTypeID" => "9",
	              "code" => "",
	              "name" => "American Express",
	              "requireCustomer" => "true",
	              "archived" => "false",
	              "internalReserved" => "false",
	              "type" => "credit card",
	              "channel" => "instore",
	              "refundAsPaymentTypeID" => "0"
	            ]
	          ],
	          [
	            "salePaymentID" => "2499",
	            "amount" => "501.22",
	            "createTime" => "2019-01-19T17:42:10+00:00",
	            "archived" => "false",
	            "remoteReference" => "",
	            "paymentID" => "eea43948-b7b7-42a7-b08e-9bec3900fae8",
	            "saleID" => "4401",
	            "paymentTypeID" => "8",
	            "ccChargeID" => "0",
	            "refPaymentID" => "0",
	            "registerID" => "6",
	            "employeeID" => "1",
	            "creditAccountID" => "0",
	            "PaymentType" => [
	              "paymentTypeID" => "8",
	              "code" => "",
	              "name" => "Mastercard",
	              "requireCustomer" => "true",
	              "archived" => "false",
	              "internalReserved" => "false",
	              "type" => "credit card",
	              "channel" => "instore",
	              "refundAsPaymentTypeID" => "0"
	            ]
	          ]
	        ]
	      ],
	      "taxTotal" => "34.97"
		  ]
		]);
	}

	public static function fakeSalePartialRefund() {
		return json_encode([
		  "@attributes" => [
		    "count" => "1"
		  ],
		  "Sale" => [
		    "saleID" => "21",
		    "timeStamp" => "2019-06-07T20:19:39+00:00",
		    "discountPercent" => "0",
		    "completed" => "false",
		    "archived" => "false",
		    "voided" => "false",
		    "enablePromotions" => "true",
		    "isTaxInclusive" => "false",
		    "createTime" => "2019-06-07T20:19:39+00:00",
		    "updateTime" => "2019-06-07T20:19:39+00:00",
		    "referenceNumber" => "",
		    "referenceNumberSource" => "",
		    "tax1Rate" => "0.0825",
		    "tax2Rate" => "0",
		    "change" => "0",
		    "receiptPreference" => "printed",
		    "displayableSubtotal" => "-10",
		    "ticketNumber" => "220000000021",
		    "calcDiscount" => "0",
		    "calcTotal" => "-10.83",
		    "calcSubtotal" => "-10",
		    "calcTaxable" => "-10",
		    "calcNonTaxable" => "0",
		    "calcAvgCost" => "0",
		    "calcFIFOCost" => "0",
		    "calcTax1" => "-0.83",
		    "calcTax2" => "0",
		    "calcPayments" => "0",
		    "total" => "-10.83",
		    "totalDue" => "-10.83",
		    "displayableTotal" => "-10.83",
		    "balance" => "-10.83",
		    "customerID" => "0",
		    "discountID" => "0",
		    "employeeID" => "1",
		    "quoteID" => "0",
		    "registerID" => "1",
		    "shipToID" => "0",
		    "shopID" => "1",
		    "taxCategoryID" => "1",
		    "SaleLines" => [
		      "SaleLine" => [
		        "saleLineID" => "28",
		        "createTime" => "2019-06-07T20:19:51+00:00",
		        "timeStamp" => "2019-06-07T20:19:51+00:00",
		        "unitQuantity" => "-2",
		        "unitPrice" => "5",
		        "normalUnitPrice" => "0",
		        "discountAmount" => "0",
		        "discountPercent" => "0",
		        "avgCost" => "0",
		        "fifoCost" => "0",
		        "tax" => "true",
		        "tax1Rate" => "0.0825",
		        "tax2Rate" => "0",
		        "isLayaway" => "false",
		        "isWorkorder" => "false",
		        "isSpecialOrder" => "false",
		        "displayableSubtotal" => "-10",
		        "displayableUnitPrice" => "5",
		        "calcLineDiscount" => "0",
		        "calcTransactionDiscount" => "0",
		        "calcTotal" => "-10.825",
		        "calcSubtotal" => "-10",
		        "calcTax1" => "-0.825",
		        "calcTax2" => "0",
		        "taxClassID" => "1",
		        "customerID" => "0",
		        "discountID" => "0",
		        "employeeID" => "1",
		        "itemID" => "1",
		        "noteID" => "0",
		        "parentSaleLineID" => "27",
		        "shopID" => "1",
		        "saleID" => "21",
		        "Item" => [
		          "itemID" => "1",
		          "systemSku" => "210000000001",
		          "defaultCost" => "0",
		          "avgCost" => "0",
		          "discountable" => "true",
		          "tax" => "true",
		          "archived" => "false",
		          "itemType" => "default",
		          "serialized" => "false",
		          "description" => "Beer",
		          "modelYear" => "0",
		          "upc" => "",
		          "ean" => "",
		          "customSku" => "",
		          "manufacturerSku" => "",
		          "createTime" => "2019-06-07T17:22:59+00:00",
		          "timeStamp" => "2019-06-07T19:04:39+00:00",
		          "publishToEcom" => "false",
		          "categoryID" => "0",
		          "taxClassID" => "1",
		          "departmentID" => "0",
		          "itemMatrixID" => "0",
		          "itemAttributesID" => "0",
		          "manufacturerID" => "0",
		          "noteID" => "1",
		          "seasonID" => "0",
		          "defaultVendorID" => "0",
		          "Prices" => [
		            "ItemPrice" => [
		              [
		                "amount" => "5",
		                "useTypeID" => "1",
		                "useType" => "Default"
		              ],
		              [
		                "amount" => "5",
		                "useTypeID" => "2",
		                "useType" => "MSRP"
		              ]
		            ]
		          ]
		        ]
		      ]
		    ],
		    "taxTotal" => "-0.83"
		  ]
		]);
	}

	public static function fakeSalePartialRefundSaleLine() {
		return json_encode([
		  "@attributes" => [
		    "count" => "1"
		  ],
		  "SaleLine" => [
		    "saleLineID" => "27",
		    "createTime" => "2019-06-07T20:17:42+00:00",
		    "timeStamp" => "2019-06-07T20:19:31+00:00",
		    "unitQuantity" => "2",
		    "unitPrice" => "5",
		    "normalUnitPrice" => "0",
		    "discountAmount" => "0",
		    "discountPercent" => "0",
		    "avgCost" => "0",
		    "fifoCost" => "0",
		    "tax" => "true",
		    "tax1Rate" => "0.0825",
		    "tax2Rate" => "0",
		    "isLayaway" => "false",
		    "isWorkorder" => "false",
		    "isSpecialOrder" => "false",
		    "displayableSubtotal" => "10",
		    "displayableUnitPrice" => "5",
		    "calcLineDiscount" => "0",
		    "calcTransactionDiscount" => "0",
		    "calcTotal" => "10.825",
		    "calcSubtotal" => "10",
		    "calcTax1" => "0.825",
		    "calcTax2" => "0",
		    "taxClassID" => "1",
		    "customerID" => "0",
		    "discountID" => "0",
		    "employeeID" => "1",
		    "itemID" => "1",
		    "noteID" => "0",
		    "parentSaleLineID" => "0",
		    "shopID" => "1",
		    "taxCategoryID" => "1",
		    "saleID" => "20"
		  ]
		]);
	}

	public static function fakeEmployeeFetch($employeeID) {
		$faker = Faker\Factory::create();
		return json_encode([
			"@attributes" => [
		    "count" => "1"
		  ],
		  "Employee" => [
		    "employeeID" => $employeeID,
		    "firstName" => $faker->firstName,
		    "lastName" => $faker->lastName,
		    "lockOut" => "false",
		    "archived" => "false",
		    "timeStamp" => "2017-03-23T15:45:03+00:00",
		    "contactID" => "15",
		    "clockInEmployeeHoursID" => "0",
		    "employeeRoleID" => "1",
		    "limitToShopID" => "0",
		    "lastShopID" => "1",
		    "lastSaleID" => "104",
		    "lastRegisterID" => "1"
		  ]
		]);
	}
}