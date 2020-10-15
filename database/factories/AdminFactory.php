<?php

use App\Models\Admin;
use Faker\Generator as Faker;

$factory->define(Admin\Admin::class, function (Faker $faker) {
	return [
		'email' => $faker->unique()->safeEmail,
		'role_id' => Admin\Role::where('name', 'help')->first()->id,
		'approved' => true,
		'password' => $faker->password,
		'remember_token' => Str::random(10),
	];
});