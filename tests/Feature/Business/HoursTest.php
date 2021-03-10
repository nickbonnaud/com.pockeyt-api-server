<?php

namespace Tests\Feature\Business;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class HoursTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

  public function test_an_unauth_business_cannot_create_hours() {
    $profile = factory(\App\Models\Business\Profile::class)->create();
    $hour = '9:00 AM - 10:00 PM';

    $body = [
      'sunday' => $hour,
      'monday' => $hour,
      'tuesday' => $hour,
      'wednesday' => $hour,
      'thursday' => $hour,
      'friday' => $hour,
      'saturday' => $hour,
    ];

    $response = $this->json('POST', '/api/business/hours', $body)->assertUnauthorized();
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_an_authorized_business_must_submit_correct_hours_data() {
    $profile = factory(\App\Models\Business\Profile::class)->create();
    $token = $this->createBusinessToken($profile->business);
    $hour = '9:00 AM - 10:00 PM';

    $body = [
      'sunday' => $hour,
      'monday' => null,
      'tuesday' => $hour,
      'wednesday' => '1:00 PM',
      'friday' => "",
      'saturday' => 0,
    ];

    $response = $this->send($token, 'post', '/api/business/hours', $body)->getData();
    $this->assertSame('The given data was invalid.', $response->message);
    $this->assertSame('The monday field is required.', $response->errors->monday[0]);
    $this->assertSame('The wednesday must be at least 17 characters.', $response->errors->wednesday[0]);
    $this->assertSame('The thursday field is required.', $response->errors->thursday[0]);
    $this->assertSame('The friday field is required.', $response->errors->friday[0]);
    $this->assertSame('The saturday must be a string.', $response->errors->saturday[0]);
  }

  public function test_an_authorized_business_can_create_hours() {
    $profile = factory(\App\Models\Business\Profile::class)->create();
    $token = $this->createBusinessToken($profile->business);
    $hour = '9:00 AM - 10:00 PM';

    $this->assertDatabaseMissing('hours', ['profile_id' => $profile->id]);
    $body = [
      'sunday' => $hour,
      'monday' => $hour,
      'tuesday' => $hour,
      'wednesday' => 'closed',
      'thursday' => $hour,
      'friday' => $hour,
      'saturday' => $hour,
    ];

    $response = $this->send($token, 'post', '/api/business/hours', $body)->getData();
    $this->assertSame($hour, $response->data->tuesday);
    $this->assertDatabaseHas('hours', ['profile_id' => $profile->id]);
  }

  public function test_an_unauth_business_cannot_update_hours() {
    $hours = factory(\App\Models\Business\Hours::class)->create([
      'sunday' => 'closed',
      'friday' => '5:00 PM - 10:00 PM'
    ]);

    $hour = '9:00 AM - 8:00 PM';
    $body = [
      'sunday' => $hour,
      'monday' => $hour,
      'tuesday' => $hour,
      'wednesday' => 'closed',
      'thursday' => $hour,
      'friday' => $hour,
      'saturday' => $hour,
    ];

    $response = $this->send('', 'patch', "/api/business/hours/{$hours->identifier}", $body)->assertUnauthorized();
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_an_auth_business_must_own_hours_to_update() {
    $hoursAuth = factory(\App\Models\Business\Hours::class)->create([
      'sunday' => 'closed',
      'friday' => '5:00 PM - 10:00 PM'
    ]);
    $token = $this->createBusinessToken($hoursAuth->profile->business);

    $hoursUnauth = factory(\App\Models\Business\Hours::class)->create();

    $hour = '9:00 AM - 8:00 PM';
    $body = [
      'sunday' => $hour,
      'monday' => $hour,
      'tuesday' => $hour,
      'wednesday' => 'closed',
      'thursday' => $hour,
      'friday' => $hour,
      'saturday' => $hour,
    ];

    $response = $this->send($token, 'patch', "/api/business/hours/{$hoursUnauth->identifier}", $body)->assertStatus(403);
    $this->assertEquals('Permission denied.', ($response->getData())->errors);
  }

  public function test_an_auth_business_cannot_update_hours_with_wrong_data() {
    $hours = factory(\App\Models\Business\Hours::class)->create();
    $token = $this->createBusinessToken($hours->profile->business);
    $hour = '9:00 AM - 10:00 PM';

    $body = [
      'sunday' => $hour,
      'monday' => null,
      'tuesday' => $hour,
      'wednesday' => '1:00 PM',
      'friday' => "",
      'saturday' => 0,
    ];

    $response = $this->send($token, 'patch', "/api/business/hours/{$hours->identifier}", $body)->getData();
    $this->assertSame('The given data was invalid.', $response->message);
    $this->assertSame('The monday field is required.', $response->errors->monday[0]);
    $this->assertSame('The wednesday must be at least 17 characters.', $response->errors->wednesday[0]);
    $this->assertSame('The thursday field is required.', $response->errors->thursday[0]);
    $this->assertSame('The friday field is required.', $response->errors->friday[0]);
    $this->assertSame('The saturday must be a string.', $response->errors->saturday[0]);
  }

  public function test_an_auth_business_can_update_their_hours() {
    $hours = factory(\App\Models\Business\Hours::class)->create([
      'sunday' => 'closed',
      'friday' => '5:00 PM - 10:00 PM'
    ]);
    $token = $this->createBusinessToken($hours->profile->business);

    $hour = '9:00 AM - 8:00 PM';
    $body = [
      'sunday' => $hour,
      'monday' => $hour,
      'tuesday' => $hour,
      'wednesday' => 'closed',
      'thursday' => $hour,
      'friday' => $hour,
      'saturday' => $hour,
    ];
    $response = $this->send($token, 'patch', "/api/business/hours/{$hours->identifier}", $body)->getData();
    $this->assertNotSame($hours->sunday, $response->data->sunday);
    $this->assertNotSame($hours->friday, $response->data->friday);
    $this->assertSame($body['wednesday'], $response->data->wednesday);
    $this->assertSame($body['saturday'], $response->data->saturday);
  }
}
