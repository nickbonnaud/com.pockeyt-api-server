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
    		'name' => 'Open',
    		'code' => 100
    	],
    	[
    		'name' => 'Closed',
    		'code' => 101
    	],
    	[
    		'name' => 'Payment Processing',
    		'code' => 103
    	],
			[
					'name' => 'Customer Approved',
					'code' => 104
			],
			[
					'name' => 'Keep Open Notification Sent',
					'code' => 105
			],
			[
					'name' => 'Customer Request Keep Open',
					'code' => 106
			],
    	[
    		'name' => 'Paid',
    		'code' => 200
    	],
    	[
    		'name' => 'Wrong Bill Assigned',
    		'code' => 500
    	],
    	[
    		'name' => 'Error in Bill',
    		'code' => 501
    	],
    	[
    		'name' => 'Error Notifying',
    		'code' => 502
    	],
    	[
    		'name' => 'Other Bill Error',
    		'code' => 503
    	]
    ]);
  }
}
