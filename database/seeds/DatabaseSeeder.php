<?php

use Illuminate\Database\Seeder;


class DatabaseSeeder extends Seeder {
  /**
   * Seed the application's database.
   *
   * @return void
   */
  public function run() {
  	$this->call([
  		AccountStatusesSeeder::class,
  		TransactionStatusesSeeder::class,
      RefundStatusesSeeder::class,
      PosAccountStatusesSeeder::class,
      RegionSeeder::class,
      CustomerStatusesSeeder::class,
      AdminRolesSeeder::class
  	]);
  }
}
