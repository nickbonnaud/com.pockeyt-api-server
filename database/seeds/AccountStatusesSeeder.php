<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccountStatusesSeeder extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run() {
    DB::table('account_statuses')->insert([
  		[
	  		'name' => 'Profile Account Incomplete',
	  		'code' => 100
	  	],
	  	[
	  		'name' => 'Photo Account Incomplete',
	  		'code' => 101
	  	],
  		[
	  		'name' => 'Business Account Incomplete',
	  		'code' => 102
	  	],
	  	[
	  		'name' => 'Owners Account Incomplete',
	  		'code' => 103
	  	],
	  	[
	  		'name' => 'Bank Account Incomplete',
	  		'code' => 104
	  	],
	  	[
	  		'name' => 'Geo Account Incomplete',
	  		'code' => 105
	  	],
	  	[
	  		'name' => 'Pos Account Incomplete',
	  		'code' => 106
	  	],
	  	[
	  		'name' => 'Credit Processor Pending/ACH Processor Pending',
	  		'code' => 120
	  	],
	  	[
	  		'name' => 'Credit Processor Ready/ACH Processor Pending',
	  		'code' => 121
	  	],
	  	[
	  		'name' => 'Credit Processor Pending/ACH Processor Ready',
	  		'code' => 122
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
	  		'name' => 'Credit Processor not Approved',
	  		'code' => 501
	  	],
	  	[
	  		'name' => 'ACH Processor not Approved',
	  		'code' => 502
	  	],
	  	[
	  		'name' => 'Credit Processor & ACH Processor not Approved',
	  		'code' => 503
	  	]
  	]);
  }
}
