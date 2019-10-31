<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RefundStatusesSeeder extends Seeder {
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run() {
    DB::table('refund_statuses')->insert([
    	[
    		'name' => 'refund pending',
    		'code' => 100
    	],
    	[
    		'name' => 'refund paid',
    		'code' => 200
    	],
    	[
    		'name' => 'refund failed',
    		'code' => 500
    	],
    ]);
  }
}
