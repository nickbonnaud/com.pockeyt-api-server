<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminRolesSeeder extends Seeder {
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run() {
  	DB::table('roles')->insert([
  		['name' => 'master', 'code' => 0],
			['name' => 'help', 'code' => 1],
			['name' => 'manager', 'code' => 2]
  	]);
  }
}
