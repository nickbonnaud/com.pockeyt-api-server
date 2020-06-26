<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CustomerStatusesSeeder extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run() {
   	DB::table('customer_statuses')->insert([
   		[
   			'name' => 'Profile Account Incomplete',
   			'code' => 100
   		],
   		[
	  		'name' => 'Photo Account Incomplete',
	  		'code' => 101
	  	],
	  	[
	  		'name' => 'Tip Settings Incomplete',
	  		'code' => 102
	  	],
	  	[
	  		'name' => 'Payment Account Incomplete',
	  		'code' => 103
	  	],
	  	[
	  		'name' => 'Payment Account Pending',
	  		'code' => 120
	  	],
	  	[
	  		'name' => 'Account Active',
	  		'code' => 200
	  	],
	  	[
	  		'name' => 'Unknown Account Error',
	  		'code' => 500
	  	],
	  	[
	  		'name' => 'Payment Processor not Approved',
	  		'code' => 501
	  	]
   	]);
  }
}
