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
	  		'name' => 'profile account incomplete',
	  		'code' => 100
	  	],
	  	[
	  		'name' => 'photo account incomplete',
	  		'code' => 101
	  	],
  		[
	  		'name' => 'business account incomplete',
	  		'code' => 102
	  	],
	  	[
	  		'name' => 'owners account incomplete',
	  		'code' => 103
	  	],
	  	[
	  		'name' => 'bank account incomplete',
	  		'code' => 104
	  	],
	  	[
	  		'name' => 'geo account incomplete',
	  		'code' => 105
	  	],
	  	[
	  		'name' => 'pos account incomplete',
	  		'code' => 106
	  	],
	  	[
	  		'name' => 'payfac pending/ach pending',
	  		'code' => 107
	  	],
	  	[
	  		'name' => 'payfac ready/ach pending',
	  		'code' => 108
	  	],
	  	[
	  		'name' => 'payfac pending/ach ready',
	  		'code' => 109
	  	],
	  	[
	  		'name' => 'ready',
	  		'code' => 200
	  	],
	  	[
	  		'name' => 'unknown error',
	  		'code' => 500
	  	],
	  	[
	  		'name' => 'payfac failed',
	  		'code' => 501
	  	],
	  	[
	  		'name' => 'ach failed',
	  		'code' => 502
	  	],
	  	[
	  		'name' => 'payfac/ach failed',
	  		'code' => 503
	  	]
  	]);
  }
}
