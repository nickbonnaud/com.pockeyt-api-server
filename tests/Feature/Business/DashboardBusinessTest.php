<?php

namespace Tests\Feature\Business;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Notification;

class DashboardBusinessTest extends TestCase {

  use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
    $this->artisan('db:seed', ['--class' => 'MasterTestSeeder']);
  }

  // public function test_onboard_steps() {
  //   $business = factory(\App\Models\Business\Business::class)->create();
  //   $token = $this->createBusinessToken($business);

  //   $name = $this->faker->company;
  //   $website = $this->faker->url;

  //   $attributes = [
  //     'name' => $name,
  //     'website' => $website,
  //     'description' => $this->faker->paragraph($nbSentences = 3, $variableNbSentences = true),
  //     'phone' => $this->faker->numerify('##########'),
  //   ];
  //   $this->send($token, 'post', '/api/business/profile', $attributes);


  //   $payFacBusiness = (factory(\App\Models\Business\PayFacBusiness::class)->make())->toArray();
  //   $payFacBusiness['city'] = "Chapel Hill";
  //   $payFacBusiness['state'] = "NC";
  //   $payFacBusiness['zip'] = '27514';
  //   $payFacBusiness['entity_type'] = 'soleProprietorship';


  //   $response = $this->send($token, 'post', '/api/business/payfac/business', $payFacBusiness)->getData();
  //   dd($response);
  // }
}
