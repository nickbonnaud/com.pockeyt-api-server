<?php

namespace Tests\Feature\Business;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProfileTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

  public function test_an_unauthorized_business_cannot_create_a_profile() {

    $attributes = [
      'name' => $this->faker->company,
      'website' => $this->faker->url,
      'description' => $this->faker->paragraph($nbSentences = 3, $variableNbSentences = true),
      'phone' => $this->faker->numerify('##########'),
    ];

    $response = $this->send('', 'post', '/api/business/profile', $attributes)->assertStatus(401);
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_an_authorized_business_can_create_a_profile() {
    $business = factory(\App\Models\Business\Business::class)->create();
    $token = $this->createBusinessToken($business);
    $name = $this->faker->company;
    $website = $this->faker->url;

    $attributes = [
      'name' => $name,
      'website' => $website,
      'description' => $this->faker->paragraph($nbSentences = 3, $variableNbSentences = true),
      'phone' => $this->faker->numerify('##########'),
    ];

    $response = $this->send($token, 'post', '/api/business/profile', $attributes)->getData();
    $this->assertEquals($name, ($response->data->name));
    $this->assertEquals($website, ($response->data->website));
    $this->assertDatabaseHas('profiles', ['business_id' => $business->id]);
  }

  public function test_an_authorized_business_must_have_correct_profile_data() {
    $business = factory(\App\Models\Business\Business::class)->create();
    $token = $this->createBusinessToken($business);

    $attributes = [
      'name' => 'o',
      'website' => 'www.blah@yahoo.com',
      'description' => 'Less than 25 characters',
      'phone' => '243fe2c$',
    ];

    $response = $this->send($token, 'post', '/api/business/profile', $attributes,)->getData();

    $this->assertEquals('The given data was invalid.', $response->message);
    $this->assertEquals('The name must be at least 2 characters.', $response->errors->name[0]);
    $this->assertEquals('The website format is invalid.', $response->errors->website[0]);
    $this->assertEquals('The description must be at least 25 characters.', $response->errors->description[0]);
  }

  public function test_an_unauthorized_business_cannot_update_their_profile() {
    $profile = factory(\App\Models\Business\Profile::class)->create();

    $attributes = [
      'name' => $this->faker->company,
      'website' => $this->faker->url,
      'description' => $this->faker->paragraph($nbSentences = 3, $variableNbSentences = true),
      'phone' => $this->faker->numerify('##########'),
    ];

    $response = $this->send("", 'patch', "/api/business/profile/{$profile->identifier}", $attributes)->assertStatus(401);
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_an_authorized_business_can_update_their_profile() {
    $profile = factory(\App\Models\Business\Profile::class)->create();
    $token = $this->createBusinessToken($profile->business);

    $name = $this->faker->company;
    $website = $this->faker->url;
    $description = $this->faker->paragraph($nbSentences = 3, $variableNbSentences = true);
    $phone = $this->faker->numerify('##########');

    $attributes = [
      'name' => $name,
      'website' => $website,
      'description' => $description,
      'phone' => $phone,
    ];

    $response = $this->send($token, 'patch', "/api/business/profile/{$profile->identifier}", $attributes)->getData();
    $this->assertEquals($name, $response->data->name);
    $this->assertEquals($website, $response->data->website);
    $this->assertEquals($description, $response->data->description);
    $this->assertEquals($phone, $response->data->phone);
  }

  public function test_an_authorized_business_can_only_update_with_correct_data() {
    $profile = factory(\App\Models\Business\Profile::class)->create();
    $token = $this->createBusinessToken($profile->business);

    $attributes = [
      'name' => 'o',
      'website' => 'www.blah@yahoo.com',
      'description' => 'Less than 25 characters',
      'phone' => 'cdg363%$',
    ];

    $response = $this->send($token, 'patch', "/api/business/profile/{$profile->identifier}", $attributes)->getData();
    $this->assertEquals('The given data was invalid.', $response->message);
    $this->assertEquals('The name must be at least 2 characters.', $response->errors->name[0]);
    $this->assertEquals('The website format is invalid.', $response->errors->website[0]);
    $this->assertEquals('The description must be at least 25 characters.', $response->errors->description[0]);
    $this->assertEquals('The phone must be a number.', $response->errors->phone[0]);
    $this->assertEquals('The phone must be 10 digits.', $response->errors->phone[1]);
  }

  public function test_an_authorized_business_cannot_update_another_profile() {
    $profile = factory(\App\Models\Business\Profile::class)->create();
    $unauthorizedProfile = factory(\App\Models\Business\Profile::class)->create();
    $token = $this->createBusinessToken($unauthorizedProfile->business);

    $attributes = [
      'name' => $this->faker->company,
      'website' => $this->faker->url,
      'description' => $this->faker->paragraph($nbSentences = 3, $variableNbSentences = true),
      'phone' => $this->faker->numerify('##########'),
    ];

    $response = $this->send($token, 'patch', "/api/business/profile/{$profile->identifier}", $attributes)->assertStatus(403);
    $response = $response->getData();
    $this->assertEquals('Permission denied.', $response->errors);
  }
}
