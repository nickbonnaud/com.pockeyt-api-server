<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RegionSeeder extends Seeder {
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run() {
    $faker = \Faker\Factory::create();

  	DB::table('regions')->insert([
  		[
  			'identifier' => $faker->uuid,
        'city' => 'Chapel Hill',
  			'state' => 'NC',
  			'zip' => '27514',
  			'center_lat' => '35.9006208',
  			'center_lng' => '-79.0306816'
  		]
  	]);
  }
}
