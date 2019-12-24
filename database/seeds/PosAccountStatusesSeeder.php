<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PosAccountStatusesSeeder extends Seeder {
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run() {
    DB::table('pos_account_statuses')->insert([
    	[
    		'name' => 'Connection Pending',
    		'code' => 100
    	],
    	[
    		'name' => 'Successfully Connected',
    		'code' => 200
    	],
    	[
    		'name' => 'Failed to Connect',
    		'code' => 500
    	],
    ]);
  }
}
