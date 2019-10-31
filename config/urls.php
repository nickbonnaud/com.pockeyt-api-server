<?php

return [

	'dashboard' => [
		'base' => 'http://localhost:4200/dashboard'
	],

	'square' => [
		'base' => 'https://connect.squareup.com/',
		'oauth_token' => 'oauth2/token',
		'locations' => 'v2/locations',
		'inventory' => 'v1/<location_id>/items',
		'web_hook' => 'v1/<location_id>/webhooks',
		'payment' => 'v1/<location_id>/payments/<entity_id>',
		'transaction' => 'v2/locations/<location_id>/transactions/<transaction_id>',
		'customer' => 'v2/customers/<customer_id>',
		'create_customer' => 'v2/customers',
		'destroy_customer' => 'v2/customers/<customer_id>',
		'employee' => 'v2/employees/<employee_id>'
	],

	'clover' => [
		'base' => 'https://apisandbox.dev.clover.com/',
		'oauth_token' => 'oauth/token',
		'inventory' => 'v3/merchants/<merchant_id>/items?limit=100&offset=1',
		'order' => 'v3/merchants/<merchant_id>/orders/<order_id>',
		'close_order' => 'v3/merchants/<merchant_id>/orders/<order_id>/payments',
		'employee' => 'v3/merchants/<merchant_id>/employees/<employee_id>'
	],

	'lightspeed_retail' => [
		'base' => 'https://api.lightspeedapp.com/API/',
		'oauth_token' => 'https://cloud.lightspeedapp.com/oauth/access_token.php',
		'account' => 'Account',
		'sale' => "Account/<account_id>/Sale/<sale_id>?load_relations=" . json_encode(['SaleLines','SaleLines.Item']),
		'payment_type' => 'Account/<account_id>/PaymentType',
		'sale_line' => 'Account/<account_id>/SaleLine/<sale_line_id>',
		'employee' => 'Account/<account_id>/Employee/<employee_id>'
	],

	'shopify' => [
		'base' => 'https://<shop_id>/admin/api/2019-04/',
		'oauth_token' => 'https://<shop_id>/admin/oauth/access_token',
		'webhook' => 'webhooks',
		'order' => 'orders/<order_id>'
	],

	'vend' => [
		'base' => "https://<domain_prefix>.vendhq.com/api/",
		'oauth_token' => '1.0/token',
		'webhook' => 'webhooks',
		'create_customer' => '2.0/customers',
		'destroy_customer' => '2.0/customers/<customer_id>',
		'product' => '2.0/products/<product_id>',
		'employee' => '2.0/users/<user_id>'
	]
];