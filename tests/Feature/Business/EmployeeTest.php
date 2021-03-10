<?php

namespace Tests\Feature\Business;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EmployeeTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

  public function test_an_unauth_business_cannot_retrieve_employees() {
    $business = factory(\App\Models\Business\Business::class)->create();
    factory(\App\Models\Business\Employee::class, 12)->create(['business_id' => $business->id]);
    $response = $this->send('', 'get', '/api/business/employees')->assertStatus(401);
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_a_business_can_retrieve_their_employees() {
    $numEmployees = 13;
    $business = factory(\App\Models\Business\Business::class)->create();
    $employees = factory(\App\Models\Business\Employee::class, $numEmployees)->create(['business_id' => $business->id]);

    $token = $this->createBusinessToken($business);
    $response = $this->send($token, 'get', '/api/business/employees')->getData();
    $this->assertEquals($numEmployees, $response->meta->total);
  }

  public function test_a_business_can_only_retrieve_their_employees() {
    $numEmployees = 12;
    $business = factory(\App\Models\Business\Business::class)->create();
    $employees = factory(\App\Models\Business\Employee::class, $numEmployees)->create(['business_id' => $business->id]);

    $notBusiness = factory(\App\Models\Business\Business::class)->create();
    factory(\App\Models\Business\Employee::class, 17)->create(['business_id' => $notBusiness->id]);

    $token = $this->createBusinessToken($business);
    $response = $this->send($token, 'get', '/api/business/employees')->getData();
    $this->assertEquals($numEmployees, $response->meta->total);
  }
}
