<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransactionStatusesSeeder extends Seeder {
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run() {
    DB::table('transaction_statuses')->insert([
    	[
    		'name' => 'open',
    		'code' => 100
    	],
    	[
    		'name' => 'closed',
    		'code' => 101
    	],
    	[
    		'name' => 'notification pending',
    		'code' => 102
    	],
    	[
    		'name' => 'payment processing',
    		'code' => 103
    	],
    	[
    		'name' => 'paid',
    		'code' => 200
    	],
    	[
    		'name' => 'wrong bill assigned',
    		'code' => 500
    	],
    	[
    		'name' => 'error in bill',
    		'code' => 501
    	],
    	[
    		'name' => 'error notifying',
    		'code' => 502
    	],
    	[
    		'name' => 'other bill error',
    		'code' => 503
    	]
    ]);
  }
}
