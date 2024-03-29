<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Business
Route::prefix('business')->group(function() {

	// Auth
	Route::prefix('auth')->group(function() {
		Route::get('logout', 'Business\AuthController@logout');
		Route::get('refresh', 'Business\AuthController@refresh');
		Route::post('register', 'Business\AuthController@register');
		Route::post('login', 'Business\AuthController@login');
		Route::post('verify', 'Business\AuthController@verify');

		Route::post('request-reset', 'Business\AuthResetPasswordController@requestReset');
		Route::patch('reset-password', 'Business\AuthResetPasswordController@reset');
	});

	// Business Self
	Route::get('business', 'Business\BusinessController@index');
	Route::patch('business/{business}', 'Business\BusinessController@update');

	// Business Profile
	Route::post('profile', 'Business\ProfileController@store');
	Route::patch('profile/{profile}', 'Business\ProfileController@update');

	// Business Hours
	Route::post('hours', 'Business\HoursController@store');
	Route::patch('hours/{hours}', 'Business\HoursController@update');

	// Business Photos
	Route::post('photos/{profile}', 'Business\PhotoController@store');

	// Account
	Route::prefix('payfac')->group(function() {
		Route::post('business', 'Business\PayFacBusinessController@store');
		Route::patch('business/{pay_fac_business}', 'Business\PayFacBusinessController@update');

		Route::post('owner', 'Business\PayFacOwnerController@store');
		Route::patch('owner/{pay_fac_owner}', 'Business\PayFacOwnerController@update');
		Route::delete('owner/{pay_fac_owner}', 'Business\PayFacOwnerController@destroy');

		Route::post('bank', 'Business\PayFacBankController@store');
		Route::patch('bank/{pay_fac_bank}', 'Business\PayFacBankController@update');
	});

	// Location
	Route::prefix('location')->group(function() {
		Route::post('geo', 'Business\GeoAccountController@store');
		Route::patch('geo/{geo_account}', 'Business\GeoAccountController@update');
	});

	// Pos
	Route::prefix('pos')->group(function() {
		Route::post('account', 'Business\PosAccountController@store');
		Route::patch('account/{pos_account}', 'Business\PosAccountController@update');
		Route::get('account', 'Business\PosAccountController@index');

		// Square
		Route::get('square/oauth', 'Business\SquareAccountController@store');

		// Clover
		Route::get('clover/oauth', 'Business\CloverAccountController@store');
		Route::patch('clover/transaction', 'Business\CloverTransactionController@update');

		// Lightspeed Retail
		Route::get('lsr/oauth', 'Business\LightspeedRetailAccountController@store');
		Route::post('lsr/transaction', 'Business\LightspeedRetailTransactionController@store');

		// Shopify
		Route::get('shopify/oauth', 'Business\ShopifyAccountController@store');

		// Vend
		Route::get('vend/oauth', 'Business\VendAccountController@store');
	});


	// Business Customers
	Route::get('customers', 'Business\CustomerController@index');

	// Business Transactions
	Route::get('transactions', 'Business\TransactionController@index');

	// Business Unassigned Transactions
	Route::get('unassigned-transactions', 'Business\UnassignedTransactionController@index');

	// Business Refunds
	Route::get('refunds', 'Business\RefundController@index');

	// Business Transaction Statuses
	Route::prefix('status')->group(function() {
		Route::get('transaction', 'Business\TransactionStatusController@index');
	});

	// Business Employee Tips
	Route::get('tips', 'Business\TipController@index');

	// Business Employees
	Route::get('employees', 'Business\EmployeeController@index');

	// Business Messages
	Route::get('message', 'Business\MessageController@index');
	Route::post('message', 'Business\MessageController@store');
	Route::patch('message/{business_message}', 'Business\MessageController@update');

	// Business Replies
	Route::post('reply', 'Business\ReplyController@store');

	// Business API Credentials
	Route::get('credentials', 'Business\CredentialsController@index');
});

// Customer
Route::prefix('customer')->group(function() {

	// Auth
	Route::prefix('auth')->group(function() {
		Route::get('logout', 'Customer\AuthController@logout');
		Route::get('refresh', 'Customer\AuthController@refresh');
		Route::post('register', 'Customer\AuthController@register');
		Route::post('login', 'Customer\AuthController@login');
		Route::post('password-check', 'Customer\AuthController@check');

		Route::post('request-reset', 'Customer\AuthController@requestResetPassword');
		Route::patch('reset-password', 'Customer\AuthController@resetPassword');
	});

	// Customer Self
	Route::get('me', 'Customer\CustomerController@index');
	Route::patch('me/{customer}', 'Customer\CustomerController@update');

	// Customer Profile
	Route::get('profile', 'Customer\ProfileController@index');
	Route::post('profile', 'Customer\ProfileController@store');
	Route::patch('profile/{customer_profile}', 'Customer\ProfileController@update');

	// Customer Photo
	Route::post('avatar/{customer_profile}', 'Customer\PhotoController@store');

	// Customer Push Token
	Route::post('push-token', 'Customer\PushTokenController@store');

	// Customer Account
	Route::patch('account/{customer_account}', 'Customer\AccountController@update');

	// Location
	Route::post('location', 'Customer\LocationController@store');
	Route::delete('location/{active_location}', 'Customer\LocationController@destroy');

	// Business Geo Locations
	Route::post('geo-location', 'Customer\GeoLocationController@store');

	// Customer transactions
	Route::get('transaction', 'Customer\TransactionController@index');
	Route::patch('transaction/{transaction}', 'Customer\TransactionController@update');

	// Customer Unassigned transactions
	Route::get('unassigned-transaction', 'Customer\UnassignedTransactionController@index');
	Route::patch('unassigned-transaction/{unassigned_transaction}', 'Customer\UnassignedTransactionController@update');

	// Customer refunds
	Route::get('refund', 'Customer\RefundController@index');

	// Fetch Businesses
	Route::get('business', 'Customer\BusinessController@index');

	// Customer Transaction Issues
	Route::post('transaction-issue', 'Customer\TransactionIssueController@store');
	Route::patch('transaction-issue/{transaction_issue}', 'Customer\TransactionIssueController@update');
	Route::delete('transaction-issue/{transaction_issue}', 'Customer\TransactionIssueController@destroy');

	// Help tickets
	Route::get('help', 'Customer\HelpTicketController@index');
	Route::post('help', 'Customer\HelpTicketController@store');
	Route::delete('help/{help_ticket}', 'Customer\HelpTicketController@destroy');

	// Help Ticket replies
	Route::post('help-reply', 'Customer\HelpTicketReplyController@store');
	Route::patch('help-reply/{help_ticket}', 'Customer\HelpTicketReplyController@update');

	// Customer API Credentials
	Route::get('credentials', 'Customer\CredentialsController@index');
});

Route::prefix('admin')->group(function() {

	// Auth
	Route::prefix('auth')->group(function() {
		Route::post('register', 'Admin\AuthController@register');
		Route::post('login', 'Admin\AuthController@login');
		Route::get('logout', 'Admin\AuthController@logout');
		Route::get('refresh', 'Admin\AuthController@refresh');
	});

	// Help Tickets
	Route::get('help', 'Admin\HelpTicketController@index');
	Route::patch('help/{help_ticket}', 'Admin\HelpTicketController@update');

	// Help Ticket Replies
	Route::post('help-reply', 'Admin\HelpTicketReplyController@store');
	Route::patch('help-reply/{help_ticket}', 'Admin\HelpTicketReplyController@update');

	// Master admin
	Route::prefix('master')->group(function() {
		Route::patch('admin', 'Admin\MasterAdminController@approve');
	});

});

// Webhooks
Route::prefix('webhook')->group(function() {
	Route::post('square', 'Webhook\SquareController@store');
	Route::post('clover', 'Webhook\CloverController@store');
	Route::post('shopify', 'Webhook\ShopifyController@store');
	Route::post('vend', 'Webhook\VendController@store');
});
